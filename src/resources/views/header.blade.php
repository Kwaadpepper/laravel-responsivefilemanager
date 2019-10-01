<div class="navbar navbar-fixed-top">
    <div class="navbar-inner">
        <div class="container-fluid">
            <button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <div class="brand">{{ __('Toolbar') }}</div>
            <div class="nav-collapse collapse">
                <div class="filters">
                    <div class="row-fluid">
                        <div class="span4 half">
                            @if (config('rfm.upload_files'))
                            <button class="tip btn upload-btn" title="{{ __('Upload_file') }}"><i class="rficon-upload"></i></button>
                            @endif
                            @if (config('rfm.create_text_files'))
                            <button class="tip btn create-file-btn" title="{{ __('New_File') }}"><i class="icon-plus"></i><i class="icon-file"></i></button>
                            @endif
                            @if (config('rfm.create_folders'))
                            <button class="tip btn new-folder" title="{{ __('New_Folder') }}><i class="icon-plus"></i><i class="icon-folder-open"></i></button>
                            @endif
                            @if (config('rfm.copy_cut_files') || config('rfm.copy_cut_dirs'))
                            <button class="tip btn paste-here-btn" title="{{ __('Paste_Here') }}"><i class="rficon-clipboard-apply"></i></button>
                            <button class="tip btn clear-clipboard-btn" title="{{ __('Clear_Clipboard') }}"><i class="rficon-clipboard-clear"></i></button>
                            @endif
                            <div id="multiple-selection" style="display:none;">
                                @if (config('rfm.multiple_selection'))
                                    @if (config('rfm.delete_files'))
                                    <button class="tip btn multiple-delete-btn" title="{{ __('Erase') }}" data-confirm="{{ __('Confirm_del') }}"><i class="icon-trash"></i></button>
                                    @endif
                                    <button class="tip btn multiple-select-btn" title="{{ __('Select_All') }}"><i class="icon-check"></i></button>
                                    <button class="tip btn multiple-deselect-btn" title="{{ __('Deselect_All') }}"><i class="icon-ban-circle"></i></button>
                                    @if ($apply_type != "apply_none" && config('rfm.multiple_selection_action_button'))
                                    <button class="btn multiple-action-btn btn-inverse" data-function="{{ $apply_type }}">{{ __('Select') }}</button>
                                    @endif
                                @endif
                            </div>
                        </div>
                        <div class="span2 half view-controller">
                            <button class="btn tip @if ($view==0) btn-inverse @endif" id="view0" data-value="0" title="{{ __('View_boxes') }}"><i class="icon-th @if ($view==0) icon-white @endif"></i></button>
                            <button class="btn tip @if ($view==1) btn-inverse @endif" id="view1" data-value="1" title="{{ __('View_list') }}"><i class="icon-align-justify @if ($view==1) icon-white @endif"></i></button>
                            <button class="btn tip @if ($view==2) btn-inverse @endif" id="view2" data-value="2" title="{{ __('View_columns_list') }}"><i class="icon-fire @if ($view==2) icon-white @endif"></i></button>
                        </div>
                        <div class="span6 entire types">
                            <span>{{ __('Filters') }}:</span>
                            @if ($type_param != 1 && $type_param != 3 && config('rfm.show_filter_buttons'))
                                @switch(true)
                                    @case(count(config('ext_file')) > 0)
                                        <input id="select-type-1" name="radio-sort" type="radio" data-item="ff-item-type-1" checked="checked" class="hide" />
                                        <label id="ff-item-type-1" title="<?php echo __('Files');?>" for="select-type-1" class="tip btn ff-label-type-1"><i class="icon-file"></i></label>
                                        @break
                                    @case(count(config('ext_img')) > 0)
                                        <input id="select-type-2" name="radio-sort" type="radio" data-item="ff-item-type-2" class="hide" />
                                        <label id="ff-item-type-2" title="<?php echo __('Images');?>" for="select-type-2" class="tip btn ff-label-type-2"><i class="icon-picture"></i></label>
                                        @break
                                    @case(count(config('ext_misc')) > 0)
                                        <input id="select-type-3" name="radio-sort" type="radio" data-item="ff-item-type-3" class="hide" />
                                        <label id="ff-item-type-3" title="<?php echo __('Archives');?>" for="select-type-3" class="tip btn ff-label-type-3"><i class="icon-inbox"></i></label>
                                        @break
                                    @case(count(config('ext_video')) > 0)
                                        <input id="select-type-4" name="radio-sort" type="radio" data-item="ff-item-type-4" class="hide" />
                                        <label id="ff-item-type-4" title="<?php echo __('Videos');?>" for="select-type-4" class="tip btn ff-label-type-4"><i class="icon-film"></i></label>
                                        @break
                                    @case(count(config('ext_music')) > 0)
                                        <input id="select-type-5" name="radio-sort" type="radio" data-item="ff-item-type-5" class="hide" />
                                        <label id="ff-item-type-5" title="<?php echo __('Music');?>" for="select-type-5" class="tip btn ff-label-type-5"><i class="icon-music"></i></label>
                                        @break
                                    @default
                                        
                                @endswitch
                            @endif
                            <input accesskey="f" type="text" class="filter-input @if ($type_param != 1 && $type_param != 3) filter-input-notype @endif"
                                id="filter-input" name="filter"
                                placeholder="{{ RFM::fixStrtolower(__('Text_filter')) }}..."
                                value="{{ $filter }}" />
                            @if ($n_files > config('file_number_limit_js'))
                                <label id="filter" class="btn"><i class="icon-play"></i></label>
                            @endif
                            <input id="select-type-all" name="radio-sort" type="radio" data-item="ff-item-type-all" class="hide" />
                            <label id="ff-item-type-all" title="{{ __('All') }}" @if ($type_param == 1 || $type_param == 3) style="visibility: hidden;" @endif
                                data-item="ff-item-type-all" for="select-type-all"
                                style="margin-rigth:0px;"
                                class="tip btn btn-inverse ff-label-type-all">{{ __('All') }}</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>