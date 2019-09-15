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

use Illuminate\Routing\Matching\UriValidator;
use Illuminate\Support\Facades\Request;
use Kwaadpepper\ResponsiveFileManager\RFM;

$config = config('rfm');

/**
 * Check RF session
 */
if (!session()->exists('RF') || session('RF.verify') != "RESPONSIVEfilemanager") {
    RFM::response(__('forbidden') . RFM::addErrorLocation(), 403)->send();
    exit;
}

if (!RFM::checkRelativePath($_POST['path'])) {
    RFM::response(__('wrong path') . RFM::addErrorLocation())->send();
    exit;
}

$ftp = RFM::ftpCon($config);

$base = config('rfm.current_path');

$path = $base . request()->post('path');

if ($ftp) {
    if (!request()->has('path')) {
        if (!FM_DEBUG_ERROR_MESSAGE) {
            throw new NotFoundHttpException();
        }
        RFM::response(__('no path post param') . RFM::addErrorLocation(), 400)->send();
        exit;
    }

    $info = request()->get('action') !== 'create_folder' ?
            RFM::decrypt(Request::create(request()->post('path'))->get('ox')) : [
                'path' => $config['current_path'].request()->post('path', '')
            ];
    $name = request()->post('name');
    $path = $info['path'];
    $path_thumb = str_replace($config['current_path'], $config['thumbs_base_path'], $path);
}

$returnPaths = function ($_path, $_name, $config) use ($ftp) {
    $path = $config['current_path'] . $_path;
    $path_thumb = $config['thumbs_base_path'] . $_path;
    $name = null;
    if ($ftp) {
        $path = $config['ftp_base_folder'] . $config['upload_dir'] . $_path;
        $path_thumb = $config['ftp_base_folder'] . $config['ftp_thumbs_dir'] . $_path;
    }
    if ($_name) {
        $name = RFM::fixGetParams($_name, $config);
        if (strpos($name, '../') !== false || strpos($name, '..\\') !== false) {
            RFM::response(__('wrong name') . RFM::addErrorLocation())->send();
            exit;
        }
    }
    return array($path, $path_thumb, $name);
};

if (isset($_POST['paths'])) {
    $paths = $paths_thumb = $names = array();
    foreach ($_POST['paths'] as $key => $path) {
        if (!RFM::checkRelativePath($path)) {
            RFM::response(__('wrong path').RFM::addErrorLocation())->send();
            exit;
        }
        $name = null;
        if (isset($_POST['names'][$key])) {
            $name = $_POST['names'][$key];
        }
        list($path,$path_thumb,$name) = $returnPaths($path, $name, $config);
        $paths[] = $path;
        $paths_thumb[] = $path_thumb;
        $names = $name;
    }
} elseif (!$ftp) {
    $name = null;
    if (isset($_POST['name'])) {
        $name = $_POST['name'];
    }
    list($path,$path_thumb,$name) = $returnPaths($_POST['path'], $name, $config);
}

$info = pathinfo($path);
if (isset($info['extension']) && !(isset($_GET['action']) &&
        ($_GET['action'] == 'create_folder') ||
        ($_GET['action'] == 'delete_folder') ||
        ($_GET['action'] == 'rename_folder')
    ) && !RFM::checkExtension($info['extension'], $config)
    && $_GET['action'] != 'create_file') {
    RFM::response(__('wrong extension') . RFM::addErrorLocation())->send();
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
            if ($config['delete_folders']) {
                if ($ftp) {
                    RFM::deleteDir($path, $ftp, $config);
                    RFM::deleteDir($path_thumb, $ftp, $config);
                } else {
                    if (is_dir($path_thumb)) {
                        RFM::deleteDir($path_thumb, null, $config);
                    }

                    if (is_dir($path)) {
                        RFM::deleteDir($path, null, $config);
                        if ($config['fixed_image_creation']) {
                            foreach ($config['fixed_path_from_filemanager'] as $k => $paths) {
                                if ($paths!="" && $paths[strlen($paths)-1] != "/") {
                                    $paths.="/";
                                }

                                $base_dir=$paths.substr_replace($path, '', 0, strlen($config['current_path']));
                                if (is_dir($base_dir)) {
                                    RFM::deleteDir($base_dir, null, $config);
                                }
                            }
                        }
                    }
                }
            }
            break;
        case 'create_folder':
            if ($config['create_folders']) {
                $name = RFM::fixGetParams($_POST['name'], $config);
                $path .= $name;
                $path_thumb .= $name;
                $res = RFM::createFolder(
                    RFM::fixPath($path, $config),
                    RFM::fixPath($path_thumb, $config),
                    $ftp,
                    $config
                );
                if (!$res) {
                    RFM::response(__('Rename_existing_folder').RFM::addErrorLocation())->send();
                }
            }
            break;
        case 'rename_folder':
            if ($config['rename_folders']) {
                if ($ftp && !is_dir($path) && !RFM::ftpIsDir($ftp, $path)) {
                    RFM::response(__('wrong path').RFM::addErrorLocation())->send();
                    exit;
                }
                $name = RFM::fixGetParams($name, $config);
                $name = str_replace('.', '', $name);

                if (!empty($name)) {
                    if (!RFM::renameFolder($path, $name, $ftp, $config)) {
                        RFM::response(__('Rename_existing_folder') . RFM::addErrorLocation())->send();
                        exit;
                    }
                    RFM::renameFolder($path_thumb, $name, $ftp, $config);
                    if (!$ftp && $config['fixed_image_creation']) {
                        foreach ($config['fixed_path_from_filemanager'] as $k => $paths) {
                            if ($paths != "" && $paths[strlen($paths) - 1] != "/") {
                                $paths .= "/";
                            }

                            $base_dir = $paths . substr_replace($path, '', 0, strlen($config['current_path']));
                            RFM::renameFolder($base_dir, $name, $ftp, $config);
                        }
                    }
                } else {
                    RFM::response(__('Empty_name') . RFM::addErrorLocation())->send();
                    exit;
                }
            }
            break;

        case 'create_file':
            if ($config['create_text_files'] === false) {
                RFM::response(__('File_Open_Edit_Not_Allowed', ['sub_action' => strtolower(__('Edit'))]).
                RFM::addErrorLocation())->send();
                exit;
            }

            if (!isset($config['editable_text_file_exts']) || !is_array($config['editable_text_file_exts'])) {
                $config['editable_text_file_exts'] = array();
            }

            // check if user supplied extension
            if (strpos($name, '.') === false) {
                RFM::response(__('No_Extension') . ' '.
                __('Valid_Extensions', [
                    'editable_text_file_exts' => implode(', ', $config['editable_text_file_exts'])
                ]).RFM::addErrorLocation())->send();
                exit;
            }

            // correct name
            $old_name = $name;
            $name = RFM::fixGetParams($name, $config);
            if (empty($name)) {
                RFM::response(__('Empty_name') . RFM::addErrorLocation())->send();
                exit;
            }

            // check extension
            $parts = explode('.', $name);
            if (!in_array(end($parts), $config['editable_text_file_exts'])) {
                RFM::response(
                    __('Error_extension').' '.
                    __('Valid_Extensions', [
                        'editable_text_file_exts' => implode(', ', $config['editable_text_file_exts'])
                    ]).RFM::addErrorLocation(),
                    400
                )->send();
                exit;
            }

            $content = $_POST['new_content'];

            if ($ftp) {
                $temp = tempnam('/tmp', 'RF');
                file_put_contents($temp, $content);
                $ftp->put("/" . $path . $name, $temp, FTP_BINARY);
                unlink($temp);
                RFM::response(__('File_Save_OK'))->send();
            } else {
                if (!RFM::checkresultingsize(strlen($content))) {
                    RFM::response(__('max_size_reached', ['size' => $config['MaxSizeTotal']]).
                    RFM::addErrorLocation())->send();
                    exit;
                }
                // file already exists
                if (file_exists($path . $name)) {
                    RFM::response(__('Rename_existing_file') . RFM::addErrorLocation())->send();
                    exit;
                }

                if (@file_put_contents($path . $name, $content) === false) {
                    RFM::response(__('File_Save_Error') . RFM::addErrorLocation())->send();
                    exit;
                } else {
                    if (RFM::isFunctionCallable('chmod') !== false) {
                        chmod($path . $name, 0644);
                    }
                    RFM::response(__('File_Save_OK'))->send();
                    exit;
                }
            }

            break;

        case 'rename_file':
            if (config('rfm.rename_files')) {
                $name = RFM::fixGetParams($name, config('rfm'));
                if (!empty($name)) {
                    // Rename File
                    if (!RFM::renameFile($path, $name, $ftp, config('rfm'))) {
                        RFM::response(__('Rename_existing_file') . RFM::addErrorLocation())->send();
                        exit;
                    }

                    $fileExt = substr(strrchr(basename($path), '.'), 1);
                    //Rename file thumb if is image
                    if (preg_match('/(gif|jpe?g|png)$/i', $fileExt)) {
                        RFM::renameFile($path_thumb, $name, $ftp, config('rfm'));
                    }

                    if (config('rfm.fixed_image_creation')) {
                        $info = pathinfo($path);

                        foreach (config('rfm.fixed_path_from_filemanager') as $k => $paths) {
                            if ($paths != "" && $paths[strlen($paths) - 1] != "/") {
                                $paths .= "/";
                            }

                            $base_dir = $paths . substr_replace(
                                $info['dirname'] . "/",
                                '',
                                0,
                                strlen(config('rfm.current_path'))
                            );
                            if (file_exists(
                                $base_dir . config('rfm.fixed_image_creation_name_to_prepend.'.$k) .
                                $info['filename'] . config('rfm.fixed_image_creation_to_append.'.$k) .
                                "." . $info['extension']
                            )) {
                                RFM::renameFile(
                                    $base_dir . config('rfm.fixed_image_creation_name_to_prepend.'.$k) .
                                    $info['filename']. config('rfm.fixed_image_creation_to_append.'.$k) .
                                    "." . $info['extension'],
                                    config('rfm.fixed_image_creation_name_to_prepend.'.$k) . $name .
                                    config('rfm.fixed_image_creation_to_append.'.$k),
                                    $ftp,
                                    config('rfm')
                                );
                            }
                        }
                    }
                } else {
                    RFM::response(__('Empty_name') . RFM::addErrorLocation())->send();
                    exit;
                }
            } else {
                RFM::response(__('forbidden') . RFM::addErrorLocation())->send();
            }
            break;

        case 'duplicate_file':
            if ($config['duplicate_files']) {
                $name = RFM::fixGetParams($name, $config);
                if (!empty($name)) {
                    if (!$ftp && !RFM::checkresultingsize(filesize($path))) {
                        RFM::response(__('max_size_reached', ['size' => $config['MaxSizeTotal']]).
                        RFM::addErrorLocation())->send();
                        exit;
                    }
                    if (!RFM::duplicateFile($path, $name, $ftp, $config)) {
                        RFM::response(__('Rename_existing_file') . RFM::addErrorLocation())->send();
                        exit;
                    }

                    RFM::duplicateFile($path_thumb, $name, $ftp, $config);

                    if (!$ftp && $config['fixed_image_creation']) {
                        $info = pathinfo($path);
                        foreach ($config['fixed_path_from_filemanager'] as $k => $paths) {
                            if ($paths != "" && $paths[strlen($paths) - 1] != "/") {
                                $paths .= "/";
                            }

                            $base_dir = $paths . substr_replace(
                                $info['dirname'] . "/",
                                '',
                                0,
                                strlen($config['current_path'])
                            );

                            if (file_exists(
                                $base_dir . $config['fixed_image_creation_name_to_prepend'][$k] .
                                $info['filename'] . $config['fixed_image_creation_to_append'][$k] .
                                "." . $info['extension']
                            )) {
                                RFM::duplicateFile(
                                    $base_dir . $config['fixed_image_creation_name_to_prepend'][$k] .
                                    $info['filename'] . $config['fixed_image_creation_to_append'][$k] .
                                    "." . $info['extension'],
                                    $config['fixed_image_creation_name_to_prepend'][$k] . $name .
                                    $config['fixed_image_creation_to_append'][$k]
                                );
                            }
                        }
                    }
                } else {
                    RFM::response(__('Empty_name') . RFM::addErrorLocation())->send();
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
                $data['path_thumb'] =   DIRECTORY_SEPARATOR . $config['ftp_base_folder'] .
                                        $config['ftp_thumbs_dir'] . $data['path'];
                $data['path'] = DIRECTORY_SEPARATOR . $config['ftp_base_folder'] .
                                $config['upload_dir'] . $data['path'];
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
                RFM::response(__('wrong action') . RFM::addErrorLocation())->send();
                exit;
            }
            if ($ftp) {
                if ($action == 'copy') {
                    $tmp = time() . basename($data['path']);
                    $ftp->get($tmp, $data['path'], FTP_BINARY);
                    $ftp->put(DIRECTORY_SEPARATOR . $path, $tmp, FTP_BINARY);
                    unlink($tmp);

                    if (RFM::urlExists($data['path_thumb'], $ftp)) {
                        $tmp = time() . basename($data['path_thumb']);
                        @$ftp->get($tmp, $data['path_thumb'], FTP_BINARY);
                        @$ftp->put(DIRECTORY_SEPARATOR . $path_thumb, $tmp, FTP_BINARY);
                        unlink($tmp);
                    }
                } elseif ($action == 'cut') {
                    if (RFM::urlExists($data['path'], $ftp)) {
                        $i = 0;
                        $path = pathinfo($path);
                        $rn = function ($i, $path) {
                            return  $path['dirname'].'/'.$path['filename'].
                                    ($i > 0 ? '('.$i.')' : '').'.'.$path['extension'];
                        };
                        do {
                            $rpath = $rn($i, $path);
                        } while ($i++ < 99 && RFM::urlExists(DIRECTORY_SEPARATOR . $rpath, $ftp));
                        $path = $rpath;
                        @$ftp->rename($data['path'], DIRECTORY_SEPARATOR . $path);
                    }
                    if (RFM::urlExists($data['path_thumb'], $ftp)) {
                        $i = 0;
                        $path_thumb = pathinfo($path_thumb);
                        $rn = function ($i, $path_thumb) {
                            return  $path_thumb['dirname'].'/'.$path_thumb['filename'].
                                    ($i > 0 ? '('.$i.')' : '').'.'.$path_thumb['extension'];
                        };
                        do {
                            $rpath_thumb = $rn($i, $path_thumb);
                        } while ($i++ < 99 && RFM::urlExists(DIRECTORY_SEPARATOR . $rpath_thumb, $ftp));
                        $path_thumb = $rpath_thumb;
                        @$ftp->rename($data['path_thumb'], DIRECTORY_SEPARATOR . $path_thumb);
                    }
                }
            } else {
                // check for writability
                if (RFM::isReallyWritable($path) === false || RFM::isReallyWritable($path_thumb) === false) {
                    RFM::response(
                        __('Dir_No_Write') . '<br/>' .
                        str_replace('../', '', $path) . '<br/>' .
                        str_replace('../', '', $path_thumb) . RFM::addErrorLocation()
                    )->send();
                    exit;
                }

                // check if server disables copy or rename
                if (RFM::isFunctionCallable(($action == 'copy' ? 'copy' : 'rename')) === false) {
                    RFM::response(__(
                        'Function_Disabled',
                        ['function' => ($action == 'copy' ? (__('Copy')) : (__('Cut')))]
                    ).RFM::addErrorLocation())->send();
                    exit;
                }
                if ($action == 'copy') {
                    list($sizeFolderToCopy, $fileNum, $foldersCount) = RFM::folderInfo($path, false);
                    if (!RFM::checkresultingsize($sizeFolderToCopy)) {
                        RFM::response(__('max_size_reached', ['size' => $config['MaxSizeTotal']]).
                        RFM::addErrorLocation())->send();
                        exit;
                    }
                    RFM::rcopy($data['path'], $path);
                    RFM::rcopy($data['path_thumb'], $path_thumb);
                } elseif ($action == 'cut') {
                    RFM::rrename($data['path'], $path);
                    RFM::rrename($data['path_thumb'], $path_thumb);

                    // cleanup
                    if (is_dir($data['path']) === true) {
                        RFM::rrenameAfterCleaner($data['path']);
                        RFM::rrenameAfterCleaner($data['path_thumb']);
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
                RFM::response(__(
                    'File_Permission_Not_Allowed',
                    ['folder_permissions' => (is_dir($path) ? (__('Folders')) : (__('Files')))]
                ) .RFM::addErrorLocation())->send();
                exit;
            }
            // check mode
            if (!preg_match("/^[0-7]{3}$/", $mode)) {
                RFM::response(__('File_Permission_Wrong_Mode') . RFM::addErrorLocation())->send();
                exit;
            }
            // check recursive option
            if (!in_array($rec_option, $valid_options)) {
                RFM::response(__("wrong option") . RFM::addErrorLocation())->send();
                exit;
            }
            // check if server disabled chmod
            if (!$ftp && RFM::isFunctionCallable('chmod') === false) {
                RFM::response(__('Function_Disabled', ['function' => 'chmod']).RFM::addErrorLocation())->send();
                exit;
            }

            $mode = "0" . $mode;
            $mode = octdec($mode);
            if ($ftp) {
                try {
                    $ftp->chmod($mode, "/" . $path);
                } catch (\Throwable $th) {
                    if ($th->getMessage() === "ftp_chmod(): Command not implemented for that parameter") {
                        RFM::response(__('ftp_cant_chmod').RFM::addErrorLocation())->send();
                        exit;
                    } else {
                        throw $th;
                    }
                }
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
                RFM::response(__('File_Save_OK'))->send();
            } else {
                // no file
                if (!file_exists($path)) {
                    RFM::response(__('File_Not_Found') . RFM::addErrorLocation())->send();
                    exit;
                }

                // not writable or edit not allowed
                if (!is_writable($path) || $config['edit_text_files'] === false) {
                    RFM::response(__(
                        'File_Open_Edit_Not_Allowed',
                        ['sub_action' => strtolower(__('Edit'))]
                    ).RFM::addErrorLocation())->send();
                    exit;
                }

                if (!RFM::checkresultingsize(strlen($content))) {
                    RFM::response(__('max_size_reached', ['size' => $config['MaxSizeTotal']]).
                    RFM::addErrorLocation())->send();
                    exit;
                }
                if (@file_put_contents($path, $content) === false) {
                    RFM::response(__('File_Save_Error') . RFM::addErrorLocation())->send();
                    exit;
                } else {
                    RFM::response(__('File_Save_OK'))->send();
                    exit;
                }
            }

            break;

        default:
            RFM::response(__('wrong action') . RFM::addErrorLocation())->send();
            exit;
    }
}
