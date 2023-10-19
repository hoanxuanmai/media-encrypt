<?php
/**
 * Created by HoanXuanMai
 * Email: hoanxuanmai@gmail.com
 * Date: 10/15/2023
 */


namespace HXM\MediaEncrypt\Models;

use HXM\MediaEncrypt\Contracts\MediaEncryptInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;
use HXM\MediaEncrypt\Facades\MediaEncryptFacade;

class MediaEncrypt extends Model implements MediaEncryptInterface
{
    public $timestamps = false;
    protected $fillable = ['field','rows', 'file_name', 'mime_type', 'ext', 'size'];
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
    function setOriginContent($data): MediaEncrypt
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
     * @return MediaEncryptInterface
     */
    public function setNeedContent($needContent): MediaEncryptInterface
    {
        $this->needContent = $needContent;
        return $this;
    }
    function able(): MorphTo
    {
        return $this->morphTo();
    }
    public function toArray()
    {
        return $this->decrypt();
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
                $originContent = MediaEncryptFacade::getEncrypt()->decrypt($decryptString);
            } catch (\Exception $e) {
                $originContent =  null;
            }
            if ($originContent && $mime_type = $this->getRawOriginal('mime_type')) {
               
                Str::startsWith($originContent, 'base64') || $originContent = "base64,".$originContent;
                $originContent = "data:{$mime_type};".$originContent;
            }
            $this->setOriginContent($originContent);
            return $originContent;
        }
        return null;
    }
    function contents(): HasMany
    {
        return $this->hasMany(config('media_encrypt.model_content'))->orderBy('part');
    }

    static function booting()
    {
        static::saving(function(self $model) {

            $model->decrypt();
            if ($model->getNeedContent() == $model->getOriginContent()) {
                return false;
            }
            MediaEncryptFacade::encryptContentBeforeSave($model);
            if (!$model->encryptedRows) {
                $model->exists && $model->delete();
                return false;
            }
            if ($model->getKey() === null) {
                $model->setAttribute($model->getKeyName(), Str::uuid()->toString());
            }
        });

        static::saved(function(self $model) {
            MediaEncryptFacade::saveDataAfterSaved($model);
        });

        static::deleted(function(self $model) {
            $model->contents()->delete();
        });
    }
}
