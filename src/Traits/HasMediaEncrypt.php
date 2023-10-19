<?php
/**
 * Created by HoanXuanMai
 * Email: hoanxuanmai@gmail.com
 * Date: 10/15/2023
 */

namespace HXM\MediaEncrypt\Traits;

use HXM\MediaEncrypt\Casts\MediaEncryptCast;
use HXM\MediaEncrypt\Contracts\CanMediaEncryptInterface;
use HXM\MediaEncrypt\Contracts\MediaEncryptInterface;
use HXM\MediaEncrypt\Facades\MediaEncryptFacade;
use HXM\MediaEncrypt\Models\MediaEncrypt;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasMediaEncrypt
{
    /**
     * the attributes need to encrypt when save
     * @var array
     */
    protected $needEncryptAttributes = [];

    /**
     * @var MediaEncrypt[]
     */
    protected $encryptedAttributes = [];

    protected $mediaEncryptFields = [];

    /**
     * run boot Model
     * @return void
     */
    static function bootHasMediaEncrypt()
    {
        static::saved(function(CanMediaEncryptInterface $model){
            $mediaAttributes = $model->getMediaEncryptFields();
            foreach ($model->getNeedEncryptAttributes() as $field => $value) {
                if (in_array($field, $mediaAttributes)) {
                    $instance = $model->getEncryptedAttribute($field);
                    /** @var MediaEncrypt $instance */
                    $instance || $instance = $model->media_encrypts()->make([
                       'field' => $field
                    ]);
                    $instance->setNeedContent($value)->save();
                    $model = $model->setEncryptedAttribute($instance);
                }
            }
        });
    }

    /**
     * run init Model
     * @return void
     */
    function initializeHasMediaEncrypt()
    {
        $this->makeHidden('media_encrypts');
        $this->with = array_unique(array_merge($this->with, ['media_encrypts']));
        $fillAble = $this->getFillable();
        foreach ($this->getCasts() as $field => $cast) {
            if (! in_array($field, $fillAble) && $cast === MediaEncryptCast::class) {
                $this->mediaEncryptFields[] = $field;
                $this->makeHidden($field);
            }
        }
    }

    /**
     * Set the given relationship on the model.
     *
     * @param  string  $relation
     * @param  mixed  $value
     * @return $this
     */
    public function setRelation($relation, $value)
    {
        if ($relation === 'media_encrypts') {
            foreach ($value as $encrypted) {
                $this->setEncryptedAttribute($encrypted);
            }
        }
        return parent::setRelation($relation, $value);
    }

    /**
     * @return array
     */
    public function getMediaEncryptFields(): array
    {
        return $this->mediaEncryptFields;
    }

    /**
     * @param $field
     * @param MediaEncrypt $mediaEncrypt
     * @return CanMediaEncryptInterface
     */
    public function setEncryptedAttribute(MediaEncryptInterface $mediaEncrypt): CanMediaEncryptInterface
    {
        $this->encryptedAttributes[$mediaEncrypt->field] = $mediaEncrypt;
        return $this;
    }

    /**
     * @param $field
     * @return MediaEncrypt|null
     */
    public function getEncryptedAttribute($field): ?MediaEncryptInterface
    {
        return $this->encryptedAttributes[$field] ?? null;
    }

    /**
     * @param $field
     * @return bool
     */
    public function hasNeedEncryptAttribute($field): bool
    {
        return  isset($this->needEncryptAttributes[$field]);
    }
    /**
     * @param $field
     * @return mixed|null
     */
    public function getNeedEncryptAttribute($field)
    {
        return $this->needEncryptAttributes[$field] ?? null;
    }


    /**
     * @return array
     */
    public function getNeedEncryptAttributes(): array
    {
        return $this->needEncryptAttributes;
    }

    /**
     * @param $key
     * @param $value
     * @return CanMediaEncryptInterface
     */
    public function setNeedEncryptAttribute($key, $value): CanMediaEncryptInterface
    {
        $this->needEncryptAttributes[$key] = $value;
        return $this;
    }


    /**
     * @return MorphMany
     */
    function media_encrypts(): MorphMany
    {
        return $this->morphMany(MediaEncryptFacade::getModelClass(), 'able');
    }


    /**
     * @return array
     */
    function getAttributes()
    {
        parent::getAttributes();
        foreach ($this->getMediaEncryptFields() as $field) {
            unset($this->attributes[$field]);
        }
        return $this->attributes;
    }

    public function getFillable()
    {
        return array_merge($this->mediaEncryptFields, $this->fillable);
    }

    /**
     * @return array
     */
    function toArray()
    {
        $result = parent::toArray();
        if (config('media_encrypt.allow_append')) {
            foreach ($this->getMediaEncryptFields() as $field) {
                if (!isset($result[$field]) && $this->hasAppended($field)) {
                    $result[$field] = $this->{$field};
                }
            }
        }

        return $result;
    }

}
