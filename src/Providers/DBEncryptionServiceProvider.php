<?php
/**
 * src/Providers/EncryptServiceProvider.php.
 */

namespace Hatcher\DBEncryption\Providers;

use Hatcher\DBEncryption\Console\Commands\DecryptModel;
use Hatcher\DBEncryption\Console\Commands\EncryptModel;
use Hatcher\DBEncryption\Encrypter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class DBEncryptionServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * This method is called after all other service providers have
     * been registered, meaning you have access to all other services
     * that have been registered by the framework.
     *
     * @return void
     */
    public function boot()
    {
        $this->bootValidators();

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../Config/config.php' => config_path('laravelDatabaseEncryption.php'),
            ], 'config');

            $this->commands([
                EncryptModel::class,
                DecryptModel::class,
            ]);
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->publishes([
            __DIR__ . '/../Config/config.php' => config_path('laravelDatabaseEncryption.php'),
        ], 'config');
    }

    private function bootValidators()
    {
        Validator::extend('unique_encrypted', function ($attribute, $value, $parameters, $validator) {
            // Initialize
            $salt = Encrypter::getKey();

            $withFilter = count($parameters) > 3;

            $ignore_id = $parameters[2] ?? '';

            // Check using normal checker
            $data = DB::table($parameters[0])->whereRaw("CONVERT(AES_DECRYPT(FROM_BASE64(`{$parameters[1]}`), '{$salt}') USING utf8mb4) = '{$value}' ");
            $data = $ignore_id != '' ? $data->where('id', '!=', $ignore_id) : $data;

            if ($withFilter) $data->where($parameters[3], $parameters[4]);

            if ($data->first()) return false;

            return true;
        });

        Validator::extend('exists_encrypted', function ($attribute, $value, $parameters, $validator) {
            // Initialize
            $salt = Encrypter::getKey();

            $withFilter = count($parameters) > 3;
            if (! $withFilter) {
                $ignore_id = $parameters[2] ?? '';
            } else {
                $ignore_id = $parameters[4] ?? '';
            }

            // Check using normal checker
            $data = DB::table($parameters[0])->whereRaw("CONVERT(AES_DECRYPT(FROM_BASE64(`{$parameters[1]}`), '{$salt}') USING utf8mb4) = '{$value}' ");
            $data = $ignore_id != '' ? $data->where('id', '!=', $ignore_id) : $data;

            if ($withFilter) {
                $data->where($parameters[2], $parameters[3]);
            }

            if ($data->first()) {
                return true;
            }

            return false;
        });
    }
}
