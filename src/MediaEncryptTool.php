<?php
/**
 * Created by HoanXuanMai
 * Email: hoanxuanmai@gmail.com
 * Date: 10/15/2023
 */

namespace HXM\MediaEncrypt;

use HXM\MediaEncrypt\Contracts\CanMediaEncryptInterface;
use HXM\MediaEncrypt\Contracts\MediaEncryptInterface as MediaEncrypt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Encryption\Encrypter;
use Illuminate\Http\UploadedFile;

class MediaEncryptTool
{
    /**
     * @var int
     */
    protected $rowLength;

    /**
     * @var Encrypter $encrypt
     */
    protected $encrypt;

    public function __construct(int $rowLength, $encrypt = null)
    {
        $this->rowLength = $rowLength;

        $this->encrypt = $encrypt ?: app()->get('encrypter');
    }


    /**
     * @return Encrypter
     */
    public function getEncrypt(): Encrypter
    {
        return $this->encrypt;
    }
    function encryptContentBeforeSave(MediaEncrypt &$model)
    {
        $content = $model->getNeedContent();

        $dataSave = [
            'file_name'=> null,
            'mime_type'=> null,
            'ext'=> null,
            'size'=> null,
        ];
        if ($content instanceof UploadedFile) {
            $dataSave = [
                'file_name'=> $content->getClientOriginalName(),
                'mime_type'=> $content->getClientMimeType(),
                'ext'=> $content->getClientOriginalExtension(),
                'size'=> $content->getSize(),
            ];
            $content = base64_encode($content->getContent());
        }
        $model->fill($dataSave);
        if ($content !== '' && $content !== null) {
            $stringSave = $this->getEncrypt()->encrypt($content);
            $rows = str_split($stringSave, $this->rowLength);
            $model->encryptedRows = $rows;
        } else {
            $model->encryptedRows = [];
        }
    }

    function saveDataAfterSaved(MediaEncrypt &$model)
    {
        if ($model->encryptedRows) {
            $model->wasRecentlyCreated || $model->contents()->delete();
            foreach ($model->encryptedRows as $part => $row) {
                $model->contents()->create([
                    'part' => $part,
                    'data' => $row
                ]);
            }
        }
    }

    /**
     * @param CanMediaEncryptInterface|Model $model
     * @param $field
     * @return mixed|string|null
     */
    function decryptData(&$model, $field)
    {
        if ($model->hasNeedEncryptAttribute($field))
            return $model->getNeedEncryptAttribute($field);

        $row = $model->getEncryptedAttribute($field);
        if ($row) {
            return $row->decrypt();
        }
        /** @var MediaEncrypt $row */
        $row = $model->getRelationValue('media_encrypts')->first(function($dt) use ($field){ return $dt->field == $field; });
        if ($row) {
            $model->setEncryptedAttribute($row);
            return $row->decrypt();
        }
        return null;
    }
}
