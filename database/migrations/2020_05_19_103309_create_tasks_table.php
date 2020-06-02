<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('server_id')->index();
            $table->nullableMorphs('provisionable');
            $table->string('name');
            $table->string('user', 25);
            $table->string('status', 25)->default('pending');
            $table->integer('exit_code')->nullable();
            $table->longText('playbook');
            $table->longText('output');
            $table->text('options');
            $table->text('vars');
            $table->timestamps();

            $table->foreign('server_id')
                ->references('id')->on('servers')
                ->onDelete('cascade');
        });

        DB::update("ALTER TABLE `tasks` AUTO_INCREMENT = " . env('APP_START_ID', 1) . ";");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tasks');
    }
}
