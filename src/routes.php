<?php

$FM_ROUTE_PREFIX = "/filemanager/";
$FM_ROUTES =    ['index.php',
                'ajax_calls.php',
                'dialog.php',
                'execute.php',
                'force_download.php',
                'upload.php'];

foreach ($FM_ROUTES as $route) {
    Route::get($FM_ROUTE_PREFIX."ajax_calls.php", function() {
        return File::get(__DIR__ . '/../ressources/filemanager/'.$route);
    });
}