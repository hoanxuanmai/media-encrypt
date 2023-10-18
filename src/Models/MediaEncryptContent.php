<?php
/**
 * Created by HoanXuanMai
 * Email: hoanxuanmai@gmail.com
 * Date: 10/15/2023
 */

namespace HXM\MediaEncrypt\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MediaEncryptContent extends Model
{
    public $timestamps = false;
    protected $keyType = "string";
    public $incrementing = false;

    protected $fillable = ['media_encrypt_id', 'part', 'data'];

    static function booting()
    {
        static::saving(function(self $model) {
            if ($model->getKey() === null) {
                $model->setAttribute($model->getKeyName(), Str::uuid()->toString());
            }
        });
    }
}
