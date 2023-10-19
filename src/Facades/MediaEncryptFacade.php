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
 * @method static void encryptContentBeforeSave(\HXM\MediaEncrypt\Models\MediaEncrypt &$mediaEncrypt)
 * @method static void saveDataAfterSaved(\HXM\MediaEncrypt\Contracts\MediaEncryptInterface &$model)
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
}
