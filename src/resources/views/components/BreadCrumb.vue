<template>
    <div class="row-fluid">
        <ul class="breadcrumb">
            <li class="pull-left">
                <a href="route('RFMInterface', $get_params) /">
                    <i class="icon-home"></i>
                </a>
            </li>
            <li>
                <span class="divider">/</span>
            </li>

            <!-- Print breadcrumb -->
            <template v-for="(dir, index) in $root.RFM.vars.subdir.split('/')">
                <template v-if="$root.RFM.vars.subdir.split('/').length -1 == index">
                    <li :key="index" class="active">{{ dir }}</li>
                </template>
                <template v-else>
                    <li :key="index">
                        <a href="route('RFMInterface', $get_params)">{{ $root.RFM.vars.dir }}</a>
                    </li>
                    <li :key="index+'2'">
                        <span class="divider">/</span>
                    </li>
                </template>
            </template>
            <!-- End print Breadcrumb -->

            <li class="pull-right">
                <a class="btn-small" href="javascript:void('')" id="info">
                    <i class="icon-question-sign"></i>
                </a>
            </li>

            <template v-if="$root.RFM.config.show_language_selection">
                <li class="pull-right">
                    <a class="btn-small" href="javascript:void('')" id="change_lang_btn">
                        <i class="icon-globe"></i>
                    </a>
                </li>
            </template>

            <li class="pull-right">
                <a
                    id="refresh"
                    class="btn-small"
                    href="route('RFMInterface', array_merge($get_params, ['fldr' => $subdir, uniqid() => '']))"
                >
                    <i class="icon-refresh"></i>
                </a>
            </li>
            <li class="pull-right">
                <div class="btn-group">
                    <a class="btn dropdown-toggle sorting-btn" data-toggle="dropdown" href="#">
                        <i class="icon-signal"></i>
                        <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu pull-left sorting">
                        <li class="text-center">
                            <strong>{{ $t('Sorting') }}</strong>
                        </li>
                        <li>
                            <a
                                class="sorter sort-name @if($sort_by=='name') @if($descending) descending @else ascending @endif @endif"
                                href="javascript:void('')"
                                data-sort="name"
                            >{{ $t('Filename') }}</a>
                        </li>
                        <li>
                            <a
                                class="sorter sort-date @if($sort_by=='date') @if($descending) descending @else ascending @endif @endif"
                                href="javascript:void('')"
                                data-sort="date"
                            >{{ $t('Date') }}</a>
                        </li>
                        <li>
                            <a
                                class="sorter sort-size @if($sort_by=='size') @if($descending) descending @else ascending @endif @endif"
                                href="javascript:void('')"
                                data-sort="size"
                            >{{ $t('Size') }}</a>
                        </li>
                        <li>
                            <a
                                class="sorter sort-extension @if($sort_by=='extension') @if($descending) descending @else ascending @endif @endif"
                                href="javascript:void('')"
                                data-sort="extension"
                            >{{ $t('Type') }}</a>
                        </li>
                    </ul>
                </div>
            </li>
            <li>
                <small class="hidden-phone">
                    <span id="files_number">{{ $root.RFM.config.current_files_number }}</span>
                    {{ $t('Files') }}
                    <span
                        id="folders_number"
                    >{{ $root.RFM.config.current_folders_number }}</span>
                    {{ $t('Folders') }}
                </small>
            </li>
            <li v-if="$root.RFM.config.show_total_size">
                <small class="hidden-phone">
                    <span :title="$t('total size')+'&nbsp;'+$root.RFM.config.MaxSizeTotal">
                        {{ $t('total size') }}: {{ $root.RFM.config.sizeCurrentFolder }}
                        <template
                            v-if="$root.RFM.config.MaxSizeTotal !== false && typeof $root.RFM.config.MaxSizeTotal === 'Number'"
                        >/{{ $root.RFM.config.MaxSizeTotal }}&nbsp;{{ $t('MB') }}</template>
                    </span>
                </small>
            </li>
        </ul>
    </div>
</template>

<script>
export default {
    name: "BreadCrumb",
    data() {
        return {};
    }
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
</style>
