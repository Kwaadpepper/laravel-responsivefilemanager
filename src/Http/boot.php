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

mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
mb_http_input('UTF-8');
mb_language('uni');
mb_regex_encoding('UTF-8');
ob_start('mb_output_handler');
date_default_timezone_set('Europe/Rome');
setlocale(LC_CTYPE, 'en_US'); //correct transliteration

// No Pre output if force download file
if (basename(request()->server('REQUEST_URI')) == "dialog.php") {
    // ALLOW Crossscript for resource load
    header("content-type: text/html; charset=UTF-8");
    header("Access-Control-Allow-Origin: https://code.jquery.com");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');    // cache for 1 day
    header('Access-Control-Allow-Headers: X-Requested-With');

    // Access-Control headers are received during OPTIONS requests
    if (request()->server('REQUEST_METHOD') == 'OPTIONS') {
        if (request()->server('HTTP_ACCESS_CONTROL_REQUEST_METHOD')) {
            header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
        }

        if (request()->server('HTTP_ACCESS_CONTROL_REQUEST_HEADERS')) {
            header("Access-Control-Allow-Headers:        ".request()->server('HTTP_ACCESS_CONTROL_REQUEST_HEADERS'));
        }

        exit(0);
    }

    mb_internal_encoding(FM_mb_internal_encoding);
    mb_http_output(FM_mb_http_output);
    mb_http_input(FM_mb_http_input);
    mb_language(FM_mb_language);
    mb_regex_encoding(FM_mb_regex_encoding);
    ob_start(FM_ob_start);
    date_default_timezone_set(FM_date_default_timezone_set);
}

$availableLangs = include __DIR__.'/../I18N/languages.php';

$preferredLang = RFM::getPreferredLanguage($availableLangs);
app()->setLocale($preferredLang);
session()->put('RF.language', $preferredLang);
