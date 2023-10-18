<?php
/**
 * Created by HoanXuanMai
 * Email: hoanxuanmai@gmail.com
 * Date: 10/15/2023
 */

namespace HXM\MediaEncrypt\Contracts;

use HXM\MediaEncrypt\Contracts\MediaEncryptInterface as MediaEncrypt;
use Illuminate\Database\Eloquent\Relations\MorphMany;

interface CanMediaEncryptInterface
{

    /**
     * get list Media Encrypt fields
     * @return array
     */
    function getMediaEncryptFields(): array;

    /**
     * get List Attributes need to save
     * @return array
     */
    public function getNeedEncryptAttributes(): array;

    /**
     * set content need to encrypt
     * @param $key
     * @param $value
     * @return self
     */
    public function setNeedEncryptAttribute($key, $value): self;

    /**
     * add field to list encrypted
     * @param MediaEncrypt $model
     * @return self
     */
    public function setEncryptedAttribute(MediaEncrypt $model): self;

    /**
     * get Instance MediaEncrypt of field
     * @param $field
     * @return MediaEncrypt|null
     */
    public function getEncryptedAttribute($field): ?MediaEncrypt;

    /**
     * @param $field
     * @return bool
     */
    public function hasNeedEncryptAttribute($field): bool;

    /**
     * @return MorphMany
     */
    function media_encrypts(): MorphMany;
}
