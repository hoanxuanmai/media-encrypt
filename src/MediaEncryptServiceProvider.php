<?php
/**
 * Created by HoanXuanMai
 * Email: hoanxuanmai@gmail.com
 * Date: 10/15/2023
 */
namespace HXM\MediaEncrypt;

use HXM\MediaEncrypt\Controller\MediaEncryptLinkController;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class MediaEncryptServiceProvider extends \Illuminate\Support\ServiceProvider
{
    function register()
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->mergeConfigFrom(__DIR__.'/../config/media_encrypt.php', 'media_encrypt');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../database/migrations/2023_10_15_125626_media_encrypt_install.php',
            ], 'media-encrypt-migrarte');
            $this->publishes([
                __DIR__.'/../config/media_encrypt.php',
            ], 'media-encrypt-config');
        }

    }

    function boot()
    {
        $this->app->singleton('media_encrypt_tool', function($app){
            $config = $app->make('config')->get('media_encrypt');
            if (Str::startsWith($key = $config['key'] ?? '', $prefix = 'base64:')) {
                $key = base64_decode(Str::after($key, $prefix));
            }

            $encrypt = null;
            if ($key && $cipher = ($config['cipher'] ?? null)) {
                $encrypt = new Encrypter($key, $cipher);
            }

            return new MediaEncryptTool($encrypt);
        });
        Route::middleware(['web'])
            ->prefix(config('media_encrypt.url_prefix'))
            ->name('media_encript.link')
            ->get('{mediaEncrypt}', MediaEncryptLinkController::class);
    }
}
