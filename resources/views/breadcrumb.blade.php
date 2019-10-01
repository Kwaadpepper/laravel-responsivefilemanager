<div class="row-fluid">
    <ul class="breadcrumb">
        <li class="pull-left"><a href="{{ route('RFMInterface', $get_params) }}/"><i class="icon-home"></i></a></li>
        <li><span class="divider">/</span></li>

        {{-- Print breadcrumb --}}
        @foreach ((($s = explode("/", $subdir)) ?? []) as $k => $dir)
            @if($k == (count($s)-2))
                <li class="active">{{ $dir }}</li>
            @elseif ($dir != "")
                <li><a href="{{ route('RFMInterface', $get_params) }}"><?php echo $dir?></a></li>
                <li><span class="divider">/</span></li>
            @endif
        @endforeach
        {{-- End print Breadcrumb --}}

        <li class="pull-right"><a class="btn-small" href="javascript:void('')" id="info"><i class="icon-question-sign"></i></a></li>

        @if (config('rfm.show_language_selection'))
            <li class="pull-right"><a class="btn-small" href="javascript:void('')" id="change_lang_btn"><i class="icon-globe"></i></a></li>
        @endif

        <li class="pull-right">
            <a id="refresh" class="btn-small" href="{{ route('RFMInterface', array_merge($get_params, ['fldr' => $subdir, uniqid() => ''])) }}">
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
                    <li class="text-center"><strong>{{ __('Sorting') }}</strong></li>
                    <li><a class="sorter sort-name @if($sort_by=="name") @if($descending) descending @else ascending @endif @endif" href="javascript:void('')" data-sort="name">{{ __('Filename') }}</a></li>
                    <li><a class="sorter sort-date @if($sort_by=="date") @if($descending) descending @else ascending @endif @endif" href="javascript:void('')" data-sort="date">{{ __('Date') }}</a></li>
                    <li><a class="sorter sort-size @if($sort_by=="size") @if($descending) descending @else ascending @endif @endif" href="javascript:void('')" data-sort="size">{{ __('Size') }}</a></li>
                    <li><a class="sorter sort-extension @if($sort_by=="extension") @if($descending) descending @else ascending @endif @endif" href="javascript:void('')" data-sort="extension">{{ __('Type') }}</a></li>
                </ul>
            </div>
        </li>
        <li><small class="hidden-phone">(<span id="files_number">{{ $current_files_number }}</span> {{ __('Files') }}<span id='folders_number'>{{ $current_folders_number }}</span>{{ __('Folders') }}</small></li>
        @if (config('rfm.show_total_size'))
            <li><small class="hidden-phone"><span title="{{ __('total size') }}{{ config('rfm.MaxSizeTotal') }}">{{ __('total size') }}: {{ RFM::makeSize($sizeCurrentFolder) }}@php echo ((config('MaxSizeTotal') !== false && is_int(config('MaxSizeTotal')))? '/'.config('MaxSizeTotal').' '.__('MB'):'');@endphp</span></small></li>
        @endif
    </ul>
</div>