<?php
/**
 * RFM command line Interface
 * Mostly used to generate RFM private key
 * @category RFMGenerate
 * @package  ResponsiveFileManager
 * @author   Jeremy Munsch <kwaadpepper@users.noreply.github.com>
 * @license  MIT https://choosealicense.com/licenses/mit/
 * @version  GIT:
 * @link     https://github.com/Kwaadpepper/laravel-responsivefilemanager/blob/master/src/RFMGenerate.php
 */
namespace Kwaadpepper\ResponsiveFileManager;

use Illuminate\Console\Command;
use Illuminate\Encryption\Encrypter;
use Illuminate\Console\ConfirmableTrait;

/**
 * RFM command line Interface
 * Mostly used to generate RFM private key
 * @category Class
 * @package  RFMGenerate
 * @author   Jeremy Munsch <kwaadpepper@users.noreply.github.com>
 * @license  MIT https://choosealicense.com/licenses/mit/
 * @link     https://github.com/Kwaadpepper/laravel-responsivefilemanager/blob/master/src/RFMGenerate.php
 */
class RFMGenerate extends Command
{
    use ConfirmableTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rfm:generate
                    {--show : Display the key instead of modifying files}
                    {--force : Force the operation to run when in production}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set Responsive File Manager private key';
    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $key = $this->generateRandomKey();
        if ($this->option('show')) {
            return $this->line('<comment>' . $key . '</comment>');
        }
        // Next, we will replace the application key in the environment file so it is
        // automatically setup for this developer. This key gets generated using a
        // secure random byte generator and is later base64 encoded for storage.
        if (!$this->setKeyInEnvironmentFile($key)) {
            return;
        }
        $this->laravel['config']['rfm.access_keys'] = [$key];
        $this->info("RFM key [$key] set successfully.");
    }
    /**
     * Generate a random key for the application.
     *
     * @return string
     */
    protected function generateRandomKey()
    {
        $this->info('generating RFM key..');
        return hash(
            'sha256',
            Encrypter::generateKey($this->laravel['config']['app.cipher'])
        );
    }
    /**
     * Set the application key in the environment file.
     *
     * @param string $key random sha256 hashed string
     *
     * @return bool
     */
    protected function setKeyInEnvironmentFile($key)
    {
        $currentKey =   isset($this->laravel['config']['rfm.access_keys'][0]) ?
                        $this->laravel['config']['rfm.access_keys'][0] : '';
        if (strlen($currentKey) !== 0 && (!$this->confirmToProceed())) {
            return false;
        }
        return $this->writeNewEnvironmentFileWith($key);
    }
    /**
     * Write a new environment file with the given key.
     *
     * @param string $key random sha256 hashed string
     *
     * @return bool
     */
    protected function writeNewEnvironmentFileWith($key)
    {
        $file = file_get_contents($this->laravel->environmentFilePath());
        $o = preg_match('/RFM_KEY=/', $file);
        switch ($o) {
            case 1:
                $this->info('overwriting RFM key..');
                return file_put_contents(
                    $this->laravel->environmentFilePath(),
                    preg_replace(
                        $this->keyReplacementPattern(),
                        'RFM_KEY=' . $key,
                        file_get_contents($this->laravel->environmentFilePath())
                    )
                );
            case 0:
                $this->info('writing RFM key..');
                return file_put_contents(
                    $this->laravel->environmentFilePath(),
                    PHP_EOL . 'RFM_KEY=' . $key . PHP_EOL,
                    FILE_APPEND | LOCK_EX
                );
            default:
                $this->error('Error reading .env file');
                return false;
        }
    }
    /**
     * Get a regex pattern that will match env APP_KEY with any random key.
     *
     * @return string
     */
    protected function keyReplacementPattern()
    {
        $k =    isset($this->laravel['config']['rfm.access_keys'][0]) ?
                $this->laravel['config']['rfm.access_keys'][0] : '';
        $escaped = preg_quote('=' . $k, '/');
        return "/^RFM_KEY{$escaped}/m";
    }
}
