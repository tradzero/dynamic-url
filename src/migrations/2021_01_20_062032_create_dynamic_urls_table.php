<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDynamicUrlsTable extends Migration
{
    public function up()
    {
        Schema::create('dynamic_urls', function (Blueprint $table) {
            $table->increments('id');
            $table->string('url', 191)->comment('url');
            $table->boolean('enable')->default(true)->comment('enable status');
            $table->boolean('available')->default(true)->comment('available status');
            $table->timestamp('check_at')->nullable()->comment('last check at');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('dynamic_urls');
    }
}
