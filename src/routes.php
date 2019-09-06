<?php

$FM_ROUTE_PREFIX = "/filemanager/";
$FM_ROUTES =    ['index.php' => ['get'],
                'ajax_calls.php' => ['get', 'post'],
                'dialog.php' => ['get'],
                'execute.php' => ['post'],
                'force_download.php' => ['post'],
                'upload.php' => ['get', 'post']];

// Routes For Responsive API and Web (dialog.php)
foreach ($FM_ROUTES as $file => $method) {
    Route::match($method, $FM_ROUTE_PREFIX.$file, function() use($file) {
        include(__DIR__ . '/../resources/filemanager/'.$file);
        return ;
    })->name('FM'.$file);
}
