<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBackupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('backups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('server_id')->index();
            $table->nullableMorphs('backupable');
            $table->string('path');
            $table->string('status');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('server_id')
                ->references('id')->on('servers')
                ->onDelete('cascade');
        });

        DB::update("ALTER TABLE `backups` AUTO_INCREMENT = " . env('APP_START_ID', 1) . ";");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('backups');
    }
}
