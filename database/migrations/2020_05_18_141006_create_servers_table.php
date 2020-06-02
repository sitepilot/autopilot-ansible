<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('servers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('provider', 25);
            $table->string('provider_server_id')->nullable();
            $table->string('region', 25)->nullable();
            $table->string('size', 25)->nullable();
            $table->string('type', 25);
            $table->string('address')->nullable();
            $table->string('ipv6_address')->nullable();
            $table->string('private_address')->nullable();
            $table->integer('port');
            $table->string('user');
            $table->string('status');
            $table->string('timezone')->nullable();
            $table->string('admin_email')->nullable();
            $table->string('health_email')->nullable();
            $table->integer('php_post_max_size')->nullable();
            $table->integer('php_upload_max_filesize')->nullable();
            $table->integer('php_memory_limit')->nullable();
            $table->string('smtp_relay_host')->nullable();
            $table->string('smtp_relay_domain')->nullable();
            $table->string('smtp_relay_user')->nullable();
            $table->string('smtp_relay_password')->nullable();
            $table->text('public_key');
            $table->text('private_key');
            $table->string('admin_password')->nullable();
            $table->string('mysql_password')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        DB::update("ALTER TABLE `servers` AUTO_INCREMENT = " . env('APP_START_ID', 1) . ";");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('servers');
    }
}
