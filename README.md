<p align="center">
    <a href="https://laravel.com"><img alt="Laravel 7.x/8.x/9.x/10.x" src="https://img.shields.io/badge/Laravel-7.x/8.x/9.x/10.x-red.svg"></a>
    <a href="https://www.paypal.me/MaiXuanHoan"><img alt="Donate" src="https://img.shields.io/badge/Donate-%3C3-red"></a>
</p>

# Media Encrypt

**Media Encrypt** is a powerful PHP package designed for encrypting sensitive data before storing it in a database. This package provides a secure solution to protect your critical data from unauthorized access.

## Installation
You can install this package via Composer with the following command:
```bash
  composer require hxm/media-encrypt
```    
After successfully installing the **Media Encrypt** package, you need to perform some configuration steps to start using it in your project.

1. **Publish Config File**: You can publish the configuration file to customize the settings according to your specific requirements by running the following command:
    ```bash
    php artisan vendor:publish --provider="HXM\MediaEncrypt\MediaEncryptServiceProvider" --tag="media-encrypt-config"
    ```
    The configuration file will be published to `config/media-encrypt.php`.
2. **Migrate Database**: After the configuration is done, you need to run the migrate command to create the necessary tables in the database:
    ```bash
   php artisan migrate
    ```
## Usage
To use the **Media Encrypt** package in your Laravel project, you can refer to the following example code:

1. **Inherit** `CanMediaEncryptInterface` in your Model, then use the `HasMediaEncrypt` trait in it.
    ```php
    use Illuminate\Database\Eloquent\Model;
    use HXM\MediaEncrypt\Contracts\CanMediaEncryptInterface;
    use HXM\MediaEncrypt\Traits\HasMediaEncrypt;
    
    class OnlineRequest extends Model implements CanMediaEncryptInterface
    {
        use HasMediaEncrypt;
        #...
    }
    
    ```
2. **Define**: To define the `field` you want to use with the `Media Encrypt` data, you just need to add it to the `$casts` attribute in the model itself. Here is the complete code for your `Model`:

    ```php
    use Illuminate\Database\Eloquent\Model;
    use HXM\MediaEncrypt\Contracts\CanMediaEncryptInterface;
    use HXM\MediaEncrypt\Traits\HasMediaEncrypt;
    use HXM\MediaEncrypt\Casts\MediaEncryptCast;
    use HXM\MediaEncrypt\Casts\MultiMediaEncryptCast;
    
    class DemoModel extends Model implements CanMediaEncryptInterface
    {
    use HasMediaEncrypt;
    
        protected $appends = ['baseData', 'media', 'media_multi'];
   
        protected $casts = [
            #...
            'baseData' => MediaEncryptCast::class,
            'media' => MediaEncryptCast::class,
            'media_multi' => MultiMediaEncryptCast::class,
        ];
        #...
    }


    ```     
   * The `MediaEncryptCast` will encode the data into one block.
   * the `MultiMediaEncryptCast` will understand that your data is stored in the form of an `Array`, with each sub-element being encrypted separately. If your data is a `Media file` data array, you must use this cast
3. **Encrypt and store data**:
    ```php
    $model = Demo::first();
   
    $model->baseData = [
        'row' => 1, 
        'column' => 2, 
   ];
   
    $model->media = request()->file('file');
   
    $model->media_multi = [
        'file1' => request()->file('file1'),
        'file2' => request()->get('file2'),
    ];
    $model->save();
    ```
   
4. **Access the encrypted data**:
    ```php
    $model = Demo::first();
   
   dump($model->baseData);
   #@return: instance  HXM\MediaEncrypt\Models\MediaEncrypt
   
   dump($model->baseData->toAray());
    #@return: array:2 [
    #   'row' => 1
    #   'column' => 2
    #   ]
   
    dump($model->media->toUrl());
    #@return: https://baseUrl/encrypt_media/4f03a151-14a9-4234...
    dump($model->media_multi->decrypt());
    #@return: array:2 [
    #   'file1' => https://baseUrl/encrypt_media/4f03a151-14a9-4234...
    #   'file2' => data:image/jpg;base64,/9j/4QAYRXh.........
    #   ]
    ```
   * When accessing the property, you will receive an instance of `HXM\MediaEncrypt\Models\MediaEncrypt`
   * The `Media file` data will be encrypted and stored in the database, so after decryption, we will return it to the base64 code of the content.
   * Raw data such as text, array will be encrypted and returned unchanged.
5. **Append**: If you want to `append` the decrypted data, simply add the fields you want to the `$appends` attribute of the `Model`:
    ```php
    protected $appends = ['baseData', 'media', 'media_multi'];
    ```
## Contribution
If you encounter any issues or have any suggestions for improvements, please open an issue on GitHub <a href="https://github.com/hoanxuanmai/media-encrypt/issues">here</a>. We welcome contributions from the community.

## License
The **Media Encrypt** package is distributed under the <a href="https://opensource.org/licenses/MIT">MIT License</a>. Please refer to the LICENSE file for detailed information about this license.

## Contact
If you have any questions, please feel free to contact us via email at <a href="mailto:hoanxuanmai@gmail.com">hoanxuanmai@gmail.com</a>. We will try to respond as soon as possible.


<h1 align="center"><a href="https://github.com/hoanxuanmai">HoanXuanMai</a></h1>

We hope that the **Media Encrypt** package will help you secure your important data effectively. Thank you for using our package!
