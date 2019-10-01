<!DOCTYPE html>
<html lang="{{ strstr(app()->getLocale(), '_', true) ?? app()->getLocale() }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <meta name="robots" content="noindex,nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Responsive FileManager New</title>
    <link rel="shortcut icon" href="{{ asset('vendor/responsivefilemanager/img/ico/favicon.ico') }}">
    <!-- CSS to style the file input field as button and adjust the Bootstrap progress bars -->
    <link rel="stylesheet" href="{{ asset('vendor/responsivefilemanager/css/jquery.fileupload.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/responsivefilemanager/css/jquery.fileupload-ui.css') }}">
    <!-- CSS adjustments for browsers with JavaScript disabled -->
    <noscript>
        <link rel="stylesheet" href="{{ asset('vendor/responsivefilemanager/css/jquery.fileupload-noscript.css') }}">
    </noscript>
    <noscript>
        <link rel="stylesheet" href="{{ asset('vendor/responsivefilemanager/css/jquery.fileupload-ui-noscript.css') }}">
    </noscript>
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/jplayer/2.7.1/skin/blue.monday/jplayer.blue.monday.min.css" />
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

    <script src="https://code.jquery.com/jquery-1.12.4.min.js"
        integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ=" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"
        integrity="sha256-VazP97ZCwtekAsvgPBSUwPFKdrwD3unUfSGVYrahUqU=" crossorigin="anonymous"></script>
    <script src="{{ asset('vendor/responsivefilemanager/js/plugins.js').'?v='.config('rfm.version') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jplayer/2.9.2/jplayer/jquery.jplayer.min.js"></script>
    <link type="text/css" href="https://uicdn.toast.com/tui-color-picker/v2.2.0/tui-color-picker.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/1.6.7/fabric.js"></script>
    <script src="https://uicdn.toast.com/tui.code-snippet/v1.5.0/tui-code-snippet.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/1.3.3/FileSaver.min.js"></script>
    <script src="https://uicdn.toast.com/tui-color-picker/v2.2.0/tui-color-picker.js"></script>
    <script src="https://uicdn.toast.com/tui-image-editor/latest/tui-image-editor.js"></script>
    <script src="{{ asset('vendor/responsivefilemanager/js/modernizr.custom.js').'?v='.config('rfm.version') }}">
    </script>
    <script src="{{ asset('vendor/responsivefilemanager/js/jquery.fileupload.js').'?v='.config('rfm.version') }}">
    </script>
    <link href={{ asset('vendor/responsivefilemanager/js/chunk-vendors.js').'?v='.config('rfm.version') }} rel=preload
        as=script>
    <link href={{ asset('vendor/responsivefilemanager/js/index.js').'?v='.config('rfm.version') }} rel=preload
        as=script>

    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
        <script src="//cdnjs.cloudflare.com/ajax/libs/html5shiv/3.6.2/html5shiv.js"></script>
        <![endif]-->

    @prepend('scripts')

    {{-- RFM JS Main file --}}
    {{-- <script src="{{ asset('vendor/responsivefilemanager/js/include.js').'?v='.config('rfm.version') }}"></script>
    --}}
    <script>
        window.RFM = {};
        window.RFM.translations = {!! json_encode($translations) !!}
    </script>
    <script src="{{ asset('vendor/responsivefilemanager/js/chunk-vendors.js').'?v='.config('rfm.version') }}">
    </script>
    <script src="{{ asset('vendor/responsivefilemanager/js/index.js').'?v='.config('rfm.version') }}">
    </script>

    @endprepend
</head>

<body>
    <div id="app" class="container-fluid">

    </div>
    @stack('scripts')
</body>

</html>
