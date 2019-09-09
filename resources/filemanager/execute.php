<?php
/**
 * @author Alberto Peripolli https://responsivefilemanager.com/#contact-section
 * @source https://github.com/trippo/ResponsiveFilemanager
 * 
 * Licenced under Attribution-NonCommercial 3.0 Unported (CC BY-NC 3.0) 
 * https://creativecommons.org/licenses/by-nc/3.0/
 * 
 * This work is licensed under the Creative Commons 
 * Attribution-NonCommercial 3.0 Unported License.
 * To view a copy of this license, visit 
 * http://creativecommons.org/licenses/by-nc/3.0/ or send a 
 * letter to Creative Commons, 444 Castro Street, Suite 900, 
 * Mountain View, California, 94041, USA.
 */

require_once __DIR__.'/boot.php';

$config = config('rfm');

use ResponsiveFileManager\RFM;

/**
 * Check RF session
 */
if (!session()->exists('RF') || session('RF.verify') != "RESPONSIVEfilemanager")
{
	RFM::response(RFM::fm_trans('forbidden') . RFM::AddErrorLocation(), 403)->send();
    exit;
}

if (!RFM::checkRelativePath($_POST['path'])) {
    RFM::response(RFM::fm_trans('wrong path') . RFM::AddErrorLocation())->send();
    exit;
}

if (session()->exists('RF.language') && file_exists(__DIR__.'/lang/' . basename(session('RF.language')) . '.php')) {
    $languages = include __DIR__.'/lang/languages.php';
    if (array_key_exists(session('RF.language'), $languages)) {
        include __DIR__.'/lang/' . basename(session('RF.language')) . '.php';
    } else {
        RFM::response(RFM::fm_trans('Lang_Not_Found') . RFM::AddErrorLocation())->send();
        exit;
    }
} else {
    RFM::response(RFM::fm_trans('Lang_Not_Found') . RFM::AddErrorLocation())->send();
    exit;
}

$ftp = RFM::ftp_con($config);

$base = $config['current_path'];
$path = $base . $_POST['path'];
$cycle = true;
$max_cycles = 50;
$i = 0;

while ($cycle && $i < $max_cycles) {
    $i++;
    if ($path == $base) {
        $cycle = false;
    }

    if (file_exists($path . "config.php")) {
        require_once $path . "config.php";
        $cycle = false;
    }
    $path = RFM::fix_dirname($path) . "/";
}

$returnPaths = function ($_path, $_name, $config) use($ftp) {
    $path = $config['current_path'] . $_path;
    $path_thumb = $config['thumbs_base_path'] . $_path;
    $name = null;
    if ($ftp) {
        $path = $config['ftp_base_folder'] . $config['upload_dir'] . $_path;
        $path_thumb = $config['ftp_base_folder'] . $config['ftp_thumbs_dir'] . $_path;
    }
    if ($_name) {
        $name = RFM::fix_filename($_name, $config);
        if (strpos($name, '../') !== false || strpos($name, '..\\') !== false) {
            RFM::response(RFM::fm_trans('wrong name') . RFM::AddErrorLocation())->send();
            exit;
        }
    }
    return array($path, $path_thumb, $name);
};

if(isset($_POST['paths'])){
	$paths = $paths_thumb = $names = array();
	foreach ($_POST['paths'] as $key => $path) {
		if (!RFM::checkRelativePath($path))
		{
			RFM::response(RFM::fm_trans('wrong path').RFM::AddErrorLocation())->send();
			exit;
		}
		$name = null;
		if(isset($_POST['names'][$key])){
			$name = $_POST['names'][$key];
		}
		list($path,$path_thumb,$name) = $returnPaths($path,$name,$config);
		$paths[] = $path;
		$paths_thumb[] = $path_thumb;
		$names = $name;
	}
} else {
	$name = null;
	if(isset($_POST['name'])){
		$name = $_POST['name'];
	}
	list($path,$path_thumb,$name) = $returnPaths($_POST['path'],$name,$config);

}

$info = pathinfo($path);
if (isset($info['extension']) && !(isset($_GET['action']) && $_GET['action'] == 'delete_folder') &&
    !RFM::check_extension($info['extension'], $config)
    && $_GET['action'] != 'create_file') {
    RFM::response(RFM::fm_trans('wrong extension') . RFM::AddErrorLocation())->send();
    exit;
}

if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'delete_file':

            RFM::deleteFile($path, $path_thumb, $config);

            break;

        case 'delete_files':
            foreach ($paths as $key => $p) {
                RFM::deleteFile($p, $paths_thumb[$key], $config);
            }

			break;
		case 'delete_folder':
			if ($config['delete_folders']){

				if($ftp){
					RFM::deleteDir($path,$ftp,$config);
					RFM::deleteDir($path_thumb,$ftp,$config);
				}else{
					if (is_dir($path_thumb))
					{
						RFM::deleteDir($path_thumb,NULL,$config);
					}

					if (is_dir($path))
					{
						RFM::deleteDir($path,NULL,$config);
						if ($config['fixed_image_creation'])
						{
							foreach($config['fixed_path_from_filemanager'] as $k=>$paths){
								if ($paths!="" && $paths[strlen($paths)-1] != "/") $paths.="/";

								$base_dir=$paths.substr_replace($path, '', 0, strlen($config['current_path']));
								if (is_dir($base_dir)) RFM::deleteDir($base_dir,NULL,$config);
							}
						}
					}
				}
			}
			break;
		case 'create_folder':
			if ($config['create_folders'])
			{

				$name = RFM::fix_filename($_POST['name'],$config);
				$path .= $name;
                $path_thumb .= $name;
				$res = RFM::create_folder(RFM::fix_path($path,$config),RFM::fix_path($path_thumb,$config),$ftp,$config);
				if(!$res){
					RFM::response(RFM::fm_trans('Rename_existing_folder').RFM::AddErrorLocation())->send();
				}
			}
			break;
		case 'rename_folder':
			if ($config['rename_folders']){
                if((!$ftp && !is_dir($path)) || !RFM::ftp_is_dir($ftp, $path)) {
                    RFM::response(RFM::fm_trans('wrong path').RFM::AddErrorLocation())->send();
                    exit;
                }
                $name = RFM::fix_filename($name, $config);
                $name = str_replace('.', '', $name);

                if (!empty($name)) {
                    if (!RFM::rename_folder($path, $name, $ftp, $config)) {
                        RFM::response(RFM::fm_trans('Rename_existing_folder') . RFM::AddErrorLocation())->send();
                        exit;
                    }
                    RFM::rename_folder($path_thumb, $name, $ftp, $config);
                    if (!$ftp && $config['fixed_image_creation']) {
                        foreach ($config['fixed_path_from_filemanager'] as $k => $paths) {
                            if ($paths != "" && $paths[strlen($paths) - 1] != "/") {
                                $paths .= "/";
                            }

                            $base_dir = $paths . substr_replace($path, '', 0, strlen($config['current_path']));
                            RFM::rename_folder($base_dir, $name, $ftp, $config);
                        }
                    }
                } else {
                    RFM::response(RFM::fm_trans('Empty_name') . RFM::AddErrorLocation())->send();
                    exit;
                }
            }
            break;

        case 'create_file':
            if ($config['create_text_files'] === false) {
                RFM::response(sprintf(RFM::fm_trans('File_Open_Edit_Not_Allowed'), strtolower(RFM::fm_trans('Edit'))) . RFM::AddErrorLocation())->send();
                exit;
            }

            if (!isset($config['editable_text_file_exts']) || !is_array($config['editable_text_file_exts'])) {
                $config['editable_text_file_exts'] = array();
            }

            // check if user supplied extension
            if (strpos($name, '.') === false) {
                RFM::response(RFM::fm_trans('No_Extension') . ' ' . sprintf(RFM::fm_trans('Valid_Extensions'), implode(', ', $config['editable_text_file_exts'])) . RFM::AddErrorLocation())->send();
                exit;
            }

            // correct name
            $old_name = $name;
            $name = RFM::fix_filename($name, $config);
            if (empty($name)) {
                RFM::response(RFM::fm_trans('Empty_name') . RFM::AddErrorLocation())->send();
                exit;
            }

            // check extension
            $parts = explode('.', $name);
            if (!in_array(end($parts), $config['editable_text_file_exts'])) {
                RFM::response(RFM::fm_trans('Error_extension') . ' ' . sprintf(RFM::fm_trans('Valid_Extensions'), implode(', ', $config['editable_text_file_exts'])) . RFM::AddErrorLocation(), 400)->send();
                exit;
            }

            $content = $_POST['new_content'];

            if ($ftp) {
                $temp = tempnam('/tmp', 'RF');
                file_put_contents($temp, $content);
                $ftp->put("/" . $path . $name, $temp, FTP_BINARY);
                unlink($temp);
                RFM::response(RFM::fm_trans('File_Save_OK'))->send();
            } else {
                if (!RFM::checkresultingsize(strlen($content))) {
                    RFM::response(sprintf(RFM::fm_trans('max_size_reached'), $config['MaxSizeTotal']) . RFM::AddErrorLocation())->send();
                    exit;
                }
                // file already exists
                if (file_exists($path . $name)) {
                    RFM::response(RFM::fm_trans('Rename_existing_file') . RFM::AddErrorLocation())->send();
                    exit;
                }

                if (@file_put_contents($path . $name, $content) === false) {
                    RFM::response(RFM::fm_trans('File_Save_Error') . RFM::AddErrorLocation())->send();
                    exit;
                } else {
                    if (RFM::is_function_callable('chmod') !== false) {
                        chmod($path . $name, 0644);
                    }
                    RFM::response(RFM::fm_trans('File_Save_OK'))->send();
                    exit;
                }
            }

            break;

        case 'rename_file':
            if ($config['rename_files']) {
                $name = RFM::fix_filename($name, $config);
                if (!empty($name)) {
                    if (!RFM::rename_file($path, $name, $ftp, $config)) {
                        RFM::response(RFM::fm_trans('Rename_existing_file') . RFM::AddErrorLocation())->send();
                        exit;
                    }

                    RFM::rename_file($path_thumb, $name, $ftp, $config);

                    if ($config['fixed_image_creation']) {
                        $info = pathinfo($path);

                        foreach ($config['fixed_path_from_filemanager'] as $k => $paths) {
                            if ($paths != "" && $paths[strlen($paths) - 1] != "/") {
                                $paths .= "/";
                            }

                            $base_dir = $paths . substr_replace($info['dirname'] . "/", '', 0, strlen($config['current_path']));
                            if (file_exists($base_dir . $config['fixed_image_creation_name_to_prepend'][$k] . $info['filename'] . $config['fixed_image_creation_to_append'][$k] . "." . $info['extension'])) {
                                RFM::rename_file($base_dir . $config['fixed_image_creation_name_to_prepend'][$k] . $info['filename'] . $config['fixed_image_creation_to_append'][$k] . "." . $info['extension'], $config['fixed_image_creation_name_to_prepend'][$k] . $name . $config['fixed_image_creation_to_append'][$k], $ftp, $config);
                            }
                        }
                    }
                } else {
                    RFM::response(RFM::fm_trans('Empty_name') . RFM::AddErrorLocation())->send();
                    exit;
                }
            }
            break;

        case 'duplicate_file':
            if ($config['duplicate_files']) {
                $name = RFM::fix_filename($name, $config);
                if (!empty($name)) {
                    if (!$ftp && !RFM::checkresultingsize(filesize($path))) {
                        RFM::response(sprintf(RFM::fm_trans('max_size_reached'), $config['MaxSizeTotal']) . RFM::AddErrorLocation())->send();
                        exit;
                    }
                    if (!RFM::duplicate_file($path, $name, $ftp, $config)) {
                        RFM::response(RFM::fm_trans('Rename_existing_file') . RFM::AddErrorLocation())->send();
                        exit;
                    }

                    RFM::duplicate_file($path_thumb, $name, $ftp, $config);

                    if (!$ftp && $config['fixed_image_creation']) {
                        $info = pathinfo($path);
                        foreach ($config['fixed_path_from_filemanager'] as $k => $paths) {
                            if ($paths != "" && $paths[strlen($paths) - 1] != "/") {
                                $paths .= "/";
                            }

                            $base_dir = $paths . substr_replace($info['dirname'] . "/", '', 0, strlen($config['current_path']));

                            if (file_exists($base_dir . $config['fixed_image_creation_name_to_prepend'][$k] . $info['filename'] . $config['fixed_image_creation_to_append'][$k] . "." . $info['extension'])) {
                                RFM::duplicate_file($base_dir . $config['fixed_image_creation_name_to_prepend'][$k] . $info['filename'] . $config['fixed_image_creation_to_append'][$k] . "." . $info['extension'], $config['fixed_image_creation_name_to_prepend'][$k] . $name . $config['fixed_image_creation_to_append'][$k]);
                            }
                        }
                    }
                } else {
                    RFM::response(RFM::fm_trans('Empty_name') . RFM::AddErrorLocation())->send();
                    exit;
                }
            }
            break;

        case 'paste_clipboard':
            if (!(session()->exists('RF.clipboard_action') && session()->exists('RF.clipboard.path'))
                || session('RF.clipboard_action') == ''
                || session('RF.clipboard.path') == '') {
                RFM::response()->send();
                exit;
            }

            $action = session('RF.clipboard_action');
            $data = session('RF.clipboard');


            if ($ftp) {
                if ($_POST['path'] != "") {
                    $path .= DIRECTORY_SEPARATOR;
                    $path_thumb .= DIRECTORY_SEPARATOR;
                }
                $path_thumb .= basename($data['path']);
                $path .= basename($data['path']);
                $data['path_thumb'] = DIRECTORY_SEPARATOR . $config['ftp_base_folder'] . $config['ftp_thumbs_dir'] . $data['path'];
                $data['path'] = DIRECTORY_SEPARATOR . $config['ftp_base_folder'] . $config['upload_dir'] . $data['path'];
            } else {
                $data['path_thumb'] = $config['thumbs_base_path'] . $data['path'];
                $data['path'] = $config['current_path'] . $data['path'];
            }

            $pinfo = pathinfo($data['path']);

            // user wants to paste to the same dir. nothing to do here...
            if ($pinfo['dirname'] == rtrim($path, DIRECTORY_SEPARATOR)) {
                RFM::response()->send();
                exit;
            }

            // user wants to paste folder to it's own sub folder.. baaaah.
            if (is_dir($data['path']) && strpos($path, $data['path']) !== false) {
                RFM::response()->send();
                exit;
            }

            // something terribly gone wrong
            if ($action != 'copy' && $action != 'cut') {
                RFM::response(RFM::fm_trans('wrong action') . RFM::AddErrorLocation())->send();
                exit;
            }
            if ($ftp) {
                if ($action == 'copy') {
                    $tmp = time() . basename($data['path']);
                    $ftp->get($tmp, $data['path'], FTP_BINARY);
                    $ftp->put(DIRECTORY_SEPARATOR . $path, $tmp, FTP_BINARY);
                    unlink($tmp);

                    if (url_exists($data['path_thumb'])) {
                        $tmp = time() . basename($data['path_thumb']);
                        @$ftp->get($tmp, $data['path_thumb'], FTP_BINARY);
                        @$ftp->put(DIRECTORY_SEPARATOR . $path_thumb, $tmp, FTP_BINARY);
                        unlink($tmp);
                    }
                } elseif ($action == 'cut') {
                    $ftp->rename($data['path'], DIRECTORY_SEPARATOR . $path);
                    if (url_exists($data['path_thumb'])) {
                        @$ftp->rename($data['path_thumb'], DIRECTORY_SEPARATOR . $path_thumb);
                    }
                }
            } else {
                // check for writability
                if (RFM::is_really_writable($path) === false || RFM::is_really_writable($path_thumb) === false) {
                    RFM::response(RFM::fm_trans('Dir_No_Write') . '<br/>' . str_replace('../', '', $path) . '<br/>' . str_replace('../', '', $path_thumb) . RFM::AddErrorLocation())->send();
                    exit;
                }

                // check if server disables copy or rename
                if (RFM::is_function_callable(($action == 'copy' ? 'copy' : 'rename')) === false) {
                    RFM::response(sprintf(RFM::fm_trans('Function_Disabled'), ($action == 'copy' ? (RFM::fm_trans('Copy')) : (RFM::fm_trans('Cut')))) . RFM::AddErrorLocation())->send();
                    exit;
                }
                if ($action == 'copy') {
                    list($sizeFolderToCopy, $fileNum, $foldersCount) = RFM::folder_info($path, false);
                    if (!RFM::checkresultingsize($sizeFolderToCopy)) {
                        RFM::response(sprintf(RFM::fm_trans('max_size_reached'), $config['MaxSizeTotal']) . RFM::AddErrorLocation())->send();
                        exit;
                    }
                    RFM::rcopy($data['path'], $path);
                    RFM::rcopy($data['path_thumb'], $path_thumb);
                } elseif ($action == 'cut') {
                    RFM::rrename($data['path'], $path);
                    RFM::rrename($data['path_thumb'], $path_thumb);

					// cleanup
					if (is_dir($data['path']) === TRUE){
						RFM::rrename_after_cleaner($data['path']);
						RFM::rrename_after_cleaner($data['path_thumb']);
					}
				}
			}

            // cleanup
            session()->put('RF.clipboard.path', null);
            session()->put('RF.clipboard_action', null);

            break;

        case 'chmod':
            $mode = $_POST['new_mode'];
            $rec_option = $_POST['is_recursive'];
            $valid_options = array('none', 'files', 'folders', 'both');
            $chmod_perm = ($_POST['folder'] ? $config['chmod_dirs'] : $config['chmod_files']);

            // check perm
            if ($chmod_perm === false) {
                RFM::response(sprintf(RFM::fm_trans('File_Permission_Not_Allowed'), (is_dir($path) ? (RFM::fm_trans('Folders')) : (RFM::fm_trans('Files')))) . RFM::AddErrorLocation())->send();
                exit;
            }
            // check mode
            if (!preg_match("/^[0-7]{3}$/", $mode)) {
                RFM::response(RFM::fm_trans('File_Permission_Wrong_Mode') . RFM::AddErrorLocation())->send();
                exit;
            }
            // check recursive option
            if (!in_array($rec_option, $valid_options)) {
                RFM::response(RFM::fm_trans("wrong option") . RFM::AddErrorLocation())->send();
                exit;
            }
            // check if server disabled chmod
            if (!$ftp && RFM::is_function_callable('chmod') === false) {
                RFM::response(sprintf(RFM::fm_trans('Function_Disabled'), 'chmod') . RFM::AddErrorLocation())->send();
                exit;
            }

            $mode = "0" . $mode;
            $mode = octdec($mode);
            if ($ftp) {
                $ftp->chmod($mode, "/" . $path);
            } else {
                RFM::rchmod($path, $mode, $rec_option);
            }

            break;

        case 'save_text_file':
            $content = $_POST['new_content'];
            // $content = htmlspecialchars($content); not needed
            // $content = stripslashes($content);

            if ($ftp) {
                $tmp = time();
                file_put_contents($tmp, $content);
                $ftp->put("/" . $path, $tmp, FTP_BINARY);
                unlink($tmp);
                RFM::response(RFM::fm_trans('File_Save_OK'))->send();
            } else {
                // no file
                if (!file_exists($path)) {
                    RFM::response(RFM::fm_trans('File_Not_Found') . RFM::AddErrorLocation())->send();
                    exit;
                }

                // not writable or edit not allowed
                if (!is_writable($path) || $config['edit_text_files'] === false) {
                    RFM::response(sprintf(RFM::fm_trans('File_Open_Edit_Not_Allowed'), strtolower(RFM::fm_trans('Edit'))) . RFM::AddErrorLocation())->send();
                    exit;
                }

                if (!RFM::checkresultingsize(strlen($content))) {
                    RFM::response(sprintf(RFM::fm_trans('max_size_reached'), $config['MaxSizeTotal']) . RFM::AddErrorLocation())->send();
                    exit;
                }
                if (@file_put_contents($path, $content) === false) {
                    RFM::response(RFM::fm_trans('File_Save_Error') . RFM::AddErrorLocation())->send();
                    exit;
                } else {
                    RFM::response(RFM::fm_trans('File_Save_OK'))->send();
                    exit;
                }
            }

            break;

        default:
            RFM::response(RFM::fm_trans('wrong action') . RFM::AddErrorLocation())->send();
            exit;
    }
}
