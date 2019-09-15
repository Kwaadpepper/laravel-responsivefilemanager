<?php
/**
 * @author Alberto Peripolli https://responsivefilemanager.com/#contact-section
 * @source https://github.com/trippo/ResponsiveFilemanager
 *
 * Licenced under Attribution-NonCommercial 3.0 Unported (CC BY-NC 3.0)
 * https://creativecommons.org/licenses/by-nc/3.0/
 *
 * This work is licensed under the Creative Commons
 * Attribution-NonCommercial 3.0 Unported License.
 * To view a copy of this license, visit
 * http://creativecommons.org/licenses/by-nc/3.0/ or send a
 * letter to Creative Commons, 444 Castro Street, Suite 900,
 * Mountain View, California, 94041, USA.
 */

use Kwaadpepper\ResponsiveFileManager\RFM;
use Kwaadpepper\ResponsiveFileManager\RFMMimeTypesLib;

/**
 * Check RF session
 */
if (!session()->exists('RF') || session('RF.verify') != "RESPONSIVEfilemanager") {
    RFM::response(__('forbidden') . RFM::addErrorLocation(), 403)->send();
    exit;
}

if (!RFM::checkRelativePath(request()->post('path')) || strpos(request()->post('path'), '/') === 0) {
    RFM::response(__('wrong path') . RFM::addErrorLocation(), 400)->send();
    exit;
}

if (strpos(request()->post('name'), '/') !== false) {
    RFM::response(__('wrong path') . RFM::addErrorLocation(), 400)->send();
    exit;
}

$ftp = RFM::ftpCon(config('rfm'));

if ($ftp) {
    $path = config('rfm.ftp_base_folder') .  config('rfm.upload_dir') . request()->post('path');
} else {
    $path = config('rfm.current_path') . request()->post('path');
}

$name = request()->post('name');
$info = pathinfo($name);

if (!RFM::checkExtension($info['extension'], config('rfm'))) {
    RFM::response(__('wrong extension') . RFM::addErrorLocation(), 400)->send();
    exit;
}

$file_name = $info['basename'];
$file_ext = $info['extension'];
$file_path = $path . $name;

$local_file_path_to_download = "";
// make sure the file exists
if ($ftp && RFM::ftpDownloadFile($ftp, $file_path, $file_name.'.'.$file_ext, $local_file_path_to_download)) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header("Content-Transfer-Encoding: Binary");
    header('Content-Disposition: attachment; filename="'.basename($file_name).'"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($local_file_path_to_download));
    readfile($local_file_path_to_download);
    exit;
} elseif (is_file($file_path) && is_readable($file_path)) {
    if (!file_exists($path . $name)) {
        RFM::response(__('File_Not_Found') . RFM::addErrorLocation(), 404)->send();
        exit;
    }

    $size = filesize($file_path);
    $file_name = rawurldecode($file_name);


    $mime_type = RFMMimeTypesLib::getFileMimeType($file_path);


    @ob_end_clean();
    if (ini_get('zlib.output_compression')) {
        ini_set('zlib.output_compression', 'Off');
    }
    header('Content-Type: ' . $mime_type);
    header('Content-Disposition: attachment; filename="' . $file_name . '"');
    header("Content-Transfer-Encoding: binary");
    header('Accept-Ranges: bytes');

    if (request()->header('HTTP_RANGE')) {
        list($a, $range) = explode("=", request()->header('HTTP_RANGE'), 2);
        list($range) = explode(",", $range, 2);
        list($range, $range_end) = explode("-", $range);
        $range = intval($range);
        if (!$range_end) {
            $range_end = $size - 1;
        } else {
            $range_end = intval($range_end);
        }

        $new_length = $range_end - $range + 1;
        header("HTTP/1.1 206 Partial Content");
        header("Content-Length: $new_length");
        header("Content-Range: bytes $range-$range_end/$size");
    } else {
        $new_length = $size;
        header("Content-Length: " . $size);
    }

    $chunksize = 1 * (1024 * 1024);
    $bytes_send = 0;

    if ($file = fopen($file_path, 'r')) {
        if (request()->header('HTTP_RANGE')) {
            fseek($file, $range);
        }

        while (!feof($file) &&
            (!connection_aborted()) &&
            ($bytes_send < $new_length)
        ) {
            $buffer = fread($file, $chunksize);
            echo $buffer;
            flush();
            $bytes_send += strlen($buffer);
        }
        fclose($file);
    } else {
        die('Error - can not open file.');
    }

    die();
} else {
    // file does not exist
    header("HTTP/1.0 404 Not Found");
}

exit;
