<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSitesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sysuser_id')->index();
            $table->string('name');
            $table->boolean('certificate');
            $table->integer('php_version');
            $table->string('status');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('sysuser_id')
                ->references('id')->on('sysusers')
                ->onDelete('cascade');
        });

        DB::update("ALTER TABLE `sites` AUTO_INCREMENT = " . env('APP_START_ID', 1) . ";");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sites');
    }
}
