<?php

namespace HXM\MediaEncrypt\Casts;

use HXM\MediaEncrypt\Contracts\CanMediaEncryptInterface;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class MediaEncryptCast implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  CanMediaEncryptInterface|Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function get($model, $key, $value, $attributes)
    {
        return app('media_encrypt_tool')->decryptData($model, $key);
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  CanMediaEncryptInterface|Model  $model
     * @param  string  $key
     * @param  array  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function set($model, $key, $value, $attributes)
    {
        $model->setNeedEncryptAttribute($key, $value);
        return null;
    }
}
