<?php
/**
 * Created by HoanXuanMai
 * Email: hoanxuanmai@gmail.com
 * Date: 10/15/2023
 */

return [
    'key' => env('APP_KEY'),

    'cipher' => 'AES-256-CBC',

    'model' => \HXM\MediaEncrypt\Models\MediaEncrypt::class,

    'model_content' => \HXM\MediaEncrypt\Models\MediaEncryptContent::class,
    /*
     * the length of row data
     * LONGTEXT: 4e9
     * MEDIUMTEXT: 16e6
     * TEXT: 65e3
     * TINYTEXT: 255
     * */
    'row_length' => 4e9,

    /**
     * eager load data
     */
    'allow_eager_loading' => true,

    /**
     * allow append data into serialize
     */
    'allow_append' => true
];
