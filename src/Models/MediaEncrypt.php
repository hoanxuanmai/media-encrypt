<?php
/**
 * Created by HoanXuanMai
 * Email: hoanxuanmai@gmail.com
 * Date: 10/15/2023
 */


namespace HXM\MediaEncrypt\Models;

use HXM\MediaEncrypt\Contracts\MediaEncryptInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MediaEncrypt extends Model implements MediaEncryptInterface
{
    public $timestamps = false;
    protected $fillable = ['field', 'file_name', 'mime_type', 'ext', 'size'];
    protected $with = ['contents'];
    protected $keyType = 'string';
    public $incrementing = false;
    protected $originContent;
    protected $needContent;

    public $encryptedRows = [];

    /**
     * @param $data
     * @return $this
     */
    function setOriginContent($data)
    {
        $this->originContent = $data;
        return $this;
    }
    function getOriginContent()
    {
        return $this->originContent;
    }


    /**
     * @return mixed
     */
    public function getNeedContent()
    {
        return $this->needContent;
    }

    /**
     * @param $needContent
     * @return $this
     */
    public function setNeedContent($needContent): self
    {
        $this->needContent = $needContent;
        return $this;
    }
    function able()
    {
        return $this->morphTo();
    }

    /**
     * @return mixed|string|null
     */
    function decrypt()
    {
        if ($this->getOriginContent()) {
            return $this->getOriginContent();
        }
        if ($decryptString = $this->getRelationValue('contents')->implode('data')) {
            try {
                $originContent = app('media_encrypt_tool')->getEncrypt()->decrypt($decryptString);
            } catch (\Exception $e) {
                $originContent =  null;
            }
            if ($originContent && $this->getRawOriginal('file_name') && $mime_type = $this->getRawOriginal('mime_type')) {
                $originContent = "data:{$mime_type};base64,".$originContent;
            }
            $this->setOriginContent($originContent);
            return $originContent;
        }
        return null;
    }
    function contents(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MediaEncryptContent::class)->orderBy('part');
    }

    static function booting()
    {
        static::saving(function(self $model) {

            $model->decrypt();
            if ($model->getNeedContent() == $model->getOriginContent()) {
                return false;
            }
            app('media_encrypt_tool')->encryptContentBeforeSave($model);
            if (!$model->encryptedRows) {
                $model->exists && $model->delete();
                return false;
            }
            if ($model->getKey() === null) {
                $model->setAttribute($model->getKeyName(), Str::uuid()->toString());
            }
        });

        static::saved(function(self $model) {
            app('media_encrypt_tool')->saveDataAfterSaved($model);
        });

        static::deleted(function(self $model) {
            $model->contents()->delete();
        });
    }



}
