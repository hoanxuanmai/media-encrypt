<?php
/**
 * Created by HoanXuanMai
 * Email: hoanxuanmai@gmail.com
 * Date: 10/15/2023
 */

namespace HXM\MediaEncrypt\Traits;

use HXM\MediaEncrypt\Casts\MediaEncryptCast;
use HXM\MediaEncrypt\Casts\MultiMediaEncryptCast;
use HXM\MediaEncrypt\Contracts\CanMediaEncryptInterface;
use HXM\MediaEncrypt\Contracts\MediaEncryptInterface;
use HXM\MediaEncrypt\Facades\MediaEncryptFacade;
use HXM\MediaEncrypt\Models\MediaEncrypt;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

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

    /**
     * @var array
     */
    protected $mediaEncryptFields = [];
    /**
     * @var array
     */
    protected $mediaEncryptFieldsTypeMulti = [];

    /**
     * run boot Model
     * @return void
     */
    static function bootHasMediaEncrypt()
    {
        static::saved(function(CanMediaEncryptInterface $model){
            $mediaAttributes = $model->getMediaEncryptFields();
            /** @var Model $model */
            $ableId = $model->getKey();
            foreach ($model->getNeedEncrypts() as $field => $instance) {
                if (in_array($field, $mediaAttributes)) {
                    if ($instance instanceof Collection) {
                        $instance->map(function($dt) use ($ableId) {
                            $dt->able_id = $ableId;
                            return $dt->save();
                        });
                        $keys = $instance->keys();

                        $encrypted = $model->getEncryptedByField($field);

                        if ($encrypted) {
                            $remove = $encrypted->map(function($dt) use($keys) {
                                return $keys->contains($dt->index) ? null : $dt->index;
                            })->filter(function($dt){ return (!is_null($dt) || trim($dt) !== ''); });

                            $remove->count() && $model->media_encrypts()
                                ->whereIn('index', $remove->toArray())
                                ->delete();
                        }

                    } else {

                        $instance->able_id = $ableId;
                        $instance->save();
                    }
                    $model->setEncrypted($field, $instance);
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
            if (! in_array($field, $fillAble) && in_array($cast, [MediaEncryptCast::class, MultiMediaEncryptCast::class])) {
                $this->mediaEncryptFields[] = $field;
                $cast === MultiMediaEncryptCast::class && $this->mediaEncryptFieldsTypeMulti[] = $field;
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
        if ($relation === 'media_encrypts' && $value->count()) {
            foreach ($value->groupBy('field') as $field => $encrypted) {
                $this->setEncrypted(
                    $field,
                    $this->isMultiMediaEncryptField($field)
                        ? $encrypted->mapWithKeys(function($dt){ return [$dt->index => $dt]; })
                        : $encrypted->first()
                );
            }
        }
        return parent::setRelation($relation, $value);
    }

    /**
     * Set a given attribute on the model.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return mixed
     */
    public function setAttribute($key, $value)
    {
        if (in_array($key, $this->mediaEncryptFields)) {
            $caster = $this->resolveCasterClass($key);
            $caster->set($this, $key, $value, $this->attributes);
            return null;
        }

        return parent::setAttribute($key, $value);
    }


    /**
     * Get an attribute from the model.
     *
     * @param  string  $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        if (in_array($key, $this->mediaEncryptFields)) {
            $caster = $this->resolveCasterClass($key);
            return $caster->get($this, $key, null, $this->attributes);
        }

        return parent::getAttribute($key);
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
     * @return bool
     */
    function isMultiMediaEncryptField($field): bool
    {
        return in_array($field, $this->mediaEncryptFieldsTypeMulti);
    }

    /**
     * @param MediaEncrypt|MediaEncrypt[] $mediaEncrypted
     * @return CanMediaEncryptInterface
     */
    public function setEncrypted($field, $mediaEncrypted): CanMediaEncryptInterface
    {
        $this->encryptedAttributes[$field] = $mediaEncrypted;
        return $this;
    }

    /**
     * get data encrypted by field
     * @param $field
     * @return MediaEncrypt|MediaEncrypt[]|null
     */
    public function getEncryptedByField($field)
    {
        $this->relationLoaded('media_encrypts') || $this->load('media_encrypts');
        return clone ($this->encryptedAttributes[$field] ?? null);
    }

    /**
     * @param $field
     * @return bool
     */
    public function hasNeedEncrypt($field): bool
    {
        return  isset($this->needEncryptAttributes[$field]);
    }
    /**
     * get need data to encrypt by field
     * @param $field
     * @return mixed|null
     */
    public function getNeedEncryptByField($field)
    {
        return $this->needEncryptAttributes[$field] ?? null;
    }


    /**
     * get all need data
     * @return array
     */
    public function getNeedEncrypts(): array
    {
        return $this->needEncryptAttributes;
    }

    /**
     * set data need encrypt into field
     * @param $key
     * @param $value
     * @return CanMediaEncryptInterface
     */
    public function setNeedEncrypt($field, $value): CanMediaEncryptInterface
    {
        $this->needEncryptAttributes[$field] = $value;
        return $this;
    }


    /**
     * @return MorphMany
     */
    function media_encrypts(): MorphMany
    {
        return $this->morphMany(MediaEncryptFacade::getModelClass(), 'able');
    }


//    /**
//     * @return array
//     */
//    function getAttributes()
//    {
//        foreach ($this->getMediaEncryptFields() as $field) {
//            unset($this->classCastCache[$field]);
//            unset($this->attributes[$field]);
//        }
//        return parent::getAttributes();
//    }

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
        if (MediaEncryptFacade::allowAppend()) {
            foreach ($this->getMediaEncryptFields() as $field) {
                if (!isset($result[$field]) && $this->hasAppended($field)) {
                    $result[$field] = MediaEncryptFacade::decryptData($this, $field);
                }
            }
        }

        return $result;
    }

}
