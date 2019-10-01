<?php

namespace Kwaadpepper\ResponsiveFileManager\Controller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use \Exception;
use Illuminate\Http\UploadedFile;
use \stdClass;
use Kwaadpepper\ResponsiveFileManager\RFM;
use Kwaadpepper\ResponsiveFileManager\UploadHandler;
use Kwaadpepper\ResponsiveFileManager\RFMMimeTypesLib;

/**
 * @author Jérémy Munsch
 */
class UploadController extends Controller
{

    public function index(Request $request)
    {
        try {
            $ftp = RFM::ftpCon(config('rfm'));
        
            if ($ftp) {
                $source_base = config('rfm.ftp_base_folder') . config('rfm.upload_dir');
                $thumb_base = config('rfm.ftp_base_folder') . config('rfm.ftp_thumbs_dir');
            } else {
                $source_base = config('rfm.current_path');
                $thumb_base = config('rfm.thumbs_base_path');
            }
        
            if ($request->has('fldr')) {
                $request->set('fldr', str_replace('undefined', '', $request->post('fldr')));
                $storeFolder = $source_base . $request->post('fldr');
                $storeFolderThumb = $thumb_base . $request->post('fldr');
            } else {
                return;
            }
        
            $fldr = rawurldecode(trim($request->post('fldr'), "/") . "/");
        
            if (!RFM::checkRelativePath($fldr)) {
                response(__('wrong path') . RFM::addErrorLocation())->send();
                exit;
            }
        
            $path = $storeFolder;
            $path = RFM::fixDirname($path) . '/';
            $messages = __("Upload_error_messages");
        
            // make sure the length is limited to avoid DOS attacks
            if ($request->has('url') && strlen($request->post('url', '')) < 2000) {
                $url = $request->post('url');
                $urlPattern = '/^(https?:\/\/)?([\da-z\.-]+\.[a-z\.]{2,6}|[\d\.]+)([\/?=&#]{1}[\da-z\.-]+)*[\/\?]?$/i';
        
                if (preg_match($urlPattern, $url)) {
                    $temp_path = tempnam('/tmp', 'RF');
        
                    $ch = curl_init($url);
                    $fp = fopen($temp_path, 'wb');
                    curl_setopt($ch, CURLOPT_FILE, $fp);
                    curl_setopt($ch, CURLOPT_HEADER, 0);
                    curl_exec($ch);
                    if (curl_errno($ch)) {
                        curl_close($ch);
                        throw new Exception('Invalid URL');
                    }
                    curl_close($ch);
                    fclose($fp);

                    $request->files->add([new UploadedFile(
                        $temp_path, // path
                        basename($request->post('url')), /// filename
                        RFMMimeTypesLib::getFileMimeType($temp_path)
                    )]);
                } else {
                    throw new Exception('Is not a valid URL.');
                }
            }
        
            if (config('rfm.mime_extension_rename')) {
                foreach ($request->files as $file) {
                    $info = pathinfo($file->originalName);
                    $mime_type = RFMMimeTypesLib::getFileMimeType($file->path);
                    $extension = RFMMimeTypesLib::getExtensionFromMime($mime_type);
                    if ($extension == 'so' || $extension == '' || $mime_type == "text/troff") {
                        $extension = $info['extension'];
                    }
                    $file->originalName = $info['filename'] . "." . $extension;
                    $file->mimeType = $mime_type;
                }
            }

            // Filter all files
            foreach ($request->files as $file) {
                $file->originalName = RFM::fixGetParams($file->originalName, config('rfm'));
                if (!$file->mimeType) {
                    $file->mimeType = RFMMimeTypesLib::getFileMimeType($file->path);
                }
                // LowerCase
                if (config('rfm.lower_case')) {
                    $file->originalName = RFM::fixStrtolower($file->originalName);
                }
            }

            // Bout de code à réparer
            // Check max upload error reached ?
            // if (!RFM::checkresultingsize($_FILES['files']['size'][0])) {
            //     if (!isset($upload_handler->response['files'][0])) {
            //         // Avoid " Warning: Creating default object from empty value ... "
            //         $upload_handler->response['files'][0] = new stdClass();
            //     }
            //     $upload_handler->response['files'][0]->error = __(
            //         'max_size_reached',
            //         ['size' => $config['MaxSizeTotal']]
            //     ).RFM::addErrorLocation();
            //     echo json_encode($upload_handler->response);
            //     exit();
            // }


            $uploadConfig = array(
                'config' => config('rfm'),
                'storeFolder' => $storeFolder,
                'storeFolderThumb' => $storeFolderThumb,
                'ftp' => $ftp,
                'upload_dir' => dirname($_SERVER['SCRIPT_FILENAME']) . '/' . $storeFolder,
                'upload_url' => config('rfm.base_url') . config('rfm.upload_dir') . $fldr,
                'mkdir_mode' => config('rfm.folderPermission'),
                'max_file_size' => config('rfm.MaxSizeUpload') * 1024 * 1024,
                'correct_image_extensions' => true,
                'print_response' => false
            );
        
            if (!config('rfm.ext_blacklist')) {
                $uploadConfig['accept_file_types'] = '/\.(' . implode('|', config('rfm.ext')) . ')$/i';
        
                if (config('rfm.files_without_extension')) {
                    $uploadConfig['accept_file_types'] = '/((\.(' .
                            implode('|', config('rfm.ext')) . ')$)|(^[^.]+$))$/i';
                }
            } else {
                $uploadConfig['accept_file_types'] = '/\.(?!' . implode('|', config('rfm.ext_blacklist')) . '$)/i';
        
                if (config('rfm.files_without_extension')) {
                    $uploadConfig['accept_file_types'] = '/((\.(?!' . implode('|', config('rfm.ext_blacklist')) . '$))|(^[^.]+$))/i';//phpcs:ignore
                }
            }
        
            if ($ftp) {
                if (!is_dir(config('rfm.ftp_temp_folder'))) {
                    mkdir(config('rfm.ftp_temp_folder'), config('rfm.folderPermission'), true);
                }
        
                if (!is_dir(config('rfm.ftp_temp_folder') . "thumbs")) {
                    mkdir(config('rfm.ftp_temp_folder') . "thumbs", config('rfm.folderPermission'), true);
                }
        
                $uploadConfig['upload_dir'] = config('rfm.ftp_temp_folder');
            }
            
            // ?? à voir avec le bout de code à réparer au dessus
            $upload_handler = new UploadHandler($ftp, $uploadConfig, true, $messages);
        } catch (Exception $e) {
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
        
                if (!FM_DEBUG_ERROR_MESSAGE) {
                    dd($e, array("files" => $return));
                }
        
                echo json_encode(array("files" => $return));
                return;
            }
            if (!FM_DEBUG_ERROR_MESSAGE) {
                dd($e);
            }
            echo json_encode(array("error" => $e->getMessage()));
        }
    }
}
