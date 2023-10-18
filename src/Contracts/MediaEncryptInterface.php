<?php
/**
 * Created by HoanXuanMai
 * Email: hoanxuanmai@gmail.com
 * Date: 10/18/2023
 */

namespace HXM\MediaEncrypt\Contracts;

use HXM\MediaEncrypt\Models\MediaEncrypt;

interface MediaEncryptInterface
{
    /**
     * @param mixed $data
     * @return $this
     */
    function setOriginContent($data);

    function getOriginContent();
    /**
     * @param mixed $data
     * @return $this
     */
    function setNeedContent($data) :self;

    function getNeedContent();

    function able();

    /**
     * @return mixed|string|null
     */
    function decrypt();

    function contents(): \Illuminate\Database\Eloquent\Relations\HasMany;
}
