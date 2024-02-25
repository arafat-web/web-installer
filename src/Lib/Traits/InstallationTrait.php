<?php

namespace Abedin\WebInstaller\Lib\Traits;

use Exception;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Output\BufferedOutput;

trait InstallationTrait
{
    public function setupEnv(array $data): void
    {
        $path = base_path('.env');
        $file = file($path); // Open File Line By line
        $diffFileLines = array_diff($file, ["\n"]); // Remove all empty lines

        foreach($diffFileLines as $lineNo => $value){
            if (strpos($value, 'APP_KEY') !== false) {
                $file[$lineNo] = 'APP_KEY=base64:'. base64_encode(random_bytes(32)) . "\n";
            }
        }

        foreach($data as $peremiter => $newValue){
            $exists = false;
            foreach($diffFileLines as $lineNo => $oldValue){
                if (strpos($oldValue, $peremiter . '=') !== false) {
                    $file[$lineNo] = $peremiter .'='. $newValue . "\n";
                    $exists = true;
                }
            }
            if(!$exists){
                $file[] = $peremiter .'='. $newValue . "\n";
            }
        }
        file_put_contents($path, implode('', $file));
    }

    /**
     * check the fuilds is for database.
     * @return bool
     * @var string
     */
    public function isDbCredential($fiulds): bool
    {
        return substr($fiulds, 0, 2) === 'DB' ? true : false;
    }

    /**
     * check data base connection.
     * @return bool
     * @var string $dbHost
     * @var string $dbName
     * @var string $dbuser
     * @var string $dbPass
     */
    public function checkDatabaseConnection(array $data): bool
    {
        try {
            if (@mysqli_connect($data['DB_HOST'], $data['DB_USERNAME'], $data['DB_PASSWORD'], $data['DB_DATABASE'])) {
                return true;
            } else {
                return false;
            }
        }catch(Exception $exception){
            return false;
        }
    }

    /**
     * Check .env and possible to migration
     * @return void
     */
    public function getReadyToRun(): void
    {
        $outputLog = new BufferedOutput;
        Artisan::call('migrate:fresh', ['--force' => true], $outputLog);

        if(config('installer.seeder_run')){
            Artisan::call('db:seed', ['--force' => true], $outputLog);
        }
    }

    protected function createInstalationFile(): void
    {
        $signature =  base64_encode('Congratulations, The Installation Process is Completed successfully. The Installation process is made by Joynal Abedin. Thanks for with us');
        $path = storage_path('installed');
        file_put_contents($path, $signature);
    }

    public function readytoImportMigration()
    {

    }
}