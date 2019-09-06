# Laravel-ResponsiveFileManager

Include in TinyMCE or CKEDITOR
https://responsivefilemanager.com/index.php#documentation-section

File manager modified for Laravel Integration


php artisan vendor:publish --provider="Kwaadpepper\ResponsiveFileManager\FileManagerServiceProvider"

Change upload Dir for specific user !
storage_path('app/public/UserName')

All configs included

Include script in head ```{{ filemanager_javascript }}```
Get Filemanager config Value ```{{ @filemanager_get_config('config_value') }}```


To make private folder use
.htaccess with "Deny from all"