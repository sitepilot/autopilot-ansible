<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSiteMountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('site_mounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sysuser_id')->unsigned();
            $table->unsignedBigInteger('site_id')->unsigned();
            $table->string('status');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('sysuser_id')
                ->references('id')->on('sysusers')
                ->onDelete('cascade');

            $table->foreign('site_id')
                ->references('id')->on('sites')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('site_mounts');
    }
}
