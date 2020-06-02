<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSysusersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sysusers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('server_id')->index();
            $table->string('name');
            $table->text('full_name')->nullable();
            $table->text('email')->nullable();
            $table->boolean('isolated');
            $table->string('password');
            $table->string('mysql_password');
            $table->string('status');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('server_id')
                ->references('id')->on('servers')
                ->onDelete('cascade');
        });

        DB::update("ALTER TABLE `sysusers` AUTO_INCREMENT = " . env('APP_START_ID', 1) . ";");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sysusers');
    }
}
