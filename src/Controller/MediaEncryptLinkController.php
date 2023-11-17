<?php
/**
 * Created by HoanXuanMai
 * Email: hoanxuanmai@gmail.com
 * Date: 11/16/2023
 */

namespace HXM\MediaEncrypt\Controller;

use HXM\MediaEncrypt\Models\MediaEncrypt;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\File\Stream;

class MediaEncryptLinkController
{
    public function __invoke(MediaEncrypt $mediaEncrypt)
    {
        $directory = storage_path('media_encrypt');
        File::isDirectory($directory) || File::makeDirectory($directory);
        $path = $directory.'/'.uniqid().'.media_encrypt';
        $base64String = $mediaEncrypt->decrypt();
        $base64String = preg_replace('/.*base64,/','', $base64String);
        File::put($path, base64_decode($base64String));
        return response()->file($path)->deleteFileAfterSend();
    }
}
