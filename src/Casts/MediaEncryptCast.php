<?php

namespace HXM\MediaEncrypt\Casts;

use HXM\MediaEncrypt\Contracts\CanMediaEncryptInterface;
use HXM\MediaEncrypt\Facades\MediaEncryptFacade;
use HXM\MediaEncrypt\Models\MediaEncrypt;
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
        return $model->getNeedEncryptByField($key) ?? $model->getEncryptedByField($key);

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
        if ($value instanceof MediaEncrypt) {
            $model->setEncrypted($key, $value);
            return null;
        }

        /** @var MediaEncrypt $instance */
        $instance = $model->getEncryptedByField($key);
        $instance || $instance = $model->media_encrypts()->make([
            'field' => $key
        ]);
        $instance->setNeedContent($value);
        $model->setNeedEncrypt($key, $instance);
        return null;
    }
}
