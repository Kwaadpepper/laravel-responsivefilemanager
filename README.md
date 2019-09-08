[![Maintenance](https://img.shields.io/badge/Maintained%3F-yes-green.svg)](https://GitHub.com/Naereen/StrapDown.js/graphs/commit-activity)
[![MIT license](https://img.shields.io/badge/License-MIT-blue.svg)](https://lbesson.mit-license.org/)
[![GitHub version](https://badge.fury.io/gh/kwaadpepper%2Flaravel-responsivefilemanager.svg)](https://badge.fury.io/gh/kwaadpepper%2Flaravel-responsivefilemanager)

# Laravel-ResponsiveFileManager

### Corrections are made in order to :
  - Prevent collisions
  - Fix small errors
  - Adapt to Laravel

**If you have some corrections, recommendations or anything else to say please let me know. Don't hesitate to make PR if you done something cool you wan't to share**

#

## Include in TinyMCE or CKEDITOR

#### Responsive File Manager Documentation
https://responsivefilemanager.com/index.php#documentation-section

#

***!IMPORTANT: if you are using htaccess protection, make sure your $GLOBALS PHP_AUTH_USER/PHP_AUTH_USER are defined in your webserver config***

### **How to Install ?**

#### *Install on your project*

    composer require kwaadpepper/laravel-responsivefilemanager

    php artisan vendor:publish --provider="Kwaadpepper\ResponsiveFileManager\FileManagerServiceProvider"

Now there is a new configuration file ```rfm.php```

Install in ```config/app.php```

    'providers' => [
            /*
             * Laravel Framework Service Providers...
             */
            ...
            // Responsive File Manager
            Kwaadpepper\ResponsiveFileManager\FileManagerServiceProvider::class
    ],

In ```HTTP/Kernel.php``` need to use StartSession, can also use and is recommended CSRF Token

    protected $middlewareGroups = [
        ...
        'web' => [
            ...
            \Illuminate\Session\Middleware\StartSession::class,
            // Responsive File Manager supports CSRF Token usage
            \App\Http\Middleware\VerifyCsrfToken::class
        ]
        ...
    ];

Generate private key for url identification

    php artisan rfm:generate

All configs included to work out of the box.
Files are meant to be stored in public folder.

#### *Include JS*

*Replace #MYTEXTAREAJS with your textarea input*

    <script src='{{ asset('/vendor/unisharp/laravel-ckeditor/ckeditor.js') }}'></script>
    <script>
        $(document).ready(function() {
            if($("#MYTEXTAREAID").length) {
                CKEDITOR.replace( 'postBody', {
                    filebrowserBrowseUrl : '@filemanager_get_resource(dialog.php)?akey=@filemanager_get_key()&type=2&editor=ckeditor&fldr=',
                    filebrowserUploadUrl : '@filemanager_get_resource(dialog.php)?akey=@filemanager_get_key()&type=2&editor=ckeditor&fldr=',
                    filebrowserImageBrowseUrl : '@filemanager_get_resource(dialog.php)?akey=@filemanager_get_key()&type=1&editor=ckeditor&fldr=',
                    language : '<?php App::getLocale() ?>'
                });
            }
        })
    </script>

#### To make private folder use .htaccess with ```Deny from all```

**TODO :**

 - [x] private key setup
 - [x] more corrections on JS side
 - [x] more corrections on languages
 - [ ] Test with tinyMCE
 - [ ] Include commercial support
 - [ ] Test and debug FTP fonctionnality
 - [ ] MultiUser and Auth Support
 - [ ] publish package
 