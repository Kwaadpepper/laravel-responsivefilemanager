<?php
/**
 * @author Jeremy Munsch Kwaadpepper@users.noreply.github.com
 * Apply "MIT Licence" here
 */

require_once __DIR__.'/boot.php';
require_once __DIR__.'/include/mime_type_lib.php';

use ResponsiveFileManager\RFM;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use \Illuminate\Contracts\Encryption\DecryptException;

$config = config('rfm');

/**
 * Public FTP file viewer
 * 
 * Here security is priority
 * FTP server request must be secured here to prevent
 * anyone exploring freely the FTP server
 * To prevent exposing FTP path on request,
 * like Oooh look its a HTTP way to explore FTP server,
 * path should be encrypted using Laravel API
 * 
 * Encryption will work as Always work Token !
 * preventing someone altering the request
 * or generating one.
 * 
 * ox parameter will be a PHP Object
 * like following :
 * 
 *  ['path' => 'testar/', 'name' => 'Git to Follow.png' ]
 */

if (!request()->get('ox')) {
    if(!FM_DEBUG_ERROR_MESSAGE)
        throw new NotFoundHttpException();
    RFM::response(RFM::fm_trans('no ox query') . RFM::AddErrorLocation(), 400)->send();
    exit;
}

try {
    $get = decrypt(request()->get('ox'));
} catch (DecryptException $e) {
    if(!FM_DEBUG_ERROR_MESSAGE)
        throw new NotFoundHttpException();
    RFM::response(RFM::fm_trans('ox query decrypt failed') . RFM::AddErrorLocation(), 400)->send();
    exit;
}

if(strpos($get['path'], '/') === 0) {
    $get['path'] = substr($get['path'],1);
}

if (!RFM::checkRelativePath($get['path'])) {
    if(!FM_DEBUG_ERROR_MESSAGE)
        throw new NotFoundHttpException();
    RFM::response(RFM::fm_trans('path is wrong') . RFM::AddErrorLocation(), 400)->send();
    exit;
}

if (strpos($get['name'], '/') !== false) {
    if(!FM_DEBUG_ERROR_MESSAGE)
        throw new NotFoundHttpException();
    RFM::response(RFM::fm_trans('name includes a forbidden \'/\' char') . RFM::AddErrorLocation(), 400)->send();
    exit;
}

if (!($ftp = RFM::ftp_con($config))) {
    if(!FM_DEBUG_ERROR_MESSAGE)
        throw new NotFoundHttpException();
    RFM::response(RFM::fm_trans('FTP is not configured') . RFM::AddErrorLocation(), 400)->send();
    exit;
}

$name = $get['name'];
$info = pathinfo($get['path']);

if (!RFM::check_extension($info['extension'], $config)) {
    if(!FM_DEBUG_ERROR_MESSAGE)
        throw new NotFoundHttpException();
        RFM::response(RFM::fm_trans('wrong extension') . RFM::AddErrorLocation(), 400)->send();
    exit;
}

$file_name = $info['basename'];
$file_ext = $info['extension'];
$file_path = $config['ftp_base_folder'] . '/' . $get['path'];

$local_file_path_to_download = "";
// make sure the file exists
if (!RFM::ftp_download_file($ftp, $file_path, $file_name.'.'.$file_ext, $local_file_path_to_download)) {
    if(!FM_DEBUG_ERROR_MESSAGE)
        throw new NotFoundHttpException();
    RFM::response(RFM::fm_trans('failed to fetch ftp file '.$file_name.'.'.$file_ext.' in '.$file_path) . RFM::AddErrorLocation(), 400)->send();
    exit;
}

header('Content-Description: File Display');
header('Content-Type: '.mime_content_type($local_file_path_to_download));
header("Content-Transfer-Encoding: Binary");
header('Content-Disposition: inline; filename="'.basename($file_name).'"');
header('Expires: 0');
header("Cache-Control: post-check=0, pre-check=0");
header('Pragma: public');
header('Content-Length: ' . filesize($local_file_path_to_download));
readfile($local_file_path_to_download);
exit;
