<?php
/**
 * Created by HoanXuanMai
 * Email: hoanxuanmai@gmail.com
 * Date: 10/15/2023
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MediaEncryptInstall extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('media_encrypts', function(Blueprint $table){
            $table->uuid('id')->primary();
            $table->morphs('able');
            $table->string('field')->nullable();
            $table->unsignedInteger('rows')->default(1);
            $table->string('file_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->string('ext')->nullable();
            $table->unsignedBigInteger('size')->nullable();
        });

        Schema::create('media_encrypt_contents', function(Blueprint $table){
            $table->uuid('id')->primary();
            $table->foreignUuid('media_encrypt_id')
                ->references('id')
                ->on('media_encrypts')
                ->cascadeOnDelete();
            $table->unsignedInteger('part');
            $table->longText('data');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('media_encrypt_contents');
        Schema::dropIfExists('media_encrypts');
    }
}
