<?php
/**
 * Created by HoanXuanMai
 * Email: hoanxuanmai@gmail.com
 * Date: 10/15/2023
 */


namespace HXM\MediaEncrypt\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use HXM\MediaEncrypt\Facades\MediaEncryptFacade;

abstract class AbstractMediaEncryptModel extends Model implements MediaEncryptInterface
{
    public $timestamps = false;
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['field', 'index', 'rows', 'file_name', 'mime_type', 'ext', 'size'];
    protected $with = ['contents'];
    protected $originContent;
    protected $needContent;
    protected $encryptedRows = [];
    protected $decrypted = false;
    protected $hasSetNeedContent = false;

    /**
     * @param $data
     * @return $this
     */
    function setOriginContent($data): MediaEncryptInterface
    {
        $this->originContent = $data;
        return $this;
    }

    /**
     * @return mixed
     */
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
        $this->hasSetNeedContent = true;
        $this->needContent = $needContent;
        return $this;
    }

    function able(): MorphTo
    {
        return $this->morphTo();
    }

    public function toArray()
    {
        return $this->getRawOriginal('mime_type') ? $this->toUrl() : $this->decrypt();
    }

    /**
     * @return mixed|string|null
     */
    function decrypt()
    {
        if (! $this->decrypted) {
            $originContent = null;
            if ($decryptString = $this->getRelationValue('contents')->implode('data')) {
                try {
                    $originContent = MediaEncryptFacade::getEncrypt()->decrypt($decryptString);
                } catch (\Exception $e) {
                    $originContent =  null;
                }
            }
            $this->originContent = $originContent;
            $this->decrypted = true;
        }

        if ($this->hasSetNeedContent) {
            return $this->needContent;
        }

        return $this->originContent;
    }

    function toUrl()
    {
        return route('media_encript.link', $this);
    }

    function contents(): HasMany
    {
        return $this->hasMany(MediaEncryptFacade::getModelContentClass())->orderBy('part');
    }


    static function booting()
    {
        static::saving(function(self $model) {
            $model->decrypt();

            if ($model->hasSetNeedContent) {
                $model->encryptContentBeforeSave();
                if (!$model->encryptedRows) {
                    $model->exists && $model->delete();
                    return false;
                }
            }

            if ($model->getKey() === null) {
                $model->setAttribute($model->getKeyName(), Str::uuid()->toString());
            }
        });

        static::saved(function(self $model) {
            $model->saveContentAfterSaved();
        });

        static::deleted(function(self $model) {
            $model->contents()->delete();
        });
    }

    protected function encryptContentBeforeSave()
    {
        $content = $this->getNeedContent();

        $dataSave = [
            'file_name'=> null,
            'mime_type'=> null,
            'ext'=> null,
            'size'=> null,
        ];

        if ($content instanceof UploadedFile) {
            $dataSave = [
                'file_name'=> $content->getClientOriginalName(),
                'mime_type'=> $content->getClientMimeType(),
                'ext'=> $content->getClientOriginalExtension(),
                'size'=> $content->getSize(),
            ];
            $content = base64_encode($content->getContent());
        }

        if ($content !== '' && $content !== null) {

            $stringSave = MediaEncryptFacade::getEncrypt()->encrypt($content);

            $rows = str_split($stringSave, MediaEncryptFacade::getRowLength());
            $this->encryptedRows = $rows;

        } else {
            $this->encryptedRows = [];
        }
        $dataSave['rows'] = count($this->encryptedRows);
        $this->fill($dataSave);
        $this->fireModelEvent('encryptContentAfter');
    }

    public static function encryptContentAfter($callback)
    {
        static::registerModelEvent('encryptContentAfter', $callback);
    }

    protected function saveContentAfterSaved()
    {
        if ($this->encryptedRows && $this->needContent != $this->originContent) {
            $this->wasRecentlyCreated || $this->contents()->delete();
            $this->contents()->insert(
                collect($this->encryptedRows)->mapWithKeys(function($data, $key) {
                    return [
                        $key => [
                            'id' => Str::orderedUuid(),
                            'media_encrypt_id' => $this->getKey(),
                            'part' => $key,
                            'data' => $data
                        ]
                    ];
                })->toArray()
            );
            $this->hasSetNeedContent = false;
            $this->encryptedRows = [];
        }
    }

}
