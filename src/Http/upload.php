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

use \Exception as _Exception;
use \stdClass as _stdClass;
use \Kwaadpepper\ResponsiveFileManager\RFM;
use \Kwaadpepper\ResponsiveFileManager\UploadHandler;
use \Kwaadpepper\ResponsiveFileManager\RfmMimeTypesLib;

$config = config('rfm');

/**
 * Check RF session
 */
if (!session()->exists('RF') || session('RF.verify') != "RESPONSIVEfilemanager") {
    RFM::response(__('forbidden') . RFM::addErrorLocation(), 403)->send();
    exit;
}

try {
    $ftp = RFM::ftpCon($config);

    if ($ftp) {
        $source_base = $config['ftp_base_folder'] . $config['upload_dir'];
        $thumb_base = $config['ftp_base_folder'] . $config['ftp_thumbs_dir'];
    } else {
        $source_base = $config['current_path'];
        $thumb_base = $config['thumbs_base_path'];
    }

    if (isset($_POST["fldr"])) {
        $_POST['fldr'] = str_replace('undefined', '', $_POST['fldr']);
        $storeFolder = $source_base . $_POST["fldr"];
        $storeFolderThumb = $thumb_base . $_POST["fldr"];
    } else {
        return;
    }

    $fldr = rawurldecode(trim(strip_tags($_POST['fldr']), "/") . "/");

    if (!RFM::checkRelativePath($fldr)) {
        response(__('wrong path') . RFM::addErrorLocation())->send();
        exit;
    }

    $path = $storeFolder;
    $cycle = true;
    $max_cycles = 50;
    $i = 0;
    //GET config
    while ($cycle && $i < $max_cycles) {
        $i++;
        if ($path == $config['current_path']) {
            $cycle = false;
        }
        if (file_exists($path . "config.php")) {
            $configTemp = include $path . 'config.php';
            $config = array_merge($config, $configTemp);
            //TODO switch to array
            $cycle = false;
        }
        $path = RFM::fixDirname($path) . '/';
    }

    $messages = null;
    if (__("Upload_error_messages") !== "Upload_error_messages") {
        $messages = __("Upload_error_messages");
    }

    // make sure the length is limited to avoid DOS attacks
    if (isset($_POST['url']) && strlen($_POST['url']) < 2000) {
        $url = $_POST['url'];
        $urlPattern = '/^(https?:\/\/)?([\da-z\.-]+\.[a-z\.]{2,6}|[\d\.]+)([\/?=&#]{1}[\da-z\.-]+)*[\/\?]?$/i';

        if (preg_match($urlPattern, $url)) {
            $temp = tempnam('/tmp', 'RF');

            $ch = curl_init($url);
            $fp = fopen($temp, 'wb');
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_exec($ch);
            if (curl_errno($ch)) {
                curl_close($ch);
                throw new _Exception('Invalid URL');
            }
            curl_close($ch);
            fclose($fp);

            $_FILES['files'] = array(
                'name' => array(basename($_POST['url'])),
                'tmp_name' => array($temp),
                'size' => array(filesize($temp)),
                'type' => null
            );
        } else {
            throw new _Exception('Is not a valid URL.');
        }
    }


    if ($config['mime_extension_rename']) {
        $info = pathinfo($_FILES['files']['name'][0]);
        $mime_type = $_FILES['files']['type'][0];
        if (function_exists('mime_content_type')) {
            $mime_type = mime_content_type($_FILES['files']['tmp_name'][0]);
        } elseif (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $_FILES['files']['tmp_name'][0]);
        } else {
            $mime_type = RfmMimeTypesLib::getFileMimeType($_FILES['files']['tmp_name'][0]);
        }
        $extension = RfmMimeTypesLib::getExtensionFromMime($mime_type);

        if ($extension == 'so' || $extension == '' || $mime_type == "text/troff") {
            $extension = $info['extension'];
        }
        $filename = $info['filename'] . "." . $extension;
    } else {
        $filename = $_FILES['files']['name'][0];
    }
    $_FILES['files']['name'][0] = RFM::fixGetParams($filename, $config);

    if (!$_FILES['files']['type'][0]) {
        $_FILES['files']['type'][0] = $mime_type;
    }
    // LowerCase
    if ($config['lower_case']) {
        $_FILES['files']['name'][0] = RFM::fixStrtolower($_FILES['files']['name'][0]);
    }
    if (!RFM::checkresultingsize($_FILES['files']['size'][0])) {
        if (!isset($upload_handler->response['files'][0])) {
            // Avoid " Warning: Creating default object from empty value ... "
            $upload_handler->response['files'][0] = new _stdClass();
        }
        $upload_handler->response['files'][0]->error = __(
            'max_size_reached',
            ['size' => $config['MaxSizeTotal']]
        ).RFM::addErrorLocation();
        echo json_encode($upload_handler->response);
        exit();
    }

    $uploadConfig = array(
        'config' => $config,
        'storeFolder' => $storeFolder,
        'storeFolderThumb' => $storeFolderThumb,
        'ftp' => $ftp,
        'upload_dir' => dirname($_SERVER['SCRIPT_FILENAME']) . '/' . $storeFolder,
        'upload_url' => $config['base_url'] . $config['upload_dir'] . $_POST['fldr'],
        'mkdir_mode' => $config['folderPermission'],
        'max_file_size' => $config['MaxSizeUpload'] * 1024 * 1024,
        'correct_image_extensions' => true,
        'print_response' => false
    );

    if (!$config['ext_blacklist']) {
        $uploadConfig['accept_file_types'] = '/\.(' . implode('|', $config['ext']) . ')$/i';

        if ($config['files_without_extension']) {
            $uploadConfig['accept_file_types'] = '/((\.(' . implode('|', $config['ext']) . ')$)|(^[^.]+$))$/i';
        }
    } else {
        $uploadConfig['accept_file_types'] = '/\.(?!' . implode('|', $config['ext_blacklist']) . '$)/i';

        if ($config['files_without_extension']) {
            $uploadConfig['accept_file_types'] = '/((\.(?!' . implode('|', $config['ext_blacklist']) . '$))|(^[^.]+$))/i';//phpcs:ignore
        }
    }

    if ($ftp) {
        if (!is_dir($config['ftp_temp_folder'])) {
            mkdir($config['ftp_temp_folder'], $config['folderPermission'], true);
        }

        if (!is_dir($config['ftp_temp_folder'] . "thumbs")) {
            mkdir($config['ftp_temp_folder'] . "thumbs", $config['folderPermission'], true);
        }

        $uploadConfig['upload_dir'] = $config['ftp_temp_folder'];
    }

    $upload_handler = new UploadHandler($ftp, $uploadConfig, true, $messages);
} catch (_Exception $e) {
    $return = array();

    if ($_FILES['files']) {
        foreach ($_FILES['files']['name'] as $i => $name) {
            $return[] = array(
                'name' => $name,
                'error' => $e->getMessage(),
                'size' => $_FILES['files']['size'][$i],
                'type' => $_FILES['files']['type'][$i]
            );
        }
        //kent
        if (defined('FM_DEBUG_ERROR_MESSAGE') && !FM_DEBUG_ERROR_MESSAGE) {
            dd($e, array("files" => $return));
        }

        echo json_encode(array("files" => $return));
        return;
    }
    //kent
    if (defined('FM_DEBUG_ERROR_MESSAGE') && !FM_DEBUG_ERROR_MESSAGE) {
        dd($e);
    }
    echo json_encode(array("error" => $e->getMessage()));
}
