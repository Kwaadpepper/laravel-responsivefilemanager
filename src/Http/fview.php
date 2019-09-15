<?php
/**
 * RFM command line Interface
 * Mostly used to generate RFM private key
 * @author   Jeremy Munsch <kwaadpepper@users.noreply.github.com>
 * @license  MIT https://choosealicense.com/licenses/mit/
 * @version  GIT:
 * @link     https://github.com/Kwaadpepper/laravel-responsivefilemanager/blob/master/resources/filemanager/fview.php
 */

use Kwaadpepper\ResponsiveFileManager\RFM;
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
    if (!FM_DEBUG_ERROR_MESSAGE) {
        throw new NotFoundHttpException();
    }
    RFM::response(__('no ox query') . RFM::addErrorLocation(), 400)->send();
    exit;
}

try {
    $get = decrypt(request()->get('ox'));
} catch (DecryptException $e) {
    if (!FM_DEBUG_ERROR_MESSAGE) {
        throw new NotFoundHttpException();
    }
    RFM::response(__('ox query decrypt failed') . RFM::addErrorLocation(), 400)->send();
    exit;
}

if (strpos($get['path'], '/') === 0) {
    $get['path'] = substr($get['path'], 1);
}

if (!RFM::checkRelativePath($get['path'])) {
    if (!FM_DEBUG_ERROR_MESSAGE) {
        throw new NotFoundHttpException();
    }
    RFM::response(__('path is wrong') . RFM::addErrorLocation(), 400)->send();
    exit;
}

if (strpos($get['name'], '/') !== false) {
    if (!FM_DEBUG_ERROR_MESSAGE) {
        throw new NotFoundHttpException();
    }
    RFM::response(__('name includes a forbidden \'/\' char') . RFM::addErrorLocation(), 400)->send();
    exit;
}

if (!($ftp = RFM::ftpCon($config))) {
    if (!FM_DEBUG_ERROR_MESSAGE) {
        throw new NotFoundHttpException();
    }
    RFM::response(__('FTP is not configured') . RFM::addErrorLocation(), 400)->send();
    exit;
}

$name = $get['name'];
$info = pathinfo($get['path']);

if (!RFM::checkExtension($info['extension'], $config)) {
    if (!FM_DEBUG_ERROR_MESSAGE) {
        throw new NotFoundHttpException();
    }
        RFM::response(__('wrong extension') . RFM::addErrorLocation(), 400)->send();
    exit;
}

$file_name = $info['basename'];
$file_ext = $info['extension'];
$file_path = $config['ftp_base_folder'] . '/' . $get['path'];

$local_file_path_to_download = "";
// make sure the file exists
if (!RFM::ftpDownloadFile($ftp, $file_path, $file_name.'.'.$file_ext, $local_file_path_to_download)) {
    if (!FM_DEBUG_ERROR_MESSAGE) {
        throw new NotFoundHttpException();
    }
    RFM::response(
        __('failed to fetch ftp file '.$file_name.'.'.$file_ext.' in '.$file_path).RFM::addErrorLocation(),
        400
    )->send();
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
