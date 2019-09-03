<?php

namespace Kwaadpepper\ResponsiveFileManager;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Blade;

class FileManagerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/routes.php');

        /**
         * Publish all static ressources
         */
        $FMPRIVPATH = "/../resources/filemanager/";
        $FMPUBPATH = "vendor/responsivefilemanager/";

        $FM_PUBLISH = [];

        $FM_PUBLISH[__DIR__.$FMPRIVPATH.'config/config.php'] = config_path('rfm.php');

        $FM_CSS =   ['jquery.fileupload-noscript.css',
                    'jquery.fileupload-ui-noscript.css',
                    'query.fileupload-ui.css',
                    'jquery.fileupload.css',
                    'rtl-style.css',
                    'style.css'];
        
        $FM_JS =    ['include.js','jquery.fileupload.js', 'jquery.iframe-transport.js',
                    'jquery.fileupload-angular.js','jquery.fileupload-process.js','modernizr.custom.js',
                    'jquery.fileupload-audio.js','jquery.fileupload-ui.js','plugins.js',
                    'jquery.fileupload-image.js','jquery.fileupload-validate.js','vendor/jquery.ui.widget.js',
                    'jquery.fileupload-jquery-ui.js','jquery.fileupload-video.js'];

        
        $FM_IMG =   ['clipboard_apply.png','clipboard_clear.png','copy.png','cut.png',
                    'date.png','dimension.png','down.png','download.png','duplicate.png',
                    'edit_img.png','file_edit.png','glyphicons-halfling-white.png','glyphicons-halflings.png','info.png',
                    'key.png','label.png','loading.png','logo.png','preview.png',
                    'processing.png','rename.png','size.png','sort.png','storing_animation.png',
                    'trans.png','up.png' ,'upload.png','url.png','zip.png'];

        $FM_ICO =   ['ac3.jpg','c4d.jpg','dxf.jpg','html.jpg','mov.jpg','odp.jpg','pdf.jpg','sql.jpg','webm.jpg',
                    'accdb.jpg','css.jpg','favicon.ico','iso.jpg','mp3.jpg','ods.jpg','png.jpg','stp.jpg','wma.jpg',
                    'ade.jpg','csv.jpg','fla.jpg','jpeg.jpg','mp4.jpg','odt.jpg','ppt.jpg','svg.jpg','xhtml.jpg',
                    'adp.jpg','default.jpg','flv.jpg','jpg.jpg','mpeg.jpg','ogg.jpg','pptx.jpg','tar.jpg','xls.jpg',
                    'aiff.jpg','dmg.jpg','folder_back.png','log.jpg','mpg.jpg','otg.jpg','psd.jpg','tiff.jpg','xlsx.jpg',
                    'ai.jpg','doc.jpg','folder.png','m4a.jpg','odb.jpg','otp.jpg','rar.jpg','txt.jpg','xml.jpg',
                    'avi.jpg','docx.jpg','gif.jpg','mdb.jpg','odf.jpg','ots.jpg','rtf.jpg','vwx.jpg','zip.jpg',
                    'bmp.jpg','dwg.jpg','gz.jpg','mid.jpg','odg.jpg','ott.jpg','skp.jpg','wav.jpg'];

        $FM_ICO_DARK =  ['ac3.jpg','css.jpg','flv.jpg','jpg.jpg','mpeg.jpg','ogg.jpg','pptx.jpg','txt.jpg','zip.jpg',
                        'accdb.jpg','csv.jpg','folder_back.png','log.jpg','mpg.jpg','otg.jpg','psd.jpg','wav.jpg',
                        'ade.jpg','default.jpg','folder.png','m4a.jpg','odb.jpg','otp.jpg','rar.jpg','webm.jpg',
                        'adp.jpg','dmg.jpg','gif.jpg','mdb.jpg','odf.jpg','ots.jpg','rtf.jpg','wma.jpg',
                        'aiff.jpg','doc.jpg','gz.jpg','mid.jpg','odg.jpg','ott.jpg','sql.jpg','xhtml.jpg',
                        'ai.jpg','docx.jpg','html.jpg','mov.jpg','odp.jpg','pdf.jpg','svg.jpg','xls.jpg',
                        'avi.jpg','favicon.ico','iso.jpg','mp3.jpg','ods.jpg','png.jpg','tar.jpg','xlsx.jpg',
                        'bmp.jpg','fla.jpg','jpeg.jpg','mp4.jpg','odt.jpg','ppt.jpg','tiff.jpg','xml.jpg'];
        
        $FM_SVG = ['icon-a.svg',  'icon-b.svg',  'icon-c.svg',  'icon-d.svg',  'svg.svg'];


        foreach ($FM_CSS as $css_file) {
            $FM_PUBLISH[__DIR__.$FMPRIVPATH.'css/'.$css_file] = public_path($FMPUBPATH.'css/'.$css_file);
        }

        foreach ($FM_JS as $js_file) {
            $FM_PUBLISH[__DIR__.$FMPRIVPATH.'js/'.$js_file] = public_path($FMPUBPATH.'js/'.$js_file);
        }

        foreach ($FM_IMG as $img_file) {
            $FM_PUBLISH[__DIR__.$FMPRIVPATH.'img/'.$img_file] = public_path($FMPUBPATH.'img/'.$img_file);
        }

        foreach ($FM_ICO as $img_file) {
            $FM_PUBLISH[__DIR__.$FMPRIVPATH.'img/ico/'.$img_file] = public_path($FMPUBPATH.'img/ico/'.$img_file);
        }

        foreach ($FM_ICO_DARK as $img_file) {
            $FM_PUBLISH[__DIR__.$FMPRIVPATH.'img/ico_dark/'.$img_file] = public_path($FMPUBPATH.'img/ico_dark/'.$img_file);
        }

        foreach ($FM_SVG as $img_file) {
            $FM_PUBLISH[__DIR__.$FMPRIVPATH.'svg/'.$img_file] = public_path($FMPUBPATH.'svg/'.$img_file);
        }

        $this->publishes($FM_PUBLISH);

        /**
         * Blade print
         */
        $inc = "<script src=\"{{ asset('".$FMPUBPATH."js/include.js') }}\" ></script>";
        Blade::directive('filemanager_javascript', function ($expression) {
            return "<?php echo \"".$inc."\"; ?>";
        });

        Blade::directive('filemanager_get_config', function ($expression) {
            return config($expression);
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        config([
            'config/config.php',
        ]);
    }
}
