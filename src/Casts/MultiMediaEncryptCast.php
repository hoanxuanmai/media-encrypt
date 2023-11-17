<?php

namespace HXM\MediaEncrypt\Casts;

use HXM\MediaEncrypt\Contracts\CanMediaEncryptInterface;
use HXM\MediaEncrypt\Facades\MediaEncryptFacade;
use HXM\MediaEncrypt\Models\MediaEncrypt;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class MultiMediaEncryptCast implements CastsAttributes
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
//        return MediaEncryptFacade::decryptData($model, $key);
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
        $existed = $model->getEncryptedByField($key) ?? collect();

        $needSave = collect($value)->mapWithKeys(function($row, $index) use ($model, $key, $value, $existed) {
            /** @var MediaEncrypt $instance */
            $instance = $existed->get($index);
            $instance || $instance = $model->media_encrypts()->make([
                'index' => $index,
                'field' => $key
            ]);
            if (!$row instanceof MediaEncrypt){
                $instance->setNeedContent($row);
            }
            return [$index => $instance];
        });

        $model->setNeedEncrypt($key, $needSave);
        return null;
    }
}
