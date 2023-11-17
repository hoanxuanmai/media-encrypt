<?php
/**
 * Created by HoanXuanMai
 * Email: hoanxuanmai@gmail.com
 * Date: 10/15/2023
 */

namespace HXM\MediaEncrypt;

use HXM\MediaEncrypt\Contracts\CanMediaEncryptInterface;
use HXM\MediaEncrypt\Contracts\MediaEncryptInterface as MediaEncrypt;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Encryption\Encrypter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class MediaEncryptTool
{

    /**
     * @var Encrypter $encrypt
     */
    protected $encrypt;

    public function __construct($encrypt = null)
    {
        $this->encrypt = $encrypt ?: app()->get('encrypter');
    }


    /**
     * @return Encrypter
     */
    public function getEncrypt(): Encrypter
    {
        return $this->encrypt;
    }


    /**
     * @param $model
     * @param string $field
     * @return array|mixed|null
     */
    function decryptData(&$model, string $field)
    {
        $row = null;
        if ($model->hasNeedEncrypt($field)) {
            $row = $model->getNeedEncryptByField($field);
        }

        if (!$row) {
            $row = $model->getEncryptedByField($field);
        }

        if ($row) {
            return ($row instanceof Collection)
                ? $row->map(function($dt) {
                    return $dt->decrypt();
                })->toArray()
                : $row->decrypt();
        }
        return null;
    }
}
