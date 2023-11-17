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
     * run init Model
     * @return void
     */
    function initializeHasMediaEncrypt();

    /**
     * Set the given relationship on the model.
     *
     * @param string $relation
     * @param mixed $value
     * @return CanMediaEncryptInterface
     */
    public function setRelation($relation, $value);

    /**
     * @return array
     */
    public function getMediaEncryptFields(): array;

    /**
     * @param $field
     * @return bool
     */
    function isMultiMediaEncryptField($field): bool;

    /**
     * @param MediaEncrypt|MediaEncrypt[] $mediaEncrypted
     * @return CanMediaEncryptInterface
     */
    public function setEncrypted($field, $mediaEncrypted): CanMediaEncryptInterface;

    /**
     * get data encrypted by field
     * @param $field
     * @return MediaEncrypt|MediaEncrypt[]|null
     */
    public function getEncryptedByField($field);

    /**
     * @param $field
     * @return bool
     */
    public function hasNeedEncrypt($field): bool;

    /**
     * get need data to encrypt by field
     * @param $field
     * @return mixed|null
     */
    public function getNeedEncryptByField($field);

    /**
     * get all need data
     * @return array
     */
    public function getNeedEncrypts(): array;

    /**
     * set data need encrypt into field
     * @param $key
     * @param $value
     * @return \HXM\MediaEncrypt\Contracts\CanMediaEncryptInterface
     */
    public function setNeedEncrypt($field, $value): CanMediaEncryptInterface;

    /**
     * @return MorphMany
     */
    function media_encrypts(): MorphMany;

    /**
     * @return array
     */
    function getAttributes();

    public function getFillable();

    /**
     * @return array
     */
    function toArray();
}
