<?php
/**
 * RFM Routes registering
 * @author   Jeremy Munsch <kwaadpepper@users.noreply.github.com>
 * @license  MIT https://choosealicense.com/licenses/mit/
 * @version  GIT:
 * @link     https://github.com/Kwaadpepper/laravel-responsivefilemanager/blob/master/src/routes.php
 */

use Illuminate\Support\Facades\Route;

$FM_ROUTES =    ['ajax_calls.php' => ['get', 'post'],
                '/' => ['get'],
                'execute.php' => ['post'],
                'force_download.php' => ['post'],
                'fview.php' => ['get'],
                'upload.php' => ['get', 'post']];

require_once __DIR__.'/boot.php';

// Routes For Responsive API and Web (dialog.php)

$middlewares = ['web', 'rfmxss', 'rfmsession'];

if (defined('FM_USE_ACCESS_KEYS') && FM_USE_ACCESS_KEYS == true) {
    array_push($middlewares, 'rfmkey');
}

Route::group(['middleware' => $middlewares], function () {

    Route::match(
        ['get'],
        config('rfm.laravel_route_prefix').'/',
        'Kwaadpepper\ResponsiveFileManager\Controller\DialogController@index'
    )->name('RFMInterface');

    Route::match(
        ['get'],
        config('rfm.laravel_route_prefix').'/new/',
        'Kwaadpepper\ResponsiveFileManager\Controller\DialogController@indexNew'
    )->name('RFMInterfaceNew');

    Route::match(
        ['get', 'post'],
        config('rfm.laravel_route_prefix').'/upload',
        'Kwaadpepper\ResponsiveFileManager\Controller\UploadController@index'
    )->name('RFMUpload');

    Route::match(
        ['get'],
        config('rfm.laravel_route_prefix').'/view',
        'Kwaadpepper\ResponsiveFileManager\Controller\DownloadController@fview'
    )->name('RFMView');
});
