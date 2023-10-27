<?php
/**
 * Created by HoanXuanMai
 * Email: hoanxuanmai@gmail.com
 * Date: 10/19/2023
 */

namespace HXM\MediaEncrypt\Facades;

use HXM\MediaEncrypt\Contracts\CanMediaEncryptInterface;
use HXM\MediaEncrypt\MediaEncryptTool;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\Facade;

/**
 * @method static Encrypter getEncrypt()
 * @method static mixed decryptData(CanMediaEncryptInterface &$model, $field)
 *
 * @see MediaEncryptTool
 */
class MediaEncryptFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'media_encrypt_tool';
    }

    static function getModelClass(): string
    {
        return config('media_encrypt.model');
    }

    static function getModelContentClass(): string
    {
        return config('media_encrypt.model_content');
    }

    static function getRowLength(): int
    {
        return (int) config('media_encrypt.row_length', 4e9);
    }

    static function allowEagerLoading(): bool
    {
        return (bool) config('media_encrypt.allow_eager_loading', true);
    }

    static function allowAppend(): bool
    {
        return (bool) config('media_encrypt.allow_append', false);
    }
}
