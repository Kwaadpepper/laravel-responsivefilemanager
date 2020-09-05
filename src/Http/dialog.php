<?php

use \FtpClient\FtpException;
use \Illuminate\Support\Facades\App;

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

use \Kwaadpepper\ResponsiveFileManager\RFM;

session()->start();

// Autorise user when first visiting dialog.php
session()->put('RF.verify', "RESPONSIVEfilemanager");

$config = config('rfm');
$version = config('rfm.version');

if (session()->has('RF.composerVersion')) {
    $composerVersion = json_decode(file_get_contents(__DIR__ . '/../../composer.json'))->version;
    session()->put('RF.composerVersion', $composerVersion);
} else {
    $composerVersion = session()->get('RF.composerVersion');
}

$time = time();

$vendor_path = parse_url(asset('vendor/responsivefilemanager').'/')['path'];


if (defined('FM_USE_ACCESS_KEYS') && FM_USE_ACCESS_KEYS == true) {
    if (!isset($_GET['akey'], $config['access_keys']) || empty($config['access_keys'])) {
        die('Access Denied!');
    }

    $_GET['akey'] = strip_tags(preg_replace("/[^a-zA-Z0-9\._-]/", '', $_GET['akey']));

    if (!in_array($_GET['akey'], $config['access_keys'])) {
        die('Access Denied!');
    }
}

if (isset($_POST['submit'])) {
    include __DIR__.'/upload.php';
}

$subdir_path = '';

if (isset($_GET['fldr']) && !empty($_GET['fldr'])) {
    $subdir_path = rawurldecode(trim(strip_tags($_GET['fldr']), "/"));
} elseif (session()->has('RF.fldr') && !session()->has('RF.fldr')) {
    $subdir_path = rawurldecode(trim(strip_tags(session('RF.fldr')), "/"));
}

if (RFM::checkRelativePath($subdir_path)) {
    $subdir = strip_tags($subdir_path) . "/";
    session()->put('RF.fldr', $subdir_path);
    session()->put('RF.filter', '');
} else {
    $subdir = '';
}

if ($subdir == "") {
    if (!empty($_COOKIE['last_position']) && strpos($_COOKIE['last_position'], '.') === false) {
        $subdir = trim($_COOKIE['last_position']);
    }
}
//remember last position
setcookie('last_position', $subdir, time() + (86400 * 7));

if ($subdir == "/") {
    $subdir = "";
}

// If hidden folders are specified
if (count($config['hidden_folders'])) {
    // If hidden folder appears in the path specified in URL parameter "fldr"
    $dirs = explode('/', $subdir);
    foreach ($dirs as $dir) {
        if ($dir !== '' && in_array($dir, $config['hidden_folders'])) {
            // Ignore the path
            $subdir = "";
            break;
        }
    }
}

if ($config['show_total_size']) {
    list($sizeCurrentFolder, $fileCurrentNum, $foldersCurrentCount) = RFM::folderInfo($config['current_path'], false);
}

/***
 * SUB-DIR CODE
 ***/
if (!session()->has('RF.subfolder')) {
    session()->put('RF.subfolder', '');
}
$rfm_subfolder = '';

if (session()->has('RF.subfolder')
    && strpos(session('RF.subfolder'), "/") !== 0
    && strpos(session('RF.subfolder'), '.') === false
) {
    $rfm_subfolder = session('RF.subfolder');
}

if ($rfm_subfolder != "" && $rfm_subfolder[strlen($rfm_subfolder) - 1] != "/") {
    $rfm_subfolder .= "/";
}

$ftp = RFM::ftpCon($config);

if (($ftp && !$ftp->isDir($config['ftp_base_folder'] . $config['upload_dir'] . $rfm_subfolder . $subdir)) ||
    (!$ftp && !file_exists($config['current_path'] . $rfm_subfolder . $subdir))) {
    $subdir = '';
    $rfm_subfolder = "";
}

//kent
$storage_url    = $config['storage_url'].$rfm_subfolder.$subdir;
$cur_dir        = $config['upload_dir'].$rfm_subfolder.$subdir;
$cur_dir_thumb  = $config['thumbs_upload_dir'].$rfm_subfolder.$subdir;
$thumbs_path    = $config['thumbs_base_path'].$rfm_subfolder.$subdir;
$parent         = $rfm_subfolder.$subdir;

if ($ftp) {
    $cur_dir = $config['ftp_base_folder'] . $cur_dir;
    $cur_dir_thumb = $config['ftp_base_folder'] . $cur_dir_thumb;
    $thumbs_path = str_replace(array('/..', '..'), '', $cur_dir_thumb);
    $parent = $config['ftp_base_folder'] . $parent;
}

if (!$ftp) {
    $cycle = true;
    $max_cycles = 50;
    $i = 0;
    while ($cycle && $i < $max_cycles) {
        $i++;

        if ($parent == "./") {
            $parent = "";
        }

        if (file_exists($config['current_path'] . $parent . "config.php")) {
            $configTemp = include $config['current_path'] . $parent . 'config.php';
            $config = array_merge($config, $configTemp);
            $cycle = false;
        }

        if ($parent == "") {
            $cycle = false;
        } else {
            $parent = RFM::fixDirname($parent) . "/";
        }
    }

    if (!is_dir($thumbs_path)) {
        RFM::createFolder(false, $thumbs_path, $ftp, $config);
    }
}

$multiple = null;

if (isset($_GET['multiple'])) {
    if ($_GET['multiple'] == 1) {
        $multiple = 1;
        $config['multiple_selection'] = true;
        $config['multiple_selection_action_button'] = true;
    } elseif ($_GET['multiple'] == 0) {
        $multiple = 0;
        $config['multiple_selection'] = false;
        $config['multiple_selection_action_button'] = false;
    }
}

if (isset($_GET['callback'])) {
    $callback = strip_tags($_GET['callback']);
    session()->put('RF.callback', $callback);
} else {
    $callback = 0;

    if (session()->has('RF.callback', $callback)) {
        $callback = session('RF.callback');
    }
}

$popup = isset($_GET['popup']) ? strip_tags($_GET['popup']) : 0;
//Sanitize popup
$popup = !!$popup;

$crossdomain = isset($_GET['crossdomain']) ? strip_tags($_GET['crossdomain']) : 0;
//Sanitize crossdomain
$crossdomain=!!$crossdomain;

//view type
if (!session()->has('RF.view_type')) {
    $view = $config['default_view'];
    session()->put('RF.view_type', $view);
}

if (isset($_GET['view'])) {
    $view = fixGetParams($_GET['view']);
    session()->put('RF.view_type', $view);
}

$view = session('RF.view_type');

//filter
$filter = "";
if (session()->has('RF.filter')) {
    $filter = session('RF.filter');
}

if (isset($_GET["filter"])) {
    $filter = RFM::fixGetParams($_GET["filter"]);
}

if (!session()->has('RF.sort_by')) {
    session()->put('RF.sort_by', 'name');
}

if (isset($_GET["sort_by"])) {
    session()->put('RF.sort_by', RFM::fixGetParams($_GET["sort_by"]));
    $sort_by = session('RF.sort_by');
} else {
    $sort_by = session('RF.sort_by');
}


if (!session()->has('RF.descending')) {
    session()->put('RF.descending', true);
}

if (isset($_GET["descending"])) {
    session()->put('RF.descending', RFM::fixGetParams($_GET["descending"]) == 1);
    $descending = session('RF.descending');
} else {
    $descending = session('RF.descending');
}

$boolarray = array(false => 'false', true => 'true');

$return_relative_url = isset($_GET['relative_url']) && $_GET['relative_url'] == "1" ? true : false;

if (!isset($_GET['type'])) {
    $_GET['type'] = 0;
}

$extensions = null;
if (isset($_GET['extensions'])) {
    $extensions = json_decode(urldecode($_GET['extensions']));
    $ext_tmp = array();
    foreach ($extensions as $extension) {
        $extension = RFM::fixStrtolower($extension);
        if (RFM::checkFileExtension($extension, $config)) {
            $ext_tmp[] = $extension;
        }
    }
    if ($extensions) {
        $ext = $ext_tmp;
        $config['ext'] = $ext_tmp;
        $config['show_filter_buttons'] = false;
    }
}

if (isset($_GET['editor'])) {
    $editor = strip_tags($_GET['editor']);
} else {
    $editor = $_GET['type'] == 0 ? null : 'tinymce';
}

$field_id = isset($_GET['field_id']) ? RFM::fixGetParams($_GET['field_id']) : null;
$type_param = RFM::fixGetParams($_GET['type']);
$apply = null;

if ($multiple) {
    $apply = 'apply_multiple';
}

if ($type_param == 1) {
    $apply_type = 'apply_img';
} elseif ($type_param == 2) {
    $apply_type = 'apply_link';
} elseif ($type_param == 0 && !$field_id) {
    $apply_type = 'apply_none';
} elseif ($type_param == 3) {
    $apply_type = 'apply_video';
} else {
    $apply_type = 'apply';
}

if (!$apply) {
    $apply = $apply_type;
}

$get_params = array(
    'editor'        => $editor,
    'type'          => $type_param,
    'lang'          => 'en_EN',
    'popup'         => $popup,
    'crossdomain'   => $crossdomain,
    'extensions'    => ($extensions) ? urlencode(json_encode($extensions)) : null ,
    'field_id'      => $field_id,
    'multiple'      => $multiple,
    'relative_url'  => $return_relative_url,
    'akey'          => (isset($_GET['akey']) && $_GET['akey'] != '' ? $_GET['akey'] : 'key')
);
if (isset($_GET['CKEditorFuncNum'])) {
    $get_params['CKEditorFuncNum'] = $_GET['CKEditorFuncNum'];
    $get_params['CKEditor'] = (isset($_GET['CKEditor']) ? $_GET['CKEditor'] : '');
}
$get_params['fldr'] ='';

$get_params = http_build_query($get_params);
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
        <meta name="robots" content="noindex,nofollow">
        <meta name="csrf-token" content="<?php echo csrf_token(); ?>">
        <title>Responsive FileManager</title>
        <link rel="shortcut icon" href="<?php echo $vendor_path; ?>img/ico/favicon.ico">
        <!-- CSS to style the file input field as button and adjust the Bootstrap progress bars -->
        <link rel="stylesheet" href="<?php echo $vendor_path; ?>css/jquery.fileupload.css">
        <link rel="stylesheet" href="<?php echo $vendor_path; ?>css/jquery.fileupload-ui.css">
        <!-- CSS adjustments for browsers with JavaScript disabled -->
        <noscript><link rel="stylesheet" href="<?php echo $vendor_path; ?>css/jquery.fileupload-noscript.css"></noscript>
        <noscript><link rel="stylesheet" href="<?php echo $vendor_path; ?>css/jquery.fileupload-ui-noscript.css"></noscript>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jplayer/2.7.1/skin/blue.monday/jplayer.blue.monday.min.css" />
        <link rel="stylesheet" href="https://uicdn.toast.com/tui-image-editor/latest/tui-image-editor.css">
        <link href="<?php echo $vendor_path; ?>css/style.css?v=<?php echo $version; ?>" rel="stylesheet" type="text/css" />
        <!--[if lt IE 8]>
        <style>
            .img-container span, .img-container-mini span {
                display: inline-block;
                height: 100%;
            }
        </style>
        <![endif]-->

        <script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
        <script src="<?php echo $vendor_path; ?>js/plugins.js?v=<?php echo $version; ?>"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jplayer/2.9.2/jplayer/jquery.jplayer.min.js"></script>
        <link type="text/css" href="https://uicdn.toast.com/tui-color-picker/v2.2.0/tui-color-picker.css" rel="stylesheet">
        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/1.6.7/fabric.js"></script>
        <script type="text/javascript" src="https://uicdn.toast.com/tui.code-snippet/v1.5.0/tui-code-snippet.min.js"></script>
        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/1.3.3/FileSaver.min.js"></script>
        <script type="text/javascript" src="https://uicdn.toast.com/tui-color-picker/v2.2.0/tui-color-picker.js"></script>
        <script src="https://uicdn.toast.com/tui-image-editor/latest/tui-image-editor.js"></script>
        <script src="<?php echo $vendor_path; ?>js/modernizr.custom.js"></script>

        <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
        <!--[if lt IE 9]>
        <script src="//cdnjs.cloudflare.com/ajax/libs/html5shiv/3.6.2/html5shiv.js"></script>
        <![endif]-->

        <script type="text/javascript">
            var ext_img=new Array('<?php echo implode("','", $config['ext_img'])?>');
            var image_editor= <?php echo $config['tui_active']?"true":"false";?>;
        </script>

        
        <script src="<?php echo $vendor_path; ?>js/include.js?v=<?php echo $version; ?>"></script>
</head>
<body>
    <!-- The Templates plugin is included to render the upload/download listings -->
    <script src="//blueimp.github.io/JavaScript-Templates/js/tmpl.min.js"></script>
    <!-- The Load Image plugin is included for the preview images and image resizing functionality -->
    <script src="//blueimp.github.io/JavaScript-Load-Image/js/load-image.all.min.js"></script>
    <!-- The Canvas to Blob plugin is included for image resizing functionality -->
    <script src="//blueimp.github.io/JavaScript-Canvas-to-Blob/js/canvas-to-blob.min.js"></script>
    <!-- The Iframe Transport is required for browsers without support for XHR file uploads -->
    <script src="<?php echo $vendor_path; ?>js/jquery.iframe-transport.js"></script>
    <!-- The basic File Upload plugin -->
    <script src="<?php echo $vendor_path; ?>js/jquery.fileupload.js"></script>
    <!-- The File Upload processing plugin -->
    <script src="<?php echo $vendor_path; ?>js/jquery.fileupload-process.js"></script>
    <!-- The File Upload image preview & resize plugin -->
    <script src="<?php echo $vendor_path; ?>js/jquery.fileupload-image.js"></script>
    <!-- The File Upload audio preview plugin -->
    <script src="<?php echo $vendor_path; ?>js/jquery.fileupload-audio.js"></script>
    <!-- The File Upload video preview plugin -->
    <script src="<?php echo $vendor_path; ?>js/jquery.fileupload-video.js"></script>
    <!-- The File Upload validation plugin -->
    <script src="<?php echo $vendor_path; ?>js/jquery.fileupload-validate.js"></script>
    <!-- The File Upload user interface plugin -->
    <script src="<?php echo $vendor_path; ?>js/jquery.fileupload-ui.js"></script>

    <input type="hidden" id="ftp" value="<?php echo !!$ftp; ?>" />
    <input type="hidden" id="popup" value="<?php echo $popup;?>" />
    <input type="hidden" id="callback" value="<?php echo $callback; ?>" />
    <input type="hidden" id="crossdomain" value="<?php echo $crossdomain;?>" />
    <input type="hidden" id="editor" value="<?php echo $editor;?>" />
    <input type="hidden" id="view" value="<?php echo $view;?>" />
    <input type="hidden" id="subdir" value="<?php echo $subdir;?>" />
    <input type="hidden" id="field_id" value="<?php echo $field_id;?>" />
    <input type="hidden" id="multiple" value="<?php echo $multiple;?>" />
    <input type="hidden" id="type_param" value="<?php echo $type_param;?>" />
    <input type="hidden" id="upload_dir" value="<?php echo $config['upload_dir'];?>" />
    <input type="hidden" id="cur_dir" value="<?php echo $cur_dir;?>" />
    <input type="hidden" id="storage_url" value="<?php echo $storage_url;?>" />
    <input type="hidden" id="cur_dir_thumb" value="<?php echo $cur_dir_thumb;?>" />
    <input type="hidden" id="insert_folder_name" value="<?php echo __('Insert_Folder_Name');?>" />
    <input type="hidden" id="rename_existing_folder" value="<?php echo __('Rename_existing_folder');?>" />
    <input type="hidden" id="new_folder" value="<?php echo __('New_Folder');?>" />
    <input type="hidden" id="ok" value="<?php echo __('OK');?>" />
    <input type="hidden" id="cancel" value="<?php echo __('Cancel');?>" />
    <input type="hidden" id="rename" value="<?php echo __('Rename');?>" />
    <input type="hidden" id="lang_duplicate" value="<?php echo __('Duplicate');?>" />
    <input type="hidden" id="duplicate" value="<?php if ($config['duplicate_files']) {
        echo 1;
                                               } else {
                                                   echo 0;
                                               }?>" />
    <input type="hidden" id="base_url" value="<?php echo $config['base_url']?>"/>
    <input type="hidden" id="fldr_value" value="<?php echo $subdir;?>"/>
    <input type="hidden" id="sub_folder" value="<?php echo $rfm_subfolder;?>"/>
    <input type="hidden" id="return_relative_url" value="<?php echo $return_relative_url == true ? 1 : 0;?>"/>
    <input type="hidden" id="file_number_limit_js" value="<?php echo $config['file_number_limit_js'];?>" />
    <input type="hidden" id="sort_by" value="<?php echo $sort_by;?>" />
    <input type="hidden" id="descending" value="<?php echo $descending?1:0;?>" />
    <input type="hidden" id="current_url" value="<?php echo str_replace(array('&filter='.$filter,'&sort_by='.$sort_by,'&descending='.intval($descending)), array(''), $config['base_url'].htmlspecialchars($_SERVER['REQUEST_URI']));?>" />
    <input type="hidden" id="lang_show_url" value="<?php echo __('Show_url');?>" />
    <input type="hidden" id="copy_cut_files_allowed" value="<?php if ($config['copy_cut_files']) {
        echo 1;
                                                            } else {
                                                                echo 0;
                                                            }?>" />
    <input type="hidden" id="copy_cut_dirs_allowed" value="<?php if ($config['copy_cut_dirs']) {
        echo 1;
                                                           } else {
                                                               echo 0;
                                                           }?>" />
    <input type="hidden" id="copy_cut_max_size" value="<?php echo $config['copy_cut_max_size'];?>" />
    <input type="hidden" id="copy_cut_max_count" value="<?php echo $config['copy_cut_max_count'];?>" />
    <input type="hidden" id="lang_copy" value="<?php echo __('Copy');?>" />
    <input type="hidden" id="lang_cut" value="<?php echo __('Cut');?>" />
    <input type="hidden" id="lang_paste" value="<?php echo __('Paste');?>" />
    <input type="hidden" id="lang_paste_here" value="<?php echo __('Paste_Here');?>" />
    <input type="hidden" id="lang_paste_confirm" value="<?php echo __('Paste_Confirm');?>" />
    <input type="hidden" id="lang_files" value="<?php echo __('Files');?>" />
    <input type="hidden" id="lang_folders" value="<?php echo __('Folders');?>" />
    <input type="hidden" id="lang_files_on_clipboard" value="<?php echo __('Files_ON_Clipboard');?>" />
    <input type="hidden" id="clipboard" value="<?php echo ((session()->has('RF.clipboard.path') && trim(session('RF.clipboard.path')) != null) ? 1 : 0);?>" />
    <input type="hidden" id="lang_clear_clipboard_confirm" value="<?php echo __('Clear_Clipboard_Confirm');?>" />
    <input type="hidden" id="lang_file_permission" value="<?php echo __('File_Permission');?>" />
    <input type="hidden" id="chmod_files_allowed" value="<?php if ($config['chmod_files']) {
        echo 1;
                                                         } else {
                                                             echo 0;
                                                         }?>" />
    <input type="hidden" id="chmod_dirs_allowed" value="<?php if ($config['chmod_dirs']) {
        echo 1;
                                                        } else {
                                                            echo 0;
                                                        }?>" />
    <input type="hidden" id="lang_lang_change" value="<?php echo __('Lang_Change');?>" />
    <input type="hidden" id="edit_text_files_allowed" value="<?php if ($config['edit_text_files']) {
        echo 1;
                                                             } else {
                                                                 echo 0;
                                                             }?>" />
    <input type="hidden" id="lang_edit_file" value="<?php echo __('Edit_File');?>" />
    <input type="hidden" id="lang_new_file" value="<?php echo __('New_File');?>" />
    <input type="hidden" id="lang_filename" value="<?php echo __('Filename');?>" />
    <input type="hidden" id="lang_file_info" value="<?php echo RFM::fixStrtoupper(__('File_info'));?>" />
    <input type="hidden" id="lang_edit_image" value="<?php echo __('Edit_image');?>" />
    <input type="hidden" id="lang_error_upload" value="<?php echo __('Error_Upload');?>" />
    <input type="hidden" id="lang_select" value="<?php echo __('Select');?>" />
    <input type="hidden" id="lang_extract" value="<?php echo __('Extract');?>" />
    <input type="hidden" id="extract_files" value="<?php if ($config['extract_files']) {
        echo 1;
                                                   } else {
                                                       echo 0;
                                                   }?>" />
    <input type="hidden" id="transliteration" value="<?php echo $config['transliteration']?"true":"false";?>" />
    <input type="hidden" id="convert_spaces" value="<?php echo $config['convert_spaces']?"true":"false";?>" />
    <input type="hidden" id="replace_with" value="<?php echo $config['convert_spaces']? $config['replace_with'] : "";?>" />
    <input type="hidden" id="lower_case" value="<?php echo $config['lower_case']?"true":"false";?>" />
    <input type="hidden" id="show_folder_size" value="<?php echo $config['show_folder_size'];?>" />
    <input type="hidden" id="add_time_to_img" value="<?php echo $config['add_time_to_img'];?>" />
<?php if ($config['upload_files']) { ?>
<!-- uploader div start -->
<div class="uploader">
    <div class="flex">
        <div class="text-center">
            <button class="btn btn-inverse close-uploader"><i class="icon-backward icon-white"></i> <?php echo __('Return_Files_List')?></button>
        </div>
        <div class="space10"></div>
        <div class="tabbable upload-tabbable"> <!-- Only required for left/right tabs -->
            <div class="container1">
            <ul class="nav nav-tabs">
                <li class="active"><a href="#baseUpload" data-toggle="tab"><?php echo __('Upload_base');?></a></li>
                <?php if ($config['url_upload']) { ?>
                <li><a href="#urlUpload" data-toggle="tab"><?php echo __('Upload_url');?></a></li>
                <?php } ?>
            </ul>
            <div class="tab-content">
                <div class="tab-pane active" id="baseUpload">
                    <!-- The file upload form used as target for the file upload widget -->
                    <form id="fileupload" action="" method="POST" enctype="multipart/form-data">
                        <div class="container2">
                            <div class="fileupload-buttonbar">
                                 <!-- The global progress state -->
                                <div class="fileupload-progress">
                                    <!-- The global progress bar -->
                                    <div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100">
                                        <div class="bar bar-success" style="width:0%;"></div>
                                    </div>
                                    <!-- The extended global progress state -->
                                    <div class="progress-extended"></div>
                                </div>
                                <div class="text-center">
                                    <!-- The fileinput-button span is used to style the file input field as button -->
                                    <span class="btn btn-success fileinput-button">
                                        <i class="glyphicon glyphicon-plus"></i>
                                        <span><?php echo __('Upload_add_files');?></span>
                                        <input type="file" name="files[]" multiple="multiple">
                                    </span>
                                    <button type="submit" class="btn btn-primary start">
                                        <i class="glyphicon glyphicon-upload"></i>
                                        <span><?php echo __('Upload_start');?></span>
                                    </button>
                                    <!-- The global file processing state -->
                                    <span class="fileupload-process"></span>
                                </div>
                            </div>
                            <!-- The table listing the files available for upload/download -->
                            <div id="filesTable">
                                <table role="presentation" class="table table-striped table-condensed small"><tbody class="files"></tbody></table>
                            </div>
                            <div class="upload-help"><?php echo __('Upload_base_help');?></div>
                        </div>
                    </form>
                    <!-- The template to display files available for upload -->
                    <script id="template-upload" type="text/x-tmpl">
                    {% for (var i=0, file; file=o.files[i]; i++) { %}
                        <tr class="template-upload">
                            <td>
                                <span class="preview"></span>
                            </td>
                            <td>
                                <p class="name">{%=file.relativePath%}{%=file.name%}</p>
                                <strong class="error text-danger"></strong>
                            </td>
                            <td>
                                <p class="size">Processing...</p>
                                <div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><div class="bar bar-success" style="width:0%;"></div></div>
                            </td>
                            <td>
                                {% if (!i && !o.options.autoUpload) { %}
                                    <button class="btn btn-primary start" disabled style="display:none">
                                        <i class="glyphicon glyphicon-upload"></i>
                                        <span>Start</span>
                                    </button>
                                {% } %}
                                {% if (!i) { %}
                                    <button class="btn btn-link cancel">
                                        <i class="icon-remove"></i>
                                    </button>
                                {% } %}
                            </td>
                        </tr>
                    {% } %}
                    </script>
                    <!-- The template to display files available for download -->
                    <script id="template-download" type="text/x-tmpl">
                    {% for (var i=0, file; file=o.files[i]; i++) { %}
                        <tr class="template-download">
                            <td>
                                <span class="preview">
                                    {% if (file.error) { %}
                                    <i class="icon icon-remove"></i>
                                    {% } else { %}
                                    <i class="icon icon-ok"></i>
                                    {% } %}
                                </span>
                            </td>
                            <td>
                                <p class="name">
                                    {% if (file.url) { %}
                                        <a href="{%=file.url%}" title="{%=file.name%}" download="{%=file.name%}" {%=file.thumbnailUrl?'data-gallery':''%}>{%=file.name%}</a>
                                    {% } else { %}
                                        <span>{%=file.name%}</span>
                                    {% } %}
                                </p>
                                {% if (file.error) { %}
                                    <div><span class="label label-danger">Error</span> {%=file.error%}</div>
                                {% } %}
                            </td>
                            <td>
                                <span class="size">{%=o.formatFileSize(file.size)%}</span>
                            </td>
                            <td></td>
                        </tr>
                    {% } %}
                    </script>
                </div>
                <?php if ($config['url_upload']) { ?>
                <div class="tab-pane" id="urlUpload">
                    <br/>
                    <form class="form-horizontal">
                        <div class="control-group">
                            <label class="control-label" for="url"><?php echo __('Upload_url');?></label>
                            <div class="controls">
                                <input type="text" class="input-block-level" id="url" placeholder="<?php echo __('Upload_url');?>">
                            </div>
                        </div>
                        <div class="control-group">
                            <div class="controls">
                                <button class="btn btn-primary" id="uploadURL"><?php echo  __('Upload_file');?></button>
                            </div>
                        </div>
                    </form>
                </div>
                <?php } ?>
            </div>
            </div>
        </div>
    </div>
</div>
<!-- uploader div end -->

<?php } ?>
        <div class="container-fluid">

<?php
$class_ext = '';
$src = '';
if ($ftp) {
    try {
        $files = $ftp->scanDir($config['ftp_base_folder'] . $config['upload_dir'] . $rfm_subfolder . $subdir);
        if (!$ftp->isDir($config['ftp_base_folder'] . $config['ftp_thumbs_dir'] . $rfm_subfolder . $subdir)) {
            RFM::createFolder(false, $config['ftp_base_folder'] . $config['ftp_thumbs_dir'] . $rfm_subfolder . $subdir, $ftp, $config);
        }
    } catch (FtpException $e) {
        echo "Error: ";
        echo $e->getMessage();
        echo "<br/>Please check configurations";
        die();
    }
} else {
    //kent
    $files = scandir(storage_path( $config['actual_folder'] ) . $rfm_subfolder . $subdir);
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
            if (is_dir($config['current_path'] . $rfm_subfolder . $subdir . $file)) {
                $date = filemtime($config['current_path'] . $rfm_subfolder . $subdir . $file);
                $current_folders_number++;
                if ($config['show_folder_size']) {
                    list($size, $nfiles, $nfolders) = RFM::folderInfo($config['current_path'] . $rfm_subfolder . $subdir . $file, false);
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

                if ($config['show_folder_size']) {
                    $sorted[$k]['nfiles'] = $nfiles;
                    $sorted[$k]['nfolders'] = $nfolders;
                }
            } else {
                $current_files_number++;
                $file_path = storage_path($config['current_path']) . $rfm_subfolder . $subdir . $file;
                //kent
                $new_file_path = storage_path($config['actual_folder']). $rfm_subfolder . $subdir . $file;
                $date = filemtime($new_file_path);
                
                $size = filesize($new_file_path);
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
?>
<!-- header div start -->
<div class="navbar navbar-fixed-top">
    <div class="navbar-inner">
        <div class="container-fluid">
        <button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        </button>
        <div class="brand"><?php echo __('Toolbar');?></div>
        <div class="nav-collapse collapse">
        <div class="filters">
            <div class="row-fluid">
            <div class="span4 half">
                <?php if ($config['upload_files']) { ?>
                <button class="tip btn upload-btn" title="<?php echo  __('Upload_file');?>"><i class="rficon-upload"></i></button>
                <?php } ?>
                <?php if ($config['create_text_files']) { ?>
                <button class="tip btn create-file-btn" title="<?php echo  __('New_File');?>"><i class="icon-plus"></i><i class="icon-file"></i></button>
                <?php } ?>
                <?php if ($config['create_folders']) { ?>
                <button class="tip btn new-folder" title="<?php echo  __('New_Folder')?>"><i class="icon-plus"></i><i class="icon-folder-open"></i></button>
                <?php } ?>
                <?php if ($config['copy_cut_files'] || $config['copy_cut_dirs']) { ?>
                <button class="tip btn paste-here-btn" title="<?php echo __('Paste_Here');?>"><i class="rficon-clipboard-apply"></i></button>
                <button class="tip btn clear-clipboard-btn" title="<?php echo __('Clear_Clipboard');?>"><i class="rficon-clipboard-clear"></i></button>
                <?php } ?>
                <div id="multiple-selection" style="display:none;">
                <?php if ($config['multiple_selection']) { ?>
                    <?php if ($config['delete_files']) { ?>
                <button class="tip btn multiple-delete-btn" title="<?php echo __('Erase');?>" data-confirm="<?php echo __('Confirm_del');?>"><i class="icon-trash"></i></button>
                    <?php } ?>
                <button class="tip btn multiple-select-btn" title="<?php echo __('Select_All');?>"><i class="icon-check"></i></button>
                <button class="tip btn multiple-deselect-btn" title="<?php echo __('Deselect_All');?>"><i class="icon-ban-circle"></i></button>
                    <?php if ($apply_type!="apply_none" && $config['multiple_selection_action_button']) { ?>
                <button class="btn multiple-action-btn btn-inverse" data-function="<?php echo $apply_type;?>"><?php echo __('Select'); ?></button>
                    <?php } ?>
                <?php } ?>
                </div>
            </div>
            <div class="span2 half view-controller">
                <button class="btn tip<?php if ($view==0) {
                    echo " btn-inverse";
                                      }?>" id="view0" data-value="0" title="<?php echo __('View_boxes');?>"><i class="icon-th <?php if ($view==0) {
                                      echo "icon-white";
                                      }?>"></i></button>
                <button class="btn tip<?php if ($view==1) {
                    echo " btn-inverse";
                                      }?>" id="view1" data-value="1" title="<?php echo __('View_list');?>"><i class="icon-align-justify <?php if ($view==1) {
                                      echo "icon-white";
                                      }?>"></i></button>
                <button class="btn tip<?php if ($view==2) {
                    echo " btn-inverse";
                                      }?>" id="view2" data-value="2" title="<?php echo __('View_columns_list');?>"><i class="icon-fire <?php if ($view==2) {
                                      echo "icon-white";
                                      }?>"></i></button>
            </div>
            <div class="span6 entire types">
                <span><?php echo __('Filters');?>:</span>
                <?php if ($_GET['type']!=1 && $_GET['type']!=3 && $config['show_filter_buttons']) { ?>
                    <?php if (count($config['ext_file'])>0 or false) { ?>
                <input id="select-type-1" name="radio-sort" type="radio" data-item="ff-item-type-1" checked="checked"  class="hide"  />
                <label id="ff-item-type-1" title="<?php echo __('Files');?>" for="select-type-1" class="tip btn ff-label-type-1"><i class="icon-file"></i></label>
                    <?php } ?>
                    <?php if (count($config['ext_img'])>0 or false) { ?>
                <input id="select-type-2" name="radio-sort" type="radio" data-item="ff-item-type-2" class="hide"  />
                <label id="ff-item-type-2" title="<?php echo __('Images');?>" for="select-type-2" class="tip btn ff-label-type-2"><i class="icon-picture"></i></label>
                    <?php } ?>
                    <?php if (count($config['ext_misc'])>0 or false) { ?>
                <input id="select-type-3" name="radio-sort" type="radio" data-item="ff-item-type-3" class="hide"  />
                <label id="ff-item-type-3" title="<?php echo __('Archives');?>" for="select-type-3" class="tip btn ff-label-type-3"><i class="icon-inbox"></i></label>
                    <?php } ?>
                    <?php if (count($config['ext_video'])>0 or false) { ?>
                <input id="select-type-4" name="radio-sort" type="radio" data-item="ff-item-type-4" class="hide"  />
                <label id="ff-item-type-4" title="<?php echo __('Videos');?>" for="select-type-4" class="tip btn ff-label-type-4"><i class="icon-film"></i></label>
                    <?php } ?>
                    <?php if (count($config['ext_music'])>0 or false) { ?>
                <input id="select-type-5" name="radio-sort" type="radio" data-item="ff-item-type-5" class="hide"  />
                <label id="ff-item-type-5" title="<?php echo __('Music');?>" for="select-type-5" class="tip btn ff-label-type-5"><i class="icon-music"></i></label>
                    <?php } ?>
                <?php } ?>
                <input accesskey="f" type="text" class="filter-input <?php echo (($_GET['type']!=1 && $_GET['type']!=3) ? '' : 'filter-input-notype');?>" id="filter-input" name="filter" placeholder="<?php echo RFM::fixStrtolower(__('Text_filter'));?>..." value="<?php echo $filter;?>"/><?php if ($n_files>$config['file_number_limit_js']) {
                    ?><label id="filter" class="btn"><i class="icon-play"></i></label><?php
                                                                     } ?>

                <input id="select-type-all" name="radio-sort" type="radio" data-item="ff-item-type-all" class="hide"  />
                <label id="ff-item-type-all" title="<?php echo __('All');?>" <?php if ($_GET['type']==1 || $_GET['type']==3) {
                    ?>style="visibility: hidden;" <?php
                                                    } ?> data-item="ff-item-type-all" for="select-type-all" style="margin-rigth:0px;" class="tip btn btn-inverse ff-label-type-all"><?php echo __('All');?></label>

            </div>
            </div>
        </div>
        </div>
    </div>
    </div>
</div>

<!-- header div end -->

    <!-- breadcrumb div start -->

    <div class="row-fluid">
    <?php
    $link = "dialog?" . $get_params;
    ?>
    <ul class="breadcrumb">
    <li class="pull-left"><a href="<?php echo $link?>/"><i class="icon-home"></i></a></li>
    <li><span class="divider">/</span></li>
    <?php
    $bc=explode("/", $subdir);
    $tmp_path='';
    if (!empty($bc)) {
        foreach ($bc as $k => $b) {
            $tmp_path.=$b."/";
            if ($k==count($bc)-2) {
                ?> <li class="active"><?php echo $b?></li><?php
            } elseif ($b!="") { ?>
        <li><a href="<?php echo $link.$tmp_path?>"><?php echo $b?></a></li><li><span class="divider"><?php echo "/";?></span></li>
            <?php }
        }
    }
    ?>

    <li class="pull-right"><a class="btn-small" href="javascript:void('')" id="info"><i class="icon-question-sign"></i></a></li>
    <?php if ($config['show_language_selection']) { ?>
    <li class="pull-right"><a class="btn-small" href="javascript:void('')" id="change_lang_btn"><i class="icon-globe"></i></a></li>
    <?php } ?>
    <li class="pull-right"><a id="refresh" class="btn-small" href="dialog?<?php echo $get_params.$subdir."&".uniqid() ?>"><i class="icon-refresh"></i></a></li>

    <li class="pull-right">
        <div class="btn-group">
        <a class="btn dropdown-toggle sorting-btn" data-toggle="dropdown" href="#">
        <i class="icon-signal"></i>
        <span class="caret"></span>
        </a>
        <ul class="dropdown-menu pull-left sorting">
            <li class="text-center"><strong><?php echo __('Sorting') ?></strong></li>
        <li><a class="sorter sort-name <?php if ($sort_by=="name") {
            echo ($descending)?"descending":"ascending";
                                       } ?>" href="javascript:void('')" data-sort="name"><?php echo __('Filename');?></a></li>
        <li><a class="sorter sort-date <?php if ($sort_by=="date") {
            echo ($descending)?"descending":"ascending";
                                       } ?>" href="javascript:void('')" data-sort="date"><?php echo __('Date');?></a></li>
        <li><a class="sorter sort-size <?php if ($sort_by=="size") {
            echo ($descending)?"descending":"ascending";
                                       } ?>" href="javascript:void('')" data-sort="size"><?php echo __('Size');?></a></li>
        <li><a class="sorter sort-extension <?php if ($sort_by=="extension") {
            echo ($descending)?"descending":"ascending";
                                            } ?>" href="javascript:void('')" data-sort="extension"><?php echo __('Type');?></a></li>
        </ul>
        </div>
    </li>
    <li><small class="hidden-phone">(<span id="files_number"><?php echo $current_files_number."</span> ".__('Files')." - <span id='folders_number'>".$current_folders_number."</span> ".__('Folders');?>)</small></li>
    <?php if ($config['show_total_size']) { ?>
    <li><small class="hidden-phone"><span title="<?php echo __('total size').$config['MaxSizeTotal'];?>"><?php echo __('total size').": ".RFM::makeSize($sizeCurrentFolder).(($config['MaxSizeTotal'] !== false && is_int($config['MaxSizeTotal']))? '/'.$config['MaxSizeTotal'].' '.__('MB'):'');?></span></small>
    </li>
    <?php } ?>
    </ul>
    </div>
    <!-- breadcrumb div end -->
    <div class="row-fluid ff-container">
    <div class="span12">
        <?php if (($ftp && !$ftp->isDir($config['ftp_base_folder'].$config['upload_dir'].$rfm_subfolder.$subdir))  || (!$ftp && @opendir(storage_path($config['actual_folder']) . $rfm_subfolder . $subdir)===false)) { ?>
        <br/>
        <div class="alert alert-error">There is an error! The upload folder there isn't. Check your config.php file. </div>
        <?php } else { ?>
        <h4 id="help"><?php echo __('Swipe_help');?></h4>
            <?php if (isset($config['folder_message'])) { ?>
        <div class="alert alert-block"><?php echo $config['folder_message'];?></div>
            <?php } ?>
            <?php if ($config['show_sorting_bar']) { ?>
        <!-- sorter -->
        <div class="sorter-container <?php echo "list-view".$view;?>">
        <div class="file-name"><a class="sorter sort-name <?php if ($sort_by=="name") {
            echo ($descending)?"descending":"ascending";
                                                          } ?>" href="javascript:void('')" data-sort="name"><?php echo __('Filename');?></a></div>
        <div class="file-date"><a class="sorter sort-date <?php if ($sort_by=="date") {
            echo ($descending)?"descending":"ascending";
                                                          } ?>" href="javascript:void('')" data-sort="date"><?php echo __('Date');?></a></div>
        <div class="file-size"><a class="sorter sort-size <?php if ($sort_by=="size") {
            echo ($descending)?"descending":"ascending";
                                                          } ?>" href="javascript:void('')" data-sort="size"><?php echo __('Size');?></a></div>
        <div class='img-dimension'><?php echo __('Dimension');?></div>
        <div class='file-extension'><a class="sorter sort-extension <?php if ($sort_by=="extension") {
            echo ($descending)?"descending":"ascending";
                                                                    } ?>" href="javascript:void('')" data-sort="extension"><?php echo __('Type');?></a></div>
        <div class='file-operations'><?php echo __('Operations');?></div>
        </div>
            <?php } ?>

        <input type="hidden" id="file_number" value="<?php echo $n_files;?>" />
        <!--ul class="thumbnails ff-items"-->
        <ul class="grid cs-style-2 <?php echo "list-view".$view;?>" id="main-item-container">
            <?php


            foreach ($files as $file_array) {
                $file=$file_array['file'];
                if ($file == '.' || ( substr($file, 0, 1) == '.' && isset($file_array[ 'extension' ]) && $file_array[ 'extension' ] == RFM::fixStrtolower(__('Type_dir'))) || (isset($file_array['extension']) && $file_array['extension']!=RFM::fixStrtolower(__('Type_dir'))) || ($file == '..' && $subdir == '') || in_array($file, $config['hidden_folders']) || ($filter!='' && $n_files>$config['file_number_limit_js'] && $file!=".." && stripos($file, $filter)===false)) {
                    continue;
                }
                $new_name=RFM::fixGetParams($file, $config);
                if ($ftp && $file!='..' && $file!=$new_name) {
                    //rename
                    RFM::renameFolder($config['current_path'].$subdir.$file, $new_name, $ftp, $config);
                    $file=$new_name;
                }
                //add in thumbs folder if not exist
                if ($file!='..') {
                    if (!$ftp && !file_exists($thumbs_path.$file)) {
                        RFM::createFolder(false, $thumbs_path.$file, $ftp, $config);
                    }
                }

                $class_ext = 3;
                if ($file=='..' && trim($subdir) != '') {
                    $src = explode("/", $subdir);
                    unset($src[count($src)-2]);
                    $src=implode("/", $src);
                    if ($src=='') {
                        $src="/";
                    }
                } elseif ($file!='..') {
                    $src = $subdir . $file."/";
                }

                ?>
                <li data-name="<?php echo $file ?>" class="<?php if ($file=='..') {
                    echo 'back';
                               } else {
                                   echo 'dir';
                               }?> <?php if (!$config['multiple_selection']) {
    ?>no-selector<?php
                               } ?>" <?php if (($filter!='' && stripos($file, $filter)===false)) {
    echo ' style="display:none;"';
                               }?>><?php
                $file_prevent_rename = false;
                $file_prevent_delete = false;
if (isset($filePermissions[$file])) {
    $file_prevent_rename = isset($filePermissions[$file]['prevent_rename']) && $filePermissions[$file]['prevent_rename'];
    $file_prevent_delete = isset($filePermissions[$file]['prevent_delete']) && $filePermissions[$file]['prevent_delete'];
}
?><figure data-name="<?php echo $file ?>" data-path="<?php echo($ftp?route('FMfview.php').'?ox='.encrypt(['path' => $config['upload_dir'].$rfm_subfolder.$subdir.$file, 'name' => $file]):$rfm_subfolder.$subdir.$file);?>" class="<?php if ($file=="..") {
    echo "back-";
}?>directory" data-type="<?php if ($file!="..") {
    echo "dir";
} ?>">
                <?php if ($file=="..") { ?>
                    <input type="hidden" class="path" value="<?php echo str_replace('.', '', dirname($rfm_subfolder.$subdir));?>"/>
                    <input type="hidden" class="path_thumb" value="<?php echo dirname($thumbs_path)."/";?>"/>
                <?php } ?>
                <a class="folder-link" href="dialog?<?php echo $get_params.rawurlencode($src)."&".($callback?'callback='.$callback."&":'').uniqid() ?>">
                    <div class="img-precontainer">
                            <div class="img-container directory"><span></span>
                            <img class="directory-img" data-src="<?php echo $vendor_path; ?>img/<?php echo $config['icon_theme'];?>/folder<?php if ($file=="..") {
                                echo "_back";
                                                                 }?>.png" />
                            </div>
                    </div>
                    <div class="img-precontainer-mini directory">
                            <div class="img-container-mini">
                            <span></span>
                            <img class="directory-img" data-src="<?php echo $vendor_path; ?>img/<?php echo $config['icon_theme'];?>/folder<?php if ($file=="..") {
                                echo "_back";
                                                                 }?>.png" />
                            </div>
                    </div>
                <?php if ($file=="..") { ?>
                    <div class="box no-effect">
                    <h4><?php echo __('Back') ?></h4>
                    </div>
                    </a>

                <?php } else { ?>
                    </a>
                    <div class="box">
                    <h4 class="<?php if ($config['ellipsis_title_after_first_row']) {
                        echo "ellipsis";
                               } ?>"><a class="folder-link" data-file="<?php echo $file ?>" href="dialog?<?php echo $get_params.rawurlencode($src)."&".uniqid() ?>"><?php echo $file;?></a></h4>
                    </div>
                    <input type="hidden" class="name" value="<?php echo $file_array['file_lcase'];?>"/>
                    <input type="hidden" class="date" value="<?php echo $file_array['date'];?>"/>
                    <input type="hidden" class="size" value="<?php echo $file_array['size'];?>"/>
                    <input type="hidden" class="extension" value="<?php echo RFM::fixStrtolower(__('Type_dir'));?>"/>
                    <div class="file-date"><?php echo date(__('Date_type'), $file_array['date']);?></div>
                    <?php if ($config['show_folder_size']) { ?>
                        <div class="file-size"><?php echo RFM::makeSize($file_array['size']);?></div>
                        <input type="hidden" class="nfiles" value="<?php echo $file_array['nfiles'];?>"/>
                        <input type="hidden" class="nfolders" value="<?php echo $file_array['nfolders'];?>"/>
                    <?php } ?>
                    <div class='file-extension'><?php echo RFM::fixStrtolower(__('Type_dir'));?></div>
                    <figcaption>
                        <a href="javascript:void('')" class="tip-left edit-button rename-file-paths <?php if ($config['rename_folders'] && !$file_prevent_rename) {
                            echo "rename-folder";
                                                                                                    }?>" title="<?php echo __('Rename')?>" data-folder="1" data-permissions="<?php echo $file_array['permissions']; ?>">
                        <i class="icon-pencil <?php if (!$config['rename_folders'] || $file_prevent_rename) {
                            echo 'icon-white';
                                              }?>"></i></a>
                        <a href="javascript:void('')" class="tip-left erase-button <?php if ($config['delete_folders'] && !$file_prevent_delete) {
                            echo "delete-folder";
                                                                                   }?>" title="<?php echo __('Erase')?>" data-confirm="<?php echo __('Confirm_Folder_del');?>" >
                        <i class="icon-trash <?php if (!$config['delete_folders'] || $file_prevent_delete) {
                            echo 'icon-white';
                                             }?>"></i>
                        </a>
                    </figcaption>
                <?php } ?>
                </figure>
            </li>
                <?php
            }


            $files_prevent_duplicate = array();
            foreach ($files as $nu => $file_array) {
                $file=$file_array['file'];

                if ($file == '.' || $file == '..' || $file_array['extension']==RFM::fixStrtolower(__('Type_dir')) || !RFM::checkExtension($file_array['extension'], $config) || ($filter!='' && $n_files>$config['file_number_limit_js'] && stripos($file, $filter)===false)) {
                    continue;
                }
                foreach ($config['hidden_files'] as $hidden_file) {
                    if (fnmatch($hidden_file, $file, FNM_PATHNAME)) {
                        continue 2;
                    }
                }
                $filename=substr($file, 0, '-' . (strlen($file_array['extension']) + 1));
                if (strlen($file_array['extension'])===0) {
                    $filename = $file;
                }
                if (!$ftp) {
                    $file_path='/'.$config['current_path'].$rfm_subfolder.$subdir.$file;
                    //check if file have illegal caracter

                    if ($file!=RFM::fixFilename($file, $config)) {
                        $file1=RFM::fixFilename($file, $config);
                        $file_path1=($config['current_path'].$rfm_subfolder.$subdir.$file1);
                        if (file_exists($file_path1)) {
                            $i = 1;
                            $info=pathinfo($file1);
                            while (file_exists($config['current_path'].$rfm_subfolder.$subdir.$info['filename'].".[".$i."].".$info['extension'])) {
                                $i++;
                            }
                            $file1=$info['filename'].".[".$i."].".$info['extension'];
                            $file_path1=($config['current_path'].$rfm_subfolder.$subdir.$file1);
                        }

                        $filename=substr($file1, 0, '-' . (strlen($file_array['extension']) + 1));
                        if (strlen($file_array['extension'])===0) {
                            $filename = $file1;
                        }
                        RFM::renameFile($file_path, RFM::fixFilename($filename, $config), $ftp, $config);
                        $file=$file1;
                        $file_array['extension']=RFM::fixFilename($file_array['extension'], $config);
                        $file_path=$file_path1;
                    }
                } else {
                    $file_path = route('FMfview').'?ox='.encrypt(['path' => $config['upload_dir'].$rfm_subfolder.$subdir.$file, 'name' => $file]);
                }

                $is_img=false;
                $is_video=false;
                $is_audio=false;
                $show_original=false;
                $show_original_mini=false;
                $mini_src="";
                $src_thumb="";
                if (in_array($file_array['extension'], $config['ext_img'])) {
                    $src = $file_path;
                    $is_img=true;

                    $img_width = $img_height = "";
                    if ($ftp) {
                        /**
                         * Can't preview for now images with FTP since not necessarely available through HTTP
                         * disabling for now
                         * TODO: cache FTP thumbnails for preview
                         */
                        $mini_src = $src_thumb = route('FMfview').'?ox='.encrypt(['path' => $config['ftp_thumbs_dir'].$subdir. $file, 'name' => $file]);
                        $creation_thumb_path = "/".$config['ftp_base_folder'].$config['ftp_thumbs_dir'].$subdir. $file;
                    } else {
                        $creation_thumb_path = $mini_src = $src_thumb = $thumbs_path. $file;

                        if (!file_exists($src_thumb)) {
                            if (!RFM::createImg($ftp, storage_path($config['actual_thumbs_folder']).$file, 122, 91, 'crop', $config)) {
                                $src_thumb = $mini_src = "";
                            }
                        }
                        //check if is smaller than thumb
                        list($img_width, $img_height, $img_type, $attr)=@getimagesize($file_path);
                        if ($img_width<122 && $img_height<91) {
                            //kent
                            $src_thumb=asset($config['storage_url']).'/'.$file;
                            $show_original=true;
                        }

                        if ($img_width<45 && $img_height<38) {
                            //kent
                            $mini_src=asset($config['storage_url']).'/'.$file;
                            $show_original_mini=true;
                        }
                    }
                }
                $is_icon_thumb=false;
                $is_icon_thumb_mini=false;
                $no_thumb=false;
                if ($src_thumb=="") {
                    $no_thumb=true;
                    if (file_exists(__DIR__.'/img/'.$config['icon_theme'].'/'.$file_array['extension'].".jpg")) {
                        $src_thumb =  $vendor_path.'img/'.$config['icon_theme'].'/'.$file_array['extension'].".jpg";
                    } else {
                        $src_thumb =  $vendor_path."img/".$config['icon_theme']."/default.jpg";
                    }
                    $is_icon_thumb=true;
                } else {
                    // is FTP but not support ted thumbnail generated (only gif,jpeg,png)
                    if (!preg_match('/(gif|jpe?g|png)$/i', $file_array['extension'])) {
                        $src_thumb =  $vendor_path.'img/'.$config['icon_theme'].'/'.$file_array['extension'].".jpg";
                        $is_icon_thumb=true;
                    }
                }
                if ($mini_src=="") {
                    $is_icon_thumb_mini=false;
                }

                $class_ext=0;
                if (in_array($file_array['extension'], $config['ext_video'])) {
                    $class_ext = 4;
                    $is_video=true;
                } elseif (in_array($file_array['extension'], $config['ext_img'])) {
                    $class_ext = 2;
                } elseif (in_array($file_array['extension'], $config['ext_music'])) {
                    $class_ext = 5;
                    $is_audio=true;
                } elseif (in_array($file_array['extension'], $config['ext_misc'])) {
                    $class_ext = 3;
                } else {
                    $class_ext = 1;
                }
                if ((!($_GET['type']==1 && !$is_img) && !(($_GET['type']==3 && !$is_video) && ($_GET['type']==3 && !$is_audio))) && $class_ext>0) {
                    ?>
            <li class="ff-item-type-<?php echo $class_ext;?> file <?php if (!$config['multiple_selection']) {
                ?>no-selector<?php
                                    } ?>"  data-name="<?php echo $file;?>" <?php if (($filter!='' && stripos($file, $filter)===false)) {
    echo ' style="display:none;"';
                                    }?>><?php
            $file_prevent_rename = false;
            $file_prevent_delete = false;
if (isset($filePermissions[$file])) {
    if (isset($filePermissions[$file]['prevent_duplicate']) && $filePermissions[$file]['prevent_duplicate']) {
        $files_prevent_duplicate[] = $file;
    }
    $file_prevent_rename = isset($filePermissions[$file]['prevent_rename']) && $filePermissions[$file]['prevent_rename'];
    $file_prevent_delete = isset($filePermissions[$file]['prevent_delete']) && $filePermissions[$file]['prevent_delete'];
}
?>
            <figure data-name="<?php echo $file ?>" data-path="<?php echo($ftp?route('FMfview').'?ox='.encrypt(['path' => $config['upload_dir'].$rfm_subfolder.$subdir.$file, 'name' => $file]):$rfm_subfolder.$subdir.$file);?>" data-type="<?php if ($is_img) {
                echo "img";
                               } else {
                                   echo "file";
                               } ?>">
                    <?php if ($config['multiple_selection']) {
                        ?><div class="selector">
                        <label class="cont">
                            <input type="checkbox" class="selection" name="selection[]" value="<?php echo $file;?>">
                            <span class="checkmark"></span>
                        </label>
                    </div>
                    <?php } ?>
                <a href="javascript:void('')" class="link" data-file="<?php echo $file;?>" data-function="<?php echo $apply;?>">
                <div class="img-precontainer">
                    <?php if ($is_icon_thumb) {
                        ?><div class="filetype"><?php echo $file_array['extension'] ?></div><?php
                    } ?>
                    
                    <div class="img-container">
                        <img class="<?php echo $show_original ? "original" : "" ?><?php echo $is_icon_thumb ? " icon" : "" ?>" data-src="<?php echo (in_array($file_array['extension'], $config['editable_text_file_exts']) ?  '' : '').$src_thumb;?>">
                    </div>
                </div>
                <div class="img-precontainer-mini <?php if ($is_img) {
                    echo 'original-thumb';
                                                  } ?>">
                    <?php if ($config['multiple_selection']) { ?>
                    <?php } ?>
                    <div class="filetype <?php echo $file_array['extension'] ?> <?php if (in_array($file_array['extension'], $config['editable_text_file_exts'])) {
                        echo 'edit-text-file-allowed';
                                         } ?> <?php if (!$is_icon_thumb) {
                        echo "hide";
                                         }?>"><?php echo $file_array['extension'] ?></div>
                    <div class="img-container-mini">
                    <?php if ($mini_src!="") { ?>
                    <img class="<?php echo $show_original_mini ? "original" : "" ?><?php echo $is_icon_thumb_mini ? " icon" : "" ?>" data-src="<?php echo $mini_src;?>">
                    <?php } ?>
                    </div>
                </div>
                    <?php if ($is_icon_thumb) { ?>
                <div class="cover"></div>
                    <?php } ?>
                <div class="box">
                <h4 class="<?php if ($config['ellipsis_title_after_first_row']) {
                    echo "ellipsis";
                           } ?>">
                    <?php echo $filename;?></h4>
                </div></a>
                <input type="hidden" class="date" value="<?php echo $file_array['date'];?>"/>
                <input type="hidden" class="size" value="<?php echo $file_array['size'] ?>"/>
                <input type="hidden" class="extension" value="<?php echo $file_array['extension'];?>"/>
                <input type="hidden" class="name" value="<?php echo $file_array['file_lcase'];?>"/>
                <div class="file-date"><?php echo date(__('Date_type'), $file_array['date'])?></div>
                <div class="file-size"><?php echo RFM::makeSize($file_array['size'])?></div>
                <div class='img-dimension'><?php if ($is_img) {
                    echo $img_width."x".$img_height;
                                           } ?></div>
                <div class='file-extension'><?php echo $file_array['extension'];?></div>
                <figcaption>
                    <form action="force_download" method="post" class="download-form" id="form<?php echo $nu;?>">
                    <input type="hidden" name="path" value="<?php echo $rfm_subfolder.$subdir?>"/>
                    <input type="hidden" class="name_download" name="name" value="<?php echo $file?>"/>
                    <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>"/>

                    <a title="<?php echo __('Download')?>" class="tip-right" href="javascript:void('')" <?php if ($config['download_files']) {
                        echo "onclick=\"$('#form".$nu."').submit();\"";
                              } ?>><i class="icon-download <?php if (!$config['download_files']) {
                              echo 'icon-white';
                              } ?>"></i></a>

                    <?php if ($is_img && $src_thumb!="") { ?>
                    <a class="tip-right preview" title="<?php echo __('Preview')?>" data-featherlight="image" href="<?php echo $src;?>"><i class=" icon-eye-open"></i></a>
                    <?php } elseif (($is_video || $is_audio) && in_array($file_array['extension'], $config['jplayer_exts'])) { ?>
                    <a class="tip-right modalAV <?php if ($is_audio) {
                        echo "audio";
                                                } else {
                                                    echo "video";
                                                } ?>"
                    title="<?php echo __('Preview')?>" data-url="ajax_calls?action=media_preview&title=<?php echo $filename;?>&file=<?php echo $rfm_subfolder.$subdir.$file;?>"
                    href="javascript:void('');" ><i class=" icon-eye-open"></i></a>
                    <?php } elseif (in_array($file_array['extension'], $config['cad_exts'])) { ?>
                    <a class="tip-right file-preview-btn" title="<?php echo __('Preview')?>" data-url="ajax_calls?action=cad_preview&title=<?php echo $filename;?>&file=<?php echo $rfm_subfolder.$subdir.$file;?>"
                    href="javascript:void('');" ><i class=" icon-eye-open"></i></a>
                    <?php } elseif ($config['preview_text_files'] && in_array($file_array['extension'], $config['previewable_text_file_exts'])) { ?>
                    <a class="tip-right file-preview-btn" title="<?php echo __('Preview')?>" data-url="ajax_calls?action=get_file&sub_action=preview&preview_mode=text&title=<?php echo $filename;?>&file=<?php echo $rfm_subfolder.$subdir.$file;?>"
                    href="javascript:void('');" ><i class=" icon-eye-open"></i></a>
                    <?php } elseif ($config['googledoc_enabled'] && in_array($file_array['extension'], $config['googledoc_file_exts'])) { ?>
                    <a class="tip-right file-preview-btn" title="<?php echo __('Preview')?>" data-url="ajax_calls?action=get_file&sub_action=preview&preview_mode=google&title=<?php echo $filename;?>&file=<?php echo $rfm_subfolder.$subdir.$file;?>"
                    href="docs.google.com;" ><i class=" icon-eye-open"></i></a>
                    <?php } else { ?>
                    <a class="preview disabled"><i class="icon-eye-open icon-white"></i></a>
                    <?php } ?>
                    <a href="javascript:void('')" class="tip-left edit-button rename-file-paths <?php if ($config['rename_files'] && !$file_prevent_rename) {
                        echo "rename-file";
                                                                                                }?>" title="<?php echo __('Rename')?>" data-folder="0" data-permissions="<?php echo $file_array['permissions']; ?>">
                    <i class="icon-pencil <?php if (!$config['rename_files'] || $file_prevent_rename) {
                        echo 'icon-white';
                                          }?>"></i></a>

                    <a href="javascript:void('')" class="tip-left erase-button <?php if ($config['delete_files'] && !$file_prevent_delete) {
                        echo "delete-file";
                                                                               }?>" title="<?php echo __('Erase')?>" data-confirm="<?php echo __('Confirm_del');?>">
                    <i class="icon-trash <?php if (!$config['delete_files'] || $file_prevent_delete) {
                        echo 'icon-white';
                                         }?>"></i>
                    </a>
                    </form>
                </figcaption>
            </figure>
        </li>
                    <?php
                }
            }

            ?>
        </ul>
        <?php } ?>
    </div>
    </div>
</div>

<script>
    var files_prevent_duplicate = [];
    <?php foreach ($files_prevent_duplicate as $key => $value) : ?>
    files_prevent_duplicate[<?php echo $key;?>] = '<?php echo $value;?>';
    <?php endforeach;?>
</script>

    <!-- loading div start -->
    <div id="loading_container" style="display:none;">
        <div id="loading" style="background-color:#000; position:fixed; width:100%; height:100%; top:0px; left:0px;z-index:100000"></div>
        <img id="loading_animation" src="<?php echo $vendor_path; ?>img/storing_animation.gif" alt="loading" style="z-index:10001; margin-left:-32px; margin-top:-32px; position:fixed; left:50%; top:50%">
    </div>
    <!-- loading div end -->

    <!-- player div start -->
    <div class="modal hide" id="previewAV">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h3><?php echo __('Preview'); ?></h3>
        </div>
        <div class="modal-body">
            <div class="row-fluid body-preview">
            </div>
        </div>
    </div>

    <!-- player div end -->
    <?php if ($config['tui_active']) { ?>
        <div id="tui-image-editor" style="height: 800px;" class="hide">
            <canvas></canvas>
        </div>

        <script>
            var tuiTheme = {
                <?php foreach ($config['tui_defaults_config'] as $aopt_key => $aopt_val) {
                    if (!empty($aopt_val)) {
                        echo "'$aopt_key':".json_encode($aopt_val).",";
                    }
                } ?>
            }; 
        </script>

        <script>
        if (image_editor) { 
            //TUI initial init with a blank image (Needs to be initiated before a dynamic image can be loaded into it)
            var imageEditor = new tui.ImageEditor('#tui-image-editor', {
                includeUI: {
                     loadImage: {
                        path: 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7',
                        name: 'Blank'
                     },
                     theme: tuiTheme,
                     initMenu: 'filter',
                     menuBarPosition: '<?php echo $config['tui_position'] ?>'
                 },
                cssMaxWidth: 1000, // Component default value: 1000
                cssMaxHeight: 800,  // Component default value: 800
                selectionStyle: {
                    cornerSize: 20,
                    rotxatingPointOffset: 70
                }
            });
            //cache loaded image
            imageEditor.loadImageFromURL = (function() {
                var cached_function = imageEditor.loadImageFromURL;
                function waitUntilImageEditorIsUnlocked(imageEditor) {
                    return new Promise((resolve,reject)=>{
                        const interval = setInterval(()=>{
                            if (!imageEditor._invoker._isLocked) {
                                clearInterval(interval);
                                resolve();
                            }
                        }, 100);
                    })
                }
                return function() {
                    return waitUntilImageEditorIsUnlocked(imageEditor).then(()=>cached_function.apply(this, arguments));
                };
            })();

            //Replace Load button with exit button
            $('.tui-image-editor-header-buttons div').
            replaceWith('<button class="tui-image-editor-exit-btn" ><?php echo __('Image_Editor_Exit');?></button>');
            $('.tui-image-editor-exit-btn').on('click', function() {
                exitTUI();
            });
            //Replace download button with save
            $('.tui-image-editor-download-btn').
            replaceWith('<button class="tui-image-editor-save-btn" ><?php echo __('Image_Editor_Save');?></button>');
            $('.tui-image-editor-save-btn').on('click', function() {
                saveTUI();
            });

            function exitTUI()
            {
                imageEditor.clearObjects();
                imageEditor.discardSelection();
                $('#tui-image-editor').addClass('hide');
            }

            function saveTUI()
            {
                show_animation();
                newURL = imageEditor.toDataURL();
                $.ajax({
                    type: "POST",
                    url: "ajax_calls?action=save_img",
                    data: { url: newURL, path:$('#sub_folder').val()+$('#fldr_value').val(), name:$('#tui-image-editor').attr('data-name'), _token: jQuery('meta[name="csrf-token"]').attr('content') }
                }).done(function( msg ) {
                    exitTUI();
                    d = new Date();
                    $("figure[data-name='"+$('#tui-image-editor').attr('data-name')+"']").find('.img-container img').each(function(){
                    $(this).attr('src',$(this).attr('src')+"?"+d.getTime());
                    });
                    $("figure[data-name='"+$('#tui-image-editor').attr('data-name')+"']").find('figcaption a.preview').each(function(){
                    $(this).attr('data-url',$(this).data('url')+"?"+d.getTime());
                    });
                    hide_animation();
                });
                return false;
            }
        }
        </script>
    <?php } ?>
    <script>
        var ua = navigator.userAgent.toLowerCase();
        var isAndroid = ua.indexOf("android") > -1; //&& ua.indexOf("mobile");
        if (isAndroid) {
            $('li').draggable({disabled: true});
        }
    </script>
    <div id="version" style="display: none;"><?php echo $composerVersion; ?></div>
</body>
</html>

<?php
    session()->save();
?>
