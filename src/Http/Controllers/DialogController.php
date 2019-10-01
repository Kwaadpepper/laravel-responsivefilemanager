<?php

namespace Kwaadpepper\ResponsiveFileManager\Controller;

use App\Http\Controllers\Controller;
use Closure;
use Illuminate\Http\Request;
use Kwaadpepper\ResponsiveFileManager\RFM;

/**
 * @author Jérémy Munsch
 */
class DialogController extends Controller
{

    public function index(Request $request)
    {
        // Autorise user when first visiting dialog.php
        session()->put('RF.verify', "RESPONSIVEfilemanager");

        // Init Vars
        $config = config('rfm');
        $ftp = null;
        try {
            $ftp = RFM::ftpCon($config);
        } catch (\Throwable $th) {
            //throw $th;
        }

        $base_url = config('rfm.base_url');
        $upload_dir = config('rfm.upload_dir');


        $field_id = RFM::fixGetParams($request->get('field_id', null));
        $type_param = RFM::fixGetParams($request->get('type')); // Should be int
        $return_relative_url = (bool)filter_var($request->get('relative_url'), FILTER_VALIDATE_INT) ? 1 : 0;

        /**
         * Get from Config
         * @var int $duplicate
         * @throws Exception
         */
        $duplicate = (int)filter_var(config('rfm.duplicate_files'), FILTER_VALIDATE_BOOLEAN);
        /**
         * Get from Config
         * @var int $copy_cut_files_allowed
         * @throws Exception
         */
        $copy_cut_files_allowed = (int)filter_var(config('rfm.copy_cut_files'), FILTER_VALIDATE_BOOLEAN);
        /**
         * Get from Config
         * @var int $copy_cut_dirs_allowed
         * @throws Exception
         */
        $copy_cut_dirs_allowed = (int)filter_var(config('rfm.copy_cut_dirs'), FILTER_VALIDATE_BOOLEAN);
        /**
         * Get from Config
         * @var int $file_number_limit_js
         * @throws Exception
         */
        $file_number_limit_js = (int)filter_var(config('rfm.file_number_limit_js'), FILTER_VALIDATE_INT);
        /**
         * Get from Config
         * @var int $copy_cut_max_size
         * @throws Exception
         */
        $copy_cut_max_size = (int)filter_var(config('rfm.copy_cut_max_size'), FILTER_VALIDATE_INT);
        /**
         * Get from Config
         * @var int $copy_cut_max_count
         * @throws Exception
         */
        $copy_cut_max_count = (int)filter_var(config('rfm.copy_cut_max_count'), FILTER_VALIDATE_INT);
        /**
         * Get from Config
         * @var int $chmod_files_allowed
         * @throws Exception
         */
        $chmod_files_allowed = (int)filter_var(config('rfm.chmod_files'), FILTER_VALIDATE_BOOLEAN);
        /**
         * Get from Config
         * @var int $chmod_dirs_allowed
         * @throws Exception
         */
        $chmod_dirs_allowed = (int)filter_var(config('rfm.chmod_dirs'), FILTER_VALIDATE_BOOLEAN);
        /**
         * Get from Config
         * @var int $edit_text_files_allowed
         * @throws Exception
         */
        $edit_text_files_allowed = (int)filter_var(config('rfm.edit_text_files'), FILTER_VALIDATE_BOOLEAN);
        /**
         * Get from Config
         * @var int $extract_files_allowed
         * @throws Exception
         */
        $extract_files_allowed = (int)filter_var(config('rfm.extract_files'), FILTER_VALIDATE_BOOLEAN);
        /**
         * Get from Config
         * @var bool $transliteration
         */
        $transliteration = filter_var(config('rfm.transliteration'), FILTER_VALIDATE_BOOLEAN);
        /**
         * Get from Config
         * @var bool $convert_spaces
         */
        $convert_spaces = filter_var(config('rfm.convert_spaces'), FILTER_VALIDATE_BOOLEAN);
        /**
         * Get from Config
         * @var string $replace_with
         */
        $replace_with = config('rfm.replace_with', '_');
        /**
         * Get from Config
         * @var bool $lower_case
         */
        $lower_case = filter_var(config('rfm.lower_case'), FILTER_VALIDATE_BOOLEAN);
        /**
         * Get from Config
         * @var bool $show_folder_size
         * @throws Exception
         */
        $show_folder_size = (int)filter_var(config('rfm.show_folder_size'), FILTER_VALIDATE_BOOLEAN);
        /**
         * Get from Config
         * @var bool $transliteration
         * @throws Exception
         */
        $add_time_to_img = (int)filter_var(config('rfm.add_time_to_img'), FILTER_VALIDATE_BOOLEAN);

        $popup = $request->get('popup', 0);
        $popup = !!$popup; //Sanitize popup to boolean

        $crossdomain = $request->has('crossdomain', 0);
        $crossdomain = !!$crossdomain; //Sanitize crossdomain to boolean


        // Callback ? on JS side for what ?
        $callback = $request->get('callback', session('RF.callback', 0));
        session()->put('RF.callback', $callback);

        /**
         * is clipboard active ?
         * @var int $clipboard
         */
        $clipboard = session()->has('RF.clipboard.path') && trim(session('RF.clipboard.path')) != null ? 1 : 0;

        /**
         * View type: default to boxes view -> 0
         * @var int $view
         */
        $view = session('RF.view_type', 0);
        if ($request->has('view')) {
            $view = RFM::fixGetParams($request->get('view', $view));
            $view = (int)filter_var($view, FILTER_VALIDATE_INT); // safe cast to int
            session()->put('RF.view_type', $view);
        }

        /**
         * sort by: name as default -> 'name'
         * @var string $sort_by
         */
        $sort_by = session('RF.sort_by', 'name');
        if ($request->has('sort_by')) {
            $sort_by = RFM::fixGetParams($request->get('sort_by', session('RF.sort_by', 'name')));
            session()->put('RF.sort_by', $sort_by);
        }

        /**
         * Goes with sort_by
         * descending: 1 as default -> 1
         * @var int $descending
         */
        $descending = session('RF.descending', 1);
        if ($request->has('descending')) {
            $descending = RFM::fixGetParams($request->get('descending', $descending));
            $descending = (int)filter_var($descending, FILTER_VALIDATE_INT); // safe cast to int
            session()->put('RF.descending', $descending);
        }

        /**
         * filter
         */
        $filter = session('RF.filter', '');
        if ($request->has('filter')) {
            $filter = RFM::fixGetParams($request->get('filter', $filter));
            session()->put('RF.filter', $filter);
        }

        // Eclaircir ce que fait ce bout de code ?
        if ($request->has('editor')) {
            $editor = $request->get('editor');
        } else {
            $editor = $request->get('type') ? null : 'tinymce';
        }

        // defaults to '' (empty string)
        $subdir_path = rawurldecode(trim($request->get('fldr', session('RF.fldr', '')), '/'));

        $subdir = '';
        if (RFM::checkRelativePath($subdir_path)) {
            $subdir = $subdir_path . "/";
            session()->put('RF.fldr', $subdir_path);
            session()->put('RF.filter', '');
        } elseif (strpos(session('RF.last_position', ''), '.') === false) {
            $subdir = trim(session('RF.last_position', ''));
        }

        //remember last position
        session('RF.last_position', $subdir);

        // Est-ce que c'est un Hack ?
        if ($subdir == "/") {
            $subdir = "";
        }

        // If hidden folders are specified (must be an array)
        if (count(config('rfm.hidden_folders', array()))) {
            // If hidden folder appears in the path specified in URL parameter "fldr"
            $dirs = explode('/', $subdir);
            foreach ($dirs as $dir) {
                if ($dir !== '' && in_array($dir, config('rfm.hidden_folders', array()))) {
                    // Ignore the path
                    $subdir = "";
                    break;
                }
            }
        }

        $multiple = null;
        $apply = null;

        if ($request->has('multiple')) {
            $multiple = $request->get('multiple') == 1 ? 1 : 0;
            config('rfm.multiple_selection', (bool)$multiple);
            config('rfm.multiple_selection_action_button', (bool)$multiple);
            if ($multiple) {
                $apply = 'apply_multiple';
            }
        }

        switch ($type_param) {
            case 1:
                $apply_type = 'apply_img';
                break;
            case 2:
                $apply_type = 'apply_link';
                break;
            case 3:
                $apply_type = 'apply_video';
                break;
            case 0:
                if (!$field_id) {
                    $apply_type = 'apply_none';
                    break;
                }
                // no break
            default:
                $apply_type = 'apply';
        }

        if (!$apply) {
            $apply = $apply_type;
        }

        /***
         * SUB-DIR CODE
         ***/
        if (!session()->exists('RF.subfolder')) {
            session()->put('RF.subfolder', '');
        }
        $rfm_subfolder = '';

        if (!session()->exists('RF.subfolder')
            && strpos(session('RF.subfolder'), "/") !== 0
            && strpos(session('RF.subfolder'), '.') === false
        ) {
            $rfm_subfolder = session('RF.subfolder');
        }

        if ($rfm_subfolder != "" && $rfm_subfolder[strlen($rfm_subfolder) - 1] != "/") {
            $rfm_subfolder .= "/";
        }

        if (($ftp && !$ftp->isDir(config('rfm.ftp_base_folder') .
                                config('rfm.upload_dir') . $rfm_subfolder . $subdir)) ||
            (!$ftp && !file_exists(config('rfm.current_path') . $rfm_subfolder . $subdir))) {
            $subdir = '';
            $rfm_subfolder = "";
        }

        $cur_dir = config('rfm.upload_dir').$rfm_subfolder.$subdir;
        $cur_dir_thumb = config('rfm.thumbs_upload_dir').$rfm_subfolder.$subdir;
        $thumbs_path = config('rfm.thumbs_base_path').$rfm_subfolder.$subdir;
        $parent = $rfm_subfolder.$subdir;

        if ($ftp) {
            $cur_dir = config('rfm.ftp_base_folder') . $cur_dir;
            $cur_dir_thumb = config('rfm.ftp_base_folder') . $cur_dir_thumb;
            $thumbs_path = str_replace(array('/..', '..'), '', $cur_dir_thumb);
            $parent = config('rfm.ftp_base_folder') . $parent;
        }

        $fldr_value = $subdir;
        $sub_folder = $rfm_subfolder;

        $current_url= str_replace(array(
            '&filter='.$filter,
            '&sort_by='.$sort_by,
            '&descending='.intval($descending)
        ), array(''), config('rfm.base_url').htmlspecialchars($_SERVER['REQUEST_URI']));

        $sizeCurrentFolder = null;
        $fileCurrentNum = null;
        $foldersCurrentCount = null;
        if (config('show_total_size')) {
            list($sizeCurrentFolder, $fileCurrentNum, $foldersCurrentCount) = RFM::folderInfo(
                config('rfm.current_path'),
                false
            );
        }

        // A corriger
        if (!$request->has('type')) {
            $_GET['type'] = 0;
        }

        // A corriger
        // Create Thumbs Path if don't exists
        $vendor_path = parse_url(asset('vendor/responsivefilemanager').'/')['path'];
        if (!$ftp && !is_dir($thumbs_path)) {
            $vendor_path = parse_url(asset('vendor/responsivefilemanager').'/')['path'];
            RFM::createFolder(false, $vendor_path.'/'.$thumbs_path, $ftp, $config);
        }

        $extensions = null;
        if ($request->has('extensions')) {
            $extensions = json_decode(urldecode($request->get('extensions')));
            $ext_tmp = array();
            foreach ($extensions as $extension) {
                $extension = RFM::fixStrtolower($extension);
                if (RFM::checkFileExtension($extension, $config)) {
                    $ext_tmp[] = $extension;
                }
            }
            if ($extensions) {
                $ext = $ext_tmp;
                config()->set('rfm.ext', $ext_tmp);
                config()->set('rfm.show_filter_buttons', false);
            }
        }

        // Pour faire quoi ça ?
        $get_params = array(
            'editor'        => $editor,
            'type'          => $type_param,
            'lang'          => session('RF.language'),
            'popup'         => $popup,
            'crossdomain'   => $crossdomain,
            'extensions'    => ($extensions) ? urlencode(json_encode($extensions)) : null ,
            'field_id'      => $field_id,
            'multiple'      => $multiple,
            'relative_url'  => $return_relative_url,
            'akey'          => $request->get('akey', 'key'),
            'fldr'          => ''
        );
        if ($request->has('CKEditorFuncNum')) {
            $get_params['CKEditorFuncNum'] = $request->get('CKEditorFuncNum');
            $get_params['CKEditor'] = $request->get('CKEditor', '');
        }
        // Pas besoin de ça avec l'api route() => route('nomdelaroute', $get_params)
        // $get_params = http_build_query($get_params);


        /**
         * HTML
         */
        $class_ext = '';
        $src = '';
        if ($ftp) {
            try {
                $files = $ftp->scanDir(config('rfm.ftp_base_folder') . config('rfm.upload_dir') .
                                        $rfm_subfolder . $subdir);
                if (!$ftp->isDir(config('rfm.ftp_base_folder') . config('rfm.ftp_thumbs_dir') .
                                        $rfm_subfolder . $subdir)) {
                    RFM::createFolder(false, config('rfm.ftp_base_folder') . config('rfm.ftp_thumbs_dir') .
                                        $rfm_subfolder . $subdir, $ftp, $config);
                }
            } catch (FtpException $e) {
                echo "Error: ";
                echo $e->getMessage();
                echo "<br/>Please check configurations";
                die();
            }
        } else {
            $files = scandir(config('rfm.current_path') . $rfm_subfolder . $subdir);
        }

        $n_files = count($files);

        //php sorting
        $sorted = array();
        //$current_folder=array();
        //$prev_folder=array();
        $current_files_number = 0;
        $current_folders_number = 0;

        foreach ($files as $k => $file) {
            if ($ftp) {
                $date = strtotime($file['day'] . " " . $file['month'] . " " . date('Y') . " " . $file['time']);
                $size = $file['size'];
                if ($file['type'] == 'file') {
                    $current_files_number++;
                    $file_ext = substr(strrchr($file['name'], '.'), 1);
                    $is_dir = false;
                } else {
                    $current_folders_number++;
                    $file_ext = __('Type_dir');
                    $is_dir = true;
                }
                $sorted[$k] = array(
                    'is_dir' => $is_dir,
                    'file' => $file['name'],
                    'file_lcase' => strtolower($file['name']),
                    'date' => $date,
                    'size' => $size,
                    'permissions' => $file['permissions'],
                    'extension' => RFM::fixStrtolower($file_ext)
                );
            } else {
                if ($file != "." && $file != "..") {
                    if (is_dir(config('rfm.current_path') . $rfm_subfolder . $subdir . $file)) {
                        $date = filemtime(config('rfm.current_path') . $rfm_subfolder . $subdir . $file);
                        $current_folders_number++;
                        if (config('rfm.show_folder_size')) {
                            list($size, $nfiles, $nfolders) = RFM::folderInfo(config('rfm.current_path') .
                                                $rfm_subfolder . $subdir . $file, false);
                        } else {
                            $size = 0;
                        }
                        $file_ext = __('Type_dir');
                        $sorted[$k] = array(
                            'is_dir' => true,
                            'file' => $file,
                            'file_lcase' => strtolower($file),
                            'date' => $date,
                            'size' => $size,
                            'permissions' => '',
                            'extension' => RFM::fixStrtolower($file_ext)
                        );

                        if (config('rfm.show_folder_size')) {
                            $sorted[$k]['nfiles'] = $nfiles;
                            $sorted[$k]['nfolders'] = $nfolders;
                        }
                    } else {
                        $current_files_number++;
                        $file_path = config('rfm.current_path') . $rfm_subfolder . $subdir . $file;
                        $date = filemtime($file_path);
                        $size = filesize($file_path);
                        $file_ext = substr(strrchr($file, '.'), 1);
                        $sorted[$k] = array(
                            'is_dir' => false,
                            'file' => $file,
                            'file_lcase' => strtolower($file),
                            'date' => $date,
                            'size' => $size,
                            'permissions' => '',
                            'extension' => strtolower($file_ext)
                        );
                    }
                }
            }
        }

        $filenameSort = function ($x, $y) use ($descending) {

            if ($x['is_dir'] !== $y['is_dir']) {
                return $y['is_dir'];
            } else {
                return ($descending)
                    ? $x['file_lcase'] < $y['file_lcase']
                    : $x['file_lcase'] >= $y['file_lcase'];
            }
        };

        $dateSort = function ($x, $y) use ($descending) {

            if ($x['is_dir'] !== $y['is_dir']) {
                return $y['is_dir'];
            } else {
                return ($descending)
                    ? $x['date'] < $y['date']
                    : $x['date'] >= $y['date'];
            }
        };

        $sizeSort = function ($x, $y) use ($descending) {

            if ($x['is_dir'] !== $y['is_dir']) {
                return $y['is_dir'];
            } else {
                return ($descending)
                    ? $x['size'] < $y['size']
                    : $x['size'] >= $y['size'];
            }
        };

        $extensionSort = function ($x, $y) use ($descending) {

            if ($x['is_dir'] !== $y['is_dir']) {
                return $y['is_dir'];
            } else {
                return ($descending)
                    ? $x['extension'] < $y['extension']
                    : $x['extension'] >= $y['extension'];
            }
        };

        switch ($sort_by) {
            case 'date':
                usort($sorted, $dateSort);
                break;
            case 'size':
                usort($sorted, $sizeSort);
                break;
            case 'extension':
                usort($sorted, $extensionSort);
                break;
            default:
                usort($sorted, $filenameSort);
                break;
        }

        if ($subdir != "") {
            $sorted = array_merge(array(array('file' => '..')), $sorted);
        }

        $files = $sorted;

        // upload folder Error
        $uploadFolderError = ((
                $ftp &&
                !$ftp->isDir(config('rfm.ftp_base_folder').config('rfm.upload_dir').$rfm_subfolder.$subdir)
            )  || (
                !$ftp &&
                @opendir(config('rfm.current_path').$rfm_subfolder.$subdir)===false)
            );

        // dd(json_encode(compact(
        //     'add_time_to_img',
        //     'apply_type',
        //     'apply',
        //     'callback',
        //     'chmod_dirs_allowed',
        //     'chmod_files_allowed',
        //     'class_ext',
        //     'clipboard',
        //     'config',
        //     'convert_spaces',
        //     'copy_cut_dirs_allowed',
        //     'copy_cut_files_allowed',
        //     'copy_cut_max_count',
        //     'copy_cut_max_size',
        //     'crossdomain',
        //     'cur_dir_thumb',
        //     'cur_dir',
        //     'current_files_number',
        //     'current_folders_number',
        //     'current_url',
        //     'descending',
        //     'dirs',
        //     'duplicate',
        //     'edit_text_files_allowed',
        //     'editor',
        //     'extensions',
        //     'extract_files_allowed',
        //     'field_id',
        //     'file_number_limit_js',
        //     'fileCurrentNum',
        //     'files',
        //     'fldr_value',
        //     'filter',
        //     'foldersCurrentCount',
        //     'ftp',
        //     'get_params',
        //     'get_params',
        //     'lower_case',
        //     'multiple',
        //     'n_files',
        //     'parent',
        //     'popup',
        //     'replace_with',
        //     'return_relative_url',
        //     'rfm_subfolder',
        //     'show_folder_size',
        //     'sizeCurrentFolder',
        //     'sort_by',
        //     'src',
        //     'subdir_path',
        //     'subdir',
        //     'thumbs_path',
        //     'transliteration',
        //     'type_param',
        //     'upload_dir',
        //     'uploadFolderError',
        //     'vendor_path',
        //     'view'
        // )));


        // print view and pass vars
        return view('vendor/rfm/filemanager', compact(
            'add_time_to_img',
            'apply_type',
            'apply',
            'callback',
            'chmod_dirs_allowed',
            'chmod_files_allowed',
            'class_ext',
            'clipboard',
            'config',
            'convert_spaces',
            'copy_cut_dirs_allowed',
            'copy_cut_files_allowed',
            'copy_cut_max_count',
            'copy_cut_max_size',
            'crossdomain',
            'cur_dir_thumb',
            'cur_dir',
            'current_files_number',
            'current_folders_number',
            'current_url',
            'descending',
            'dirs',
            'duplicate',
            'edit_text_files_allowed',
            'editor',
            'extensions',
            'extract_files_allowed',
            'field_id',
            'file_number_limit_js',
            'fileCurrentNum',
            'files',
            'fldr_value',
            'filter',
            'foldersCurrentCount',
            'ftp',
            'get_params',
            'get_params',
            'lower_case',
            'multiple',
            'n_files',
            'parent',
            'popup',
            'replace_with',
            'return_relative_url',
            'rfm_subfolder',
            'show_folder_size',
            'sizeCurrentFolder',
            'sort_by',
            'src',
            'subdir_path',
            'subdir',
            'thumbs_path',
            'transliteration',
            'type_param',
            'upload_dir',
            'uploadFolderError',
            'vendor_path',
            'view'
        ));
    }

    public function indexNew(Request $request)
    {
        // Autorise user when first visiting dialog.php
        session()->put('RF.verify', "RESPONSIVEfilemanager");

        $translations = $this->getAllTranslationsForCurrentLocale();
        return view('vendor/rfm/filemanagerNew', compact([
            'translations'
        ]));
    }

    protected function getAllTranslationsForCurrentLocale() : array
    {
        $closure = function ($locale = null) {
            $this->load('*', '*', $this->locale);
            $langArray = $this->loaded['*']['*'][$this->locale];
            array_walk_recursive($langArray, function (&$item, &$key) {
                $key = strip_tags($key);
                $item = strip_tags($item);
            });
            return $langArray;
        };

        return Closure::bind($closure, app()->app['translator'], 'Illuminate\Translation\Translator')();
    }
}
