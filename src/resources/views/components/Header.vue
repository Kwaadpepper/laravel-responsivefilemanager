<template>
    <div class="navbar navbar-fixed-top">
        <div class="navbar-inner">
            <div class="container-fluid">
                <button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <div class="brand">{{ $t('Toolbar') }}</div>
                <div class="nav-collapse collapse">
                    <div class="filters">
                        <div class="row-fluid">
                            <div class="span4 half">
                                <button v-if="$root.RFM.config.upload_files" class="tip btn upload-btn" :title="$t('Upload_file')"><i class="rficon-upload"></i></button>
                                <button v-if="$root.RFM.config.create_text_files" class="tip btn create-file-btn" :title="$t('New_File')"><i class="icon-plus"></i><i class="icon-file"></i></button>
                                <button v-if="$root.RFM.config.create_text_files" class="tip btn new-folder" :title="$t('New_Folder')"><i class="icon-plus"></i><i class="icon-folder-open"></i></button>
                                <template v-if="$root.RFM.config.copy_cut_files || $root.RFM.config.copy_cut_dirs">
                                <button class="tip btn paste-here-btn" :title="$t('Paste_Here')"><i class="rficon-clipboard-apply"></i></button>
                                <button class="tip btn clear-clipboard-btn" :title="$t('Clear_Clipboard')"><i class="rficon-clipboard-clear"></i></button>
                                </template>
                            </div>
                            <div class="span2 half view-controller">
                                <button v-if="$root.RFM.vars.view == 0" class="btn tip @if ($view==0) btn-inverse @endif" id="view0" data-value="0" :title="$t('View_boxes')"><i class="icon-th @if ($view==0) icon-white @endif"></i></button>
                                <button class="btn tip @if ($view==1) btn-inverse @endif" id="view1" data-value="1" :title="$t('View_list')"><i class="icon-align-justify @if ($view==1) icon-white @endif"></i></button>
                                <button class="btn tip @if ($view==2) btn-inverse @endif" id="view2" data-value="2" :title="$t('View_columns_list')"><i class="icon-fire @if ($view==2) icon-white @endif"></i></button>
                            </div>
                            <div class="span6 entire types">
                                <span>{{ $t('Filters') }}:</span>
                                <template v-if="$root.RFM.vars.type_param != 1 && $root.RFM.vars.type_param != 3 && $root.RFM.config.show_filter_buttons">
                                <template v-if="$root.RFM.config.ext_file.length > 0"><input id="select-type-1" name="radio-sort" type="radio" data-item="ff-item-type-1" checked="checked" class="hide" />
                                        <label id="ff-item-type-1" :title="$t('Files')" for="select-type-1" class="tip btn ff-label-type-1"><i class="icon-file"></i></label></template>
                                <template v-ifelse="$root.RFM.config.ext_img.length > 0"><input id="select-type-2" name="radio-sort" type="radio" data-item="ff-item-type-2" class="hide" />
                                        <label id="ff-item-type-2" :title="$t('Images')" for="select-type-2" class="tip btn ff-label-type-2"><i class="icon-picture"></i></label>
                                        </template>
                                <template v-ifelse="$root.RFM.config.ext_misc.length > 0"><input id="select-type-3" name="radio-sort" type="radio" data-item="ff-item-type-3" class="hide" />
                                        <label id="ff-item-type-3" :title="$t('Archives')" for="select-type-3" class="tip btn ff-label-type-3"><i class="icon-inbox"></i></label>
                                        </template>
                                <template v-ifelse="$root.RFM.config.ext_video.length > 0"><input id="select-type-4" name="radio-sort" type="radio" data-item="ff-item-type-4" class="hide" />
                                        <label id="ff-item-type-4" :title="$t('Videos')" for="select-type-4" class="tip btn ff-label-type-4"><i class="icon-film"></i></label>
                                        </template>
                                <template v-ifelse="$root.RFM.config.ext_music.length > 0"><input id="select-type-5" name="radio-sort" type="radio" data-item="ff-item-type-5" class="hide" />
                                        <label id="ff-item-type-5" :title="$t('Music')" for="select-type-5" class="tip btn ff-label-type-5"><i class="icon-music"></i></label>
                                        </template>
                                </template>
                                <input accesskey="f" type="text" :class="'filter-input'+(($root.RFM.vars.type_param != 1 && $root.RFM.vars.type_param != 3) ? 'filter-input-notype' : '')"
                                    id="filter-input" name="filter"
                                    :placeholder="$t('Text_filter')+'...'"
                                    :value="$root.RFM.vars.filter" />
                                    <template v-if="$root.RFM.vars.n_files > $root.RFM.config.file_number_limit_js">
                                    <label id="filter" class="btn"><i class="icon-play"></i></label>
                                    </template>
                                <input id="select-type-all" name="radio-sort" type="radio" data-item="ff-item-type-all" class="hide" />
                                <label id="ff-item-type-all" :title="$t('All')" :style="($root.RFM.vars.type_param == 1 || $root.RFM.vars.type_param == 3) ? 'visibility: hidden;' : ''"
                                    data-item="ff-item-type-all" for="select-type-all"
                                    style="margin-rigth:0px;"
                                    class="tip btn btn-inverse ff-label-type-all">{{ $t('All') }}</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
  name: 'Header',
  data () {
    return {
    }
  }
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
</style>
