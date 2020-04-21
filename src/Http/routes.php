<?php
/**
 * RFM Routes registering
 * @author   Jeremy Munsch <kwaadpepper@users.noreply.github.com>
 * @license  MIT https://choosealicense.com/licenses/mit/
 * @version  GIT:
 * @link     https://github.com/Kwaadpepper/laravel-responsivefilemanager/blob/master/src/routes.php
 */
$FM_ROUTE_PREFIX = "/filemanager/";
$FM_ROUTES =    ['ajax_calls' => ['get', 'post'],
                'dialog' => ['get'],
                'execute' => ['post'],
                'force_download' => ['post'],
                'fview' => ['get'],
                'upload' => ['get', 'post']];

require_once __DIR__.'/boot.php';

// Routes For Responsive API and Web (dialog.php)
Route::group(
    ['middleware' => 'admin.user'],
    function () use ($FM_ROUTE_PREFIX, $FM_ROUTES) {
        foreach ($FM_ROUTES as $file => $method) {
            Route::match(
                $method,
                $FM_ROUTE_PREFIX.$file,
                function () use ($file) {
                    include __DIR__ . '/../Http/'.$file.'.php';
                    return ;
                }
            )->name('FM'.$file);
        }
    }
);
