<!-- sorter -->
<div class="sorter-container <?php echo "list-view".$view;?>">
    <div class="file-name"><a class="sorter sort-name @if($sort_by == 'name') @if($descending) descending @else ascending @endif @endif" href="javascript:void('')" data-sort="name">{{  __('Filename') }}</a></div>
    <div class="file-date"><a class="sorter sort-date @if($sort_by == 'date') @if($descending) descending @else ascending @endif @endif" href="javascript:void('')" data-sort="date">{{  __('Date') }}</a></div>
    <div class="file-date"><a class="sorter sort-size @if($sort_by == 'size') @if($descending) descending @else ascending @endif @endif" href="javascript:void('')" data-sort="size">{{  __('Size') }}</a></div>
    <div class='img-dimension'>{{ __('Dimension') }}</div>
    <div class='file-extension'><a class="sorter sort-extension @if($sort_by == 'extension') @if($descending) descending @else ascending @endif @endif" href="javascript:void('')" data-sort="extension">{{  __('Type') }}</a></div>
    <div class='file-operations'>{{ __('Operations') }}</div>
</div>