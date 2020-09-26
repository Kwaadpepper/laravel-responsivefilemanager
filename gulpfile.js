var elixir = require('laravel-elixir');
elixir.config.sourcemaps = false;
elixir(function (mix) {
    mix.less(
        [
            '../../../src/assets/less/style.less',
        ],
        'resources/tmp/css/style.css',
        'src/assets/less'
    );

    mix.less(
        [
            "../../../node_modules/bootstrap-lightbox/less/bootstrap-lightbox.less"
        ],
        'resources/tmp/css/lib.css',
        'node_modules'
    );

    mix.styles(
        [
            '../../../node_modules/bootstrap/docs/assets/css/bootstrap.css',
            '../../../node_modules/bootstrap/docs/assets/css/bootstrap-responsive.css',
            '../../../node_modules/bootstrap-modal/css/bootstrap-modal.css',
            '../../../node_modules/jquery-contextmenu/dist/jquery.contextMenu.css',
            '../../../node_modules/tui-color-picker/dist/tui-color-picker.css',
            '../../../node_modules/tui-image-editor/dist/tui-image-editor.css',
            'lib.css',
            'style.css'
        ],
        'resources/filemanager/css/style.css',
        'resources/tmp/css'
    );

    mix.styles(
      ["../../../src/assets/less/rtl-style.less"],
      'resources/filemanager/css/rtl-style.css',
      'resources/assets/less'
    );

    mix.scripts(
        [
            "bootstrap/js/bootstrap-transition.js",
            "bootstrap/js/bootstrap-affix.js",
            "bootstrap/js/bootstrap-dropdown.js",
            "bootstrap/js/bootstrap-alert.js",
            "bootstrap/js/bootstrap-button.js",
            "bootstrap/js/bootstrap-collapse.js",
            "bootstrap/js/bootstrap-dropdown.js",
            "bootstrap/js/bootstrap-modal.js",
            "bootstrap/js/bootstrap-tooltip.js",
            "bootstrap/js/bootstrap-popover.js",
            "bootstrap/js/bootstrap-scrollspy.js",
            "bootstrap/js/bootstrap-tab.js",
            "bootstrap/js/bootstrap-typeahead.js",
            "bootstrap-lightbox/js/bootstrap-lightbox.js",
            "jquery-contextmenu/dist/jquery.contextMenu.js",
            "vanilla-lazyload/dist/lazyload.js",
            "jquery-scrollstop/jquery.scrollstop.js",
            "bootbox.js/bootbox.js",
            "jquery-touchswipe/jquery.touchSwipe.js",
            "bootstrap-modal/js/bootstrap-modalmanager.js",
            "bootstrap-modal/js/bootstrap-modal.js",
            "clipboard/dist/clipboard.js",
            "jquery-ui-touch-punch/jquery.ui.touch-punch.js",
        ],
        'resources/filemanager/js/plugins.js',
        'node_modules'
    );

    mix.scripts(
        [
            "fabric/dist/fabric.js",
            "tui-code-snippet/dist/tui-code-snippet.js",
            "tui-color-picker/dist/tui-color-picker.js",
            "tui-image-editor/dist/tui-image-editor.js",
        ],
        'resources/filemanager/js/tui-image-editor.js',
        'node_modules'
    );

    mix.copy('node_modules/blueimp-file-upload/js', 'resources/filemanager/js/');
    mix.copy('node_modules/blueimp-file-upload/css', 'resources/filemanager/css/');

    mix.scripts(
        ['../../../src/assets/js/include.js'],
        'resources/filemanager/js/include.js'
    );

    mix.scripts(
        ['../../../src/assets/js/plugin.js'],
        'resources/filemanager/plugin.min.js'
    );

    mix.scripts(
        ['../../../src/assets/js/plugin_responsivefilemanager_plugin.js'],
        'resources/tinymce/plugins/responsivefilemanager/plugin.min.js'
    );

    mix.scripts(
        ['../../../src/assets/js/modernizr.custom.js'],
        'resources/filemanager/js/modernizr.custom.js'
    );

    mix.scripts(
        ['../../../src/assets/js/load_more.js'],
        'resources/filemanager/js/load_more.js'
    );
});