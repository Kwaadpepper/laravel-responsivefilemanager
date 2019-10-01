<!DOCTYPE html>
<html lang="{{ strstr(app()->getLocale(), '_', true) ?? app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <meta name="robots" content="noindex,nofollow">
    <meta name="csrf-token" content="<?php echo csrf_token(); ?>">
    <title>Responsive FileManager</title>
    <link rel="shortcut icon" href="{{ asset('vendor/responsivefilemanager/img/ico/favicon.ico') }}">
    <!-- CSS to style the file input field as button and adjust the Bootstrap progress bars -->
    <link rel="stylesheet" href="{{ asset('vendor/responsivefilemanager/css/jquery.fileupload.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/responsivefilemanager/css/jquery.fileupload-ui.css') }}">
    <!-- CSS adjustments for browsers with JavaScript disabled -->
    <noscript><link rel="stylesheet" href="{{ asset('vendor/responsivefilemanager/css/jquery.fileupload-noscript.css') }}"></noscript>
    <noscript><link rel="stylesheet" href="{{ asset('vendor/responsivefilemanager/css/jquery.fileupload-ui-noscript.css') }}"></noscript>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jplayer/2.9.2/skin/blue.monday/jplayer.blue.monday.min.css" />
    <link rel="stylesheet" href="https://uicdn.toast.com/tui-image-editor/latest/tui-image-editor.css">
    <link href="{{ asset('vendor/responsivefilemanager/css/style.css').'?v='.config('rfm.version') }}" rel="stylesheet"
        type="text/css" />
    <!--[if lt IE 8]>
        <style>
            .img-container span, .img-container-mini span {
                display: inline-block;
                height: 100%;
            }
        </style>
        <![endif]-->

    <script src="https://code.jquery.com/jquery-1.12.4.min.js" integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ=" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js" integrity="sha256-VazP97ZCwtekAsvgPBSUwPFKdrwD3unUfSGVYrahUqU=" crossorigin="anonymous"></script>
    <script src="{{ asset('vendor/responsivefilemanager/js/plugins.js').'?v='.config('rfm.version') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jplayer/2.9.2/jplayer/jquery.jplayer.min.js"></script>
    <link type="text/css" href="https://uicdn.toast.com/tui-color-picker/latest/tui-color-picker.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/3.4.0/fabric.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/1.3.3/FileSaver.min.js"></script>
    <script src="https://uicdn.toast.com/tui.code-snippet/latest/tui-code-snippet.js"></script>
    <script src="https://uicdn.toast.com/tui-color-picker/latest/tui-color-picker.js"></script>
    <script src="https://uicdn.toast.com/tui-image-editor/latest/tui-image-editor.js"></script>
    <script src="{{ asset('vendor/responsivefilemanager/js/modernizr.custom.js').'?v='.config('rfm.version') }}"></script>
    <script src="{{ asset('vendor/responsivefilemanager/js/jquery.fileupload.js').'?v='.config('rfm.version') }}"></script>

    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
        <script src="//cdnjs.cloudflare.com/ajax/libs/html5shiv/3.6.2/html5shiv.js"></script>
        <![endif]-->

    @prepend('scripts')

    {{-- RFM VARS --}}
    <script >
        var ext_img = @json(config('rfm.ext_img'));
        var image_editor = @php echo config('rfm.tui_active') ? "true" : "false"; @endphp;
    </script>

    {{-- RFM JS Main file --}}
    <script src="{{ asset('vendor/responsivefilemanager/js/include.js').'?v='.config('rfm.version') }}"></script>

    @endprepend
</head>
<body>
    @includeWhen(config('rfm.upload_files'), 'vendor/rfm/upload')
    <div class="container-fluid">
        @include('vendor.rfm.header')
        @include('vendor.rfm.breadcrumb')
        <div class="row-fluid ff-container">
            <div class="span12">
                @if ($uploadFolderError)
                    <div class="alert alert-error">There is an error! The upload folder there isn't. Check your config.php file. </div>
                @else
                    <h4 id="help">{{ __('Swipe_help') }}</h4>
                    @if (config('rfm.folder_message'))
                        <div class="alert alert-block">{{ config('rfm.folder_message') }}</div>
                    @endif
                    @if (config('rfm.show_sorting_bar'))
                        @include('vendor.rfm.filemanager.sortbar')
                    @endif

                    <input type="hidden" id="file_number" value="{{ $n_files }}" />
                    <!--ul class="thumbnails ff-items"-->
                    <ul class="grid cs-style-2 list-view{{ $view }}" id="main-item-container">
                        @foreach ($files as $file_array)
                            @php
                                $file=$file_array['file'];
                                if ($file == '.' || ( substr($file, 0, 1) == '.' && isset($file_array[ 'extension' ]) && $file_array[ 'extension' ] == RFM::fixStrtolower(__('Type_dir'))) || (isset($file_array['extension']) && $file_array['extension']!=RFM::fixStrtolower(__('Type_dir'))) || ($file == '..' && $subdir == '') || in_array($file, config('rfm.hidden_folders')) || ($filter!='' && $n_files>config('rfm.file_number_limit_js') && $file!=".." && stripos($file, $filter)===false)) {
                                    continue;
                                }
                                $new_name=RFM::fixGetParams($file, $config);
                                if ($ftp && $file!='..' && $file!=$new_name) {
                                    //rename
                                    RFM::renameFolder(config('rfm.current_path').$subdir.$file, $new_name, $ftp, $config);
                                    $file=$new_name;
                                }
                                //add in thumbs folder if not exist
                                if ( $file != '..' ) {
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
                            @endphp
                            <li data-name="{{ $file }}" class="@if ($file == '..') back @else dir @endif @if(!config('rfm.multiple_selection')) no-selector @endif" @if (($filter!='' && stripos($file, $filter)===false)) style="display:none;" @endif>
                            @php
                                $file_prevent_rename = false;
                                $file_prevent_delete = false;
                                if (isset($filePermissions[$file])) {
                                    $file_prevent_rename = isset($filePermissions[$file]['prevent_rename']) && $filePermissions[$file]['prevent_rename'];
                                    $file_prevent_delete = isset($filePermissions[$file]['prevent_delete']) && $filePermissions[$file]['prevent_delete'];
                                }
                            @endphp
                            <figure data-name="{{ $file }}"
                                    data-path="@if($ftp) {{ route('RFMView', ['ox' => encrypt(['path' => config('rfm.upload_dir').$rfm_subfolder.$subdir.$file, 'name' => $file])]) }} @else {{ $rfm_subfolder.$subdir.$file }} @endif"
                                    class="@if($file == "..") back-directory @else directory @endif" data-type="@if($file != "..") dir @endif">
                                @if($file == "..")
                                    <input type="hidden" class="path" value="{{ str_replace('.', '', dirname($rfm_subfolder.$subdir)) }}" />
                                    <input type="hidden" class="path_thumb" value="{{ dirname($thumbs_path)."/" }}" />
                                @endif

                                <a class="folder-link" href="{{ route('RFMInterface', $get_params).rawurlencode($src).'&'.($callback?'callback='.$callback.'&':'').uniqid() }}">
                                <div class="img-precontainer">
                                    <div class="img-container directory"><span></span>
                                        <img class="directory-img" src="#" alt="directory" data-src="{{ $vendor_path }}img/{{ config('rfm.icon_theme') }}/folder{{ ($file=="..") ? "_back" : '' }}.png" />
                                    </div>
                                </div>
                                <div class="img-precontainer-mini directory">
                                    <div class="img-container-mini">
                                        <span></span>
                                        <img class="directory-img" src="#" alt="directory" data-src="{{ $vendor_path }}img/{{ config('rfm.icon_theme') }}/folder{{ ($file=='..' ? '_back' : '') }}.png" />
                                    </div>
                                </div>
                                @if ($file=="..")
                                    <div class="box no-effect">
                                        <h4>{{ __('Back') }}</h4>
                                    </div>
                                    </a>
                                @else
                                    </a>
                                    <div class="box">
                                        <h4 class="@if (config('rfm.ellipsis_title_after_first_row')) ellipsis @endif">
                                            <a class="folder-link" data-file="{{ $file }}"
                                                href="{{ route('RFMInterface', $get_params).rawurlencode($src)."&".uniqid() }}">{{ $file }}</a>
                                        </h4>
                                    </div>
                                    <input type="hidden" class="name" value="{{ $file_array['file_lcase'] }}" />
                                    <input type="hidden" class="date" value="{{ $file_array['date'] }}" />
                                    <input type="hidden" class="size" value="{{ $file_array['size'] }}" />
                                    <input type="hidden" class="extension"
                                        value="{{ RFM::fixStrtolower(__('Type_dir')) }}" />
                                    <div class="file-date">{{ date(__('Date_type'), $file_array['date']) }}</div>
                                    @if (config('rfm.show_folder_size'))
                                        <div class="file-size">{{ RFM::makeSize($file_array['size']) }}</div>
                                        <input type="hidden" class="nfiles" value="{{ $file_array['nfiles'] }}"/>
                                        <input type="hidden" class="nfolders" value="{{ $file_array['nfolders'] }}"/>
                                    @endif
                                    <div class='file-extension'>{{ RFM::fixStrtolower(__('Type_dir')) }}</div>
                                    <figcaption>
                                    <a href="javascript:void('')"
                                        class="tip-left edit-button rename-file-paths @if(config('rfm.rename_folders') && !$file_prevent_rename) rename-folder @endif"
                                        title="{{ __('Rename') }}" data-folder="1"
                                        data-permissions="{{ $file_array['permissions'] }}">
                                        <i class="icon-pencil @if (!config('rfm.rename_folders') || $file_prevent_rename) icon-white @endif"></i>
                                    </a>
                                    <a href="javascript:void('')" class="tip-left erase-button @if(config('rfm.delete_folders') && !$file_prevent_delete) delete-folder @endif"
                                        title="{{ __('Erase') }}"
                                        data-confirm="{{ __('Confirm_Folder_del') }}">
                                        <i class="icon-trash @if(!config('rfm.delete_folders') || $file_prevent_delete) icon-white @endif"></i>
                                    </a>
                                    </figcaption>
                                @endif
                            </figure>
                            </li>
                        @endforeach
                        <?php
                            $files_prevent_duplicate = array();
                            foreach ($files as $nu => $file_array) {
                                $file=$file_array['file'];
                
                                if ($file == '.' || $file == '..' || $file_array['extension']==RFM::fixStrtolower(__('Type_dir')) || !RFM::checkExtension($file_array['extension'], $config) || ($filter!='' && $n_files>config('rfm.file_number_limit_js') && stripos($file, $filter)===false)) {
                                    continue;
                                }
                                foreach (config('rfm.hidden_files') as $hidden_file) {
                                    if (fnmatch($hidden_file, $file, FNM_PATHNAME)) {
                                        continue 2;
                                    }
                                }
                                $filename=substr($file, 0, '-' . (strlen($file_array['extension']) + 1));
                                if (strlen($file_array['extension'])===0) {
                                    $filename = $file;
                                }
                                if (!$ftp) {
                                    $file_path='/'.config('rfm.current_path').$rfm_subfolder.$subdir.$file;
                                    //check if file have illegal caracter
                
                                    if ($file!=RFM::fixGetParams($file, $config)) {
                                        $file1=RFM::fixGetParams($file, $config);
                                        $file_path1=(config('rfm.current_path').$rfm_subfolder.$subdir.$file1);
                                        if (file_exists($file_path1)) {
                                            $i = 1;
                                            $info=pathinfo($file1);
                                            while (file_exists(config('rfm.current_path').$rfm_subfolder.$subdir.$info['filename'].".[".$i."].".$info['extension'])) {
                                                $i++;
                                            }
                                            $file1=$info['filename'].".[".$i."].".$info['extension'];
                                            $file_path1=(config('rfm.current_path').$rfm_subfolder.$subdir.$file1);
                                        }
                
                                        $filename=substr($file1, 0, '-' . (strlen($file_array['extension']) + 1));
                                        if (strlen($file_array['extension'])===0) {
                                            $filename = $file1;
                                        }
                                        RFM::renameFile($file_path, RFM::fixGetParams($filename, $config), $ftp, $config);
                                        $file=$file1;
                                        $file_array['extension']=RFM::fixGetParams($file_array['extension'], $config);
                                        $file_path=$file_path1;
                                    }
                                } else {
                                    $file_path = route('RFMView').'?ox='.encrypt(['path' => config('rfm.upload_dir').$rfm_subfolder.$subdir.$file, 'name' => $file]);
                                }
                
                                $is_img=false;
                                $is_video=false;
                                $is_audio=false;
                                $show_original=false;
                                $show_original_mini=false;
                                $mini_src="";
                                $src_thumb="";
                                if (in_array($file_array['extension'], config('rfm.ext_img'))) {
                                    $src = $file_path;
                                    $is_img=true;
                
                                    $img_width = $img_height = "";
                                    if ($ftp) {
                                        /**
                                            * Can't preview for now images with FTP since not necessarely available through HTTP
                                            * disabling for now
                                            * TODO: cache FTP thumbnails for preview
                                            */
                                        $mini_src = $src_thumb = route('RFMView').'?ox='.encrypt(['path' => config('rfm.ftp_thumbs_dir').$subdir. $file, 'name' => $file]);
                                        $creation_thumb_path = "/".config('rfm.ftp_base_folder').config('rfm.ftp_thumbs_dir').$subdir. $file;
                                    } else {
                                        $creation_thumb_path = $mini_src = $src_thumb = $thumbs_path. $file;
                
                                        if (!file_exists($src_thumb)) {
                                            if (!RFM::createImg($ftp, __DIR__.'/'.$file_path, __DIR__.'/'.$creation_thumb_path, 122, 91, 'crop', $config)) {
                                                $src_thumb = $mini_src = "";
                                            }
                                        }
                                        //check if is smaller than thumb
                                        list($img_width, $img_height, $img_type, $attr)=@getimagesize($file_path);
                                        if ($img_width<122 && $img_height<91) {
                                            $src_thumb=$file_path;
                                            $show_original=true;
                                        }
                
                                        if ($img_width<45 && $img_height<38) {
                                            $mini_src=config('rfm.current_path').$rfm_subfolder.$subdir.$file;
                                            $show_original_mini=true;
                                        }
                                    }
                                }
                                $is_icon_thumb=false;
                                $is_icon_thumb_mini=false;
                                $no_thumb=false;
                                if ($src_thumb=="") {
                                    $no_thumb=true;
                                    if (file_exists(__DIR__.'/img/'.config('rfm.icon_theme').'/'.$file_array['extension'].".jpg")) {
                                        $src_thumb =  $vendor_path.'img/'.config('rfm.icon_theme').'/'.$file_array['extension'].".jpg";
                                    } else {
                                        $src_thumb =  $vendor_path."img/".config('rfm.icon_theme')."/default.jpg";
                                    }
                                    $is_icon_thumb=true;
                                } else {
                                    // is FTP but not support ted thumbnail generated (only gif,jpeg,png)
                                    if (!preg_match('/(gif|jpe?g|png)$/i', $file_array['extension'])) {
                                        $src_thumb =  $vendor_path.'img/'.config('rfm.icon_theme').'/'.$file_array['extension'].".jpg";
                                        $is_icon_thumb=true;
                                    }
                                }
                                if ($mini_src=="") {
                                    $is_icon_thumb_mini=false;
                                }
                
                                $class_ext=0;
                                if (in_array($file_array['extension'], config('rfm.ext_video'))) {
                                    $class_ext = 4;
                                    $is_video=true;
                                } elseif (in_array($file_array['extension'], config('rfm.ext_img'))) {
                                    $class_ext = 2;
                                } elseif (in_array($file_array['extension'], config('rfm.ext_music'))) {
                                    $class_ext = 5;
                                    $is_audio=true;
                                } elseif (in_array($file_array['extension'], config('rfm.ext_misc'))) {
                                    $class_ext = 3;
                                } else {
                                    $class_ext = 1;
                                }
                                if ((!($_GET['type']==1 && !$is_img) && !(($_GET['type']==3 && !$is_video) && ($_GET['type']==3 && !$is_audio))) && $class_ext>0) {
                                    ?>
                                <li class="ff-item-type-<?php echo $class_ext;?> file 
                                    <?php if (!config('rfm.multiple_selection')) {
                                        ?>no-selector<?php
                                    } ?>" data-name="<?php echo $file;?>" <?php if (($filter!='' && stripos($file, $filter)===false)) {
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
                                    }?>
                                    <figure data-name="<?php echo $file ?>"
                                        data-path="@if($ftp) {{ route('RFMView', ['ox' => encrypt(['path' => config('rfm.upload_dir').$rfm_subfolder.$subdir.$file, 'name' => $file])]) }} @else {{ $rfm_subfolder.$subdir.$file }} @endif"
                                        data-type="
                                                <?php if ($is_img) {
                                                    echo "img";
                                                } else {
                                                    echo "file";
                                                } ?>">
                                        <?php if (config('rfm.multiple_selection')) {
                                                    ?><div class="selector">
                                            <label class="cont">
                                                <input type="checkbox" class="selection" name="selection[]"
                                                    value="<?php echo $file;?>">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <?php } ?>
                                        <a href="javascript:void('')" class="link" data-file="<?php echo $file;?>"
                                            data-function="<?php echo $apply;?>">
                                            <div class="img-precontainer">
                                                <?php if ($is_icon_thumb) {
                                                    ?><div class="filetype"><?php echo $file_array['extension'] ?></div><?php
                                                } ?>

                                                <div class="img-container">
                                                    <img src="#" alt="{{ $file_array['extension'] }}" class="{{ ($show_original ? "original" : "").($is_icon_thumb ? " icon" : "") }}" data-src="{{ (in_array($file_array['extension'], config('rfm.editable_text_file_exts')) ?  '' : '').$src_thumb }}">
                                                </div>
                                            </div>
                                            <div class="img-precontainer-mini {{ (($is_img) ? 'original-thumb' : '') }}">
                                                <div class="filetype {{ $file_array['extension'] }} {{ ((in_array($file_array['extension'], config('rfm.editable_text_file_exts'))) ? 'edit-text-file-allowed' : '') }} {{ ((!$is_icon_thumb) ? 'hide' : '') }}">{{ $file_array['extension'] }}</div>
                                                <div class="img-container-mini">
                                                    @if($mini_src != "")
                                                    <img src="#" alt="{{ $file_array['extension'] }}" class="{{ $show_original_mini ? "original" : "" }}{{ $is_icon_thumb_mini ? " icon" : "" }}" data-src="{{ (in_array($file_array['extension'], config('rfm.editable_text_file_exts')) ? '' : '').$mini_src }}">
                                                    @endif
                                                </div>
                                            </div>
                                            @if ($is_icon_thumb)
                                                <div class="cover"></div>
                                            @endif
                                            <div class="box">
                                                <h4 class="{{ ((config('rfm.ellipsis_title_after_first_row')) ? 'ellipsis' : '') }}">{{ $filename }}</h4>
                                            </div>
                                        </a>
                                        <input type="hidden" class="date" value="{{ $file_array['date'] }}" />
                                        <input type="hidden" class="size" value="{{ $file_array['size'] }}" />
                                        <input type="hidden" class="extension" value="{{ $file_array['extension'] }}" />
                                        <input type="hidden" class="name" value="{{ $file_array['file_lcase'] }}" />
                                        <div class="file-date">{{ date(__('Date_type'), $file_array['date']) }}</div>
                                        <div class="file-size">{{ RFM::makeSize($file_array['size']) }}</div>
                                        <div class='img-dimension'>{{ (($is_img) ? $img_width."x".$img_height : '') }}</div>
                                        <div class='file-extension'>{{ $file_array['extension'] }}</div>
                                        <figcaption>
                                            <form action="force_download.php" method="post" class="download-form"
                                                id="form{{ $nu }}">
                                                <input type="hidden" name="path" value="{{ $rfm_subfolder.$subdir }}" />
                                                <input type="hidden" class="name_download" name="name" value="{{ $file }}" />
                                                <input type="hidden" name="_token" value="{{ csrf_token() }}" />

                                                <a title="{{ __('Download') }}" class="tip-right" href="javascript:void('')" @if(config('rfm.download_files')) onclick="$('#form{{ $nu }}').submit();" @endif>
                                                    <i class="icon-download @if(!config('rfm.download_files')) icon-white @endif"></i>
                                                </a>

                                                @switch(true)
                                                    @case(($is_img && $src_thumb!=""))
                                                        <a class="tip-right preview" title="{{ __('Preview') }}" data-featherlight="image" href="{{ $src }}"><i class=" icon-eye-open"></i></a>
                                                        @break
                                                    @case(($is_video || $is_audio) && in_array($file_array['extension'], config('rfm.jplayer_exts')))
                                                        <a class="tip-right modalAV {{ ($is_audio ? 'audio' : 'video' ) }}" title="{{ __('Preview') }}" data-url="ajax_calls.php?action=media_preview&title={{ $filename }}&file={{ $rfm_subfolder.$subdir.$file }}" href="javascript:void('');"><i class=" icon-eye-open"></i></a>
                                                        @break
                                                    @case((in_array($file_array['extension'], config('rfm.cad_exts'))))
                                                        <a class="tip-right file-preview-btn" title="{{ __('Preview') }}" data-url="ajax_calls.php?action=cad_preview&title={{ $filename }}&file={{ $rfm_subfolder.$subdir.$file }}" href="javascript:void('');"><i class=" icon-eye-open"></i></a>
                                                        @break
                                                    @case((config('rfm.preview_text_files') && in_array($file_array['extension'], config('rfm.previewable_text_file_exts'))))
                                                        <a class="tip-right file-preview-btn" title="{{ __('Preview') }}" data-url="ajax_calls.php?action=get_file&sub_action=preview&preview_mode=text&title={{ $filename }}&file={{ $rfm_subfolder.$subdir.$file }}" href="javascript:void('');"><i class=" icon-eye-open"></i></a>
                                                        @break
                                                    @case((config('rfm.googledoc_enabled') && in_array($file_array['extension'], config('rfm.googledoc_file_exts'))))
                                                        <a class="tip-right file-preview-btn" title="{{ __('Preview') }}" data-url="ajax_calls.php?action=get_file&sub_action=preview&preview_mode=google&title={{ $filename }}&file={{ $rfm_subfolder.$subdir.$file }}" href="docs.google.com;"><i class=" icon-eye-open"></i></a>
                                                        @break
                                                    @default
                                                        <a class="preview disabled"><i class="icon-eye-open icon-white"></i></a>
                                                @endswitch
                                                <a href="javascript:void('')" class="tip-left edit-button rename-file-paths {{ ((config('rfm.rename_files') && !$file_prevent_rename) ? 'rename-file' : '') }}" title="{{ __('Rename') }}" data-folder="0" data-permissions="{{ $file_array['permissions'] }}">
                                                    <i class="icon-pencil {{ ((!config('rfm.rename_files') || $file_prevent_rename) ? 'icon-white' : '') }}"></i>
                                                </a>
                                                <a href="javascript:void('')" class="tip-left erase-button {{ (config('rfm.delete_files') && !$file_prevent_delete) ? 'delete-file' : '' }}" title="{{ __('Erase') }}" data-confirm="{{ __('Confirm_del') }}">
                                                    <i class="icon-trash {{ ((!config('rfm.delete_files') || $file_prevent_delete) ? 'icon-white' : '') }}"></i>
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
                @endif
            </div>
        </div>
    </div>

    <script>
        var files_prevent_duplicate = [];
        @foreach ($files_prevent_duplicate as $key => $value)
        files_prevent_duplicate[{{ $key }}] = '{{  $value }}';
        @endforeach
    </script>
    @include('vendor.rfm.filemanager.loadingscreen')
    @include('vendor.rfm.filemanager.videoplayer')
        
    @if (config('rfm.tui_active'))
        <div id="tui-image-editor" style="height: 800px;" class="hide">
            <canvas></canvas>
        </div>
        <script >
            var tuiTheme = {
                <?php foreach (config('rfm.tui_defaults_config') as $aopt_key => $aopt_val) {
                    if (!empty($aopt_val)) {
                        echo "'$aopt_key':".json_encode($aopt_val).",";
                    }
                } ?>
            }; 
        </script>
        <script >
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
                            menuBarPosition: '<?php echo config('rfm.tui_position') ?>'
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
                        url: "ajax_calls.php?action=save_img",
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
    @endif

    <script>
        var ua = navigator.userAgent.toLowerCase();
        var isAndroid = ua.indexOf("android") > -1; //&& ua.indexOf("mobile");
        if (isAndroid) {
            $('li').draggable({disabled: true});
        }
    </script>

    @stack('scripts')
</body>

</html>