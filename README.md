# Laravel-ResponsiveFileManager

[![GitHub issues](https://img.shields.io/github/issues/Kwaadpepper/laravel-responsivefilemanager)](https://github.com/Kwaadpepper/laravel-responsivefilemanager/issues)
[![GitHub license](https://img.shields.io/badge/License-MIT-informational.svg)](https://github.com/Kwaadpepper/laravel-responsivefilemanager/blob/master/LICENSE)
[![GitHub license](https://img.shields.io/badge/Licence-CC%20BY%20NC%203.0-informational.svg)](https://creativecommons.org/licenses/by-nc/3.0/)
[![Maintenance](https://img.shields.io/badge/Maintained%3F-yes-informational.svg)](https://GitHub.com/Naereen/StrapDown.js/graphs/commit-activity)
[![GitHub tag](https://img.shields.io/github/tag/Kwaadpepper/laravel-responsivefilemanager.svg?style=flat&logo=laravel&color=informational)](https://github.com/Kwaadpepper/laravel-responsivefilemanager/tags)

This software includes a modified version of [Responsive File Manager](https://github.com/trippo/ResponsiveFilemanager) *see [official website](https://responsivefilemanager.com/)*

## *Responsive File Manager (RFM) is licenced under CC BY-NC 3.0 which means software can't be redistributed for a commercial use.*

![CC BY-NC 3.0](http://i.creativecommons.org/l/by-nc/3.0/88x31.png)

**If you wan't to use it for comercial purpose take a look on [the author (Alberto Peripolli
) website](https://responsivefilemanager.com/#download-section)**

*This repo is under MIT Licence except parts where antoher licence is mentioned in file.*

### Corrections are made to RFM in order to

- Prevent collisions
- Fix small errors
- Adapt to Laravel

The Laravel plugin code part here is under **MIT Licence**.

*The RFM author delivers a commercial version of his code (a modified ```include.js```). You will need to modify this file if you use CSRF check on your laravel app by adding ```_token: jQuery('meta[name="csrf-token"]').attr('content')``` on ajax calls. You can use [www.diffchecker.com](https://www.diffchecker.com) to check modifications you will have to apply to your ```include.commercial.js``` file. I can't deliver myself a licence to use RFM for commercial purpose*

__**If you have some corrections, recommendations or anything else to say please let me know. Don't hesitate to make PR if you done something cool you wan't to share**__

**__[Read Responsive File Manager Documentation](https://responsivefilemanager.com/index.php#documentation-section)__**

___

## **How to Install ?**

## **#1**

### *Install in your project*

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

___

## **#2**

### Use as StandAlone

*Use helpers to write filemanager url*

    <a href="@filemanager_get_resource(dialog.php)?field_id=imgField&lang=en_EN&akey=@filemanager_get_key()" value="Files">Open RFM</a>

see ```USE AS STAND-ALONE FILE MANAGER``` in Responsible [File Manager Doc](https://responsivefilemanager.com/index.php#documentation-section)

*tip: Seems to support Bootstrap Modal*

___

### Include in TinyMCE or CKEDITOR

#### *Include JS*

- **For CKEditor**

__**Replace #MYTEXTAREAJS with your textarea input**__

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

- **For TinyMCE**

with tinymce parameters

    $(document).ready(() => {
        $('textarea').first().tinymce({
            script_url : '/tinymce/tinymce.min.js',
            width: 680,height: 300,
            plugins: [
                "advlist autolink link image lists charmap print preview hr anchor pagebreak",
                "searchreplace wordcount visualblocks visualchars insertdatetime media nonbreaking",
                "table contextmenu directionality emoticons paste textcolor filemanager code"
        ],
        toolbar1: "undo redo | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | styleselect",
        toolbar2: "| responsivefilemanager | link unlink anchor | image media | forecolor backcolor  | print preview code ",
        image_advtab: true ,
        filemanager_access_key: '@filemanager_get_key()',
        filemanager_sort_by: '',
        filemanager_descending: '',
        filemanager_subfolder: '',
        filemanager_crossdomain: '',
        external_filemanager_path: '@filemanager_get_resource(dialog.php)',
        filemanager_title:"Responsive Filemanager" ,
        external_plugins: { "filemanager" : "/vendor/responsivefilemanager/plugin.min.js"}
        });
    });

#### To make private folder use .htaccess with ```Deny from all```

___

### Configuration

#### Language

*This package has multiple languages support, it is still incomplete. please contribute if you have the language concerned skills.*

1. Url get parameter ($_GET)
2. ```RF.language``` session var (*User selected languages in drop list*)
3. config.php (*rfm.php*) ```default_language```
4. User prefered language (*HTTP headers*)
5. Your Laravel app default language

<details>
<summary><b>Available Languages</b></summary>

    az_AZ      Azərbaycan dili
    bg_BG      български език
    ca         Català, valencià
    cs         čeština, český jazyk
    da         Dansk
    de         Deutsch
    el_GR      ελληνικά
    en_EN      English
    es         Español
    fa         فارسی
    fr_FR      Français
    he_IL      Hebrew (Israel)
    hr         Hrvatski jezik
    hu_HU      Magyar
    id         Bahasa Indonesia
    it         Italiano
    ja         日本
    lt         Lietuvių kalba
    mn_MN      монгол
    nb_NO      Norsk bokmål
    nn_NO      Norsk nynorsk
    nl         Nederlands, Vlaams
    pl         Język polski, polszczyzna
    pt_BR      Português(Brazil,
    pt_PT      Português
    ro         Română
    ru         Pусский язык
    sk         Slovenčina
    sl         Slovenski jezik
    sv_SE      Svenska
    th_TH      ไทย
    tr_TR      Türkçe
    uk_UA      Yкраїнська мова
    vi         Tiếng Việt
    zh_CN      中文 (Zhōngwén), 汉语, 漢語
</details>

#### FTP

To come
___

**TODO :**

- [x] private key setup
- [x] more corrections on JS side
- [x] more corrections on languages
- [x] Test with tinyMCE
- [x] Include commercial support
- [x] Test and debug FTP fonctionnality (Alpha Still need debug some functionallities)
- [x] TODO: cache FTP thumbnails for preview (images only)
- [ ] MultiUser and Auth Support
- [ ] compile assets
- [ ] publish package
- [ ] Rewrite routes to be cleaner (eg :  ajax_calls/{action}/{subaction})
- [ ] separe properly View from logic ( ex: dialog.php  OMG <(o_O)>)
