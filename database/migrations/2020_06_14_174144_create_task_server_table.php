<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaskServerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('server_task', function (Blueprint $table) {
            $table->unsignedBigInteger('server_id')->unsigned();
            $table->unsignedBigInteger('task_id')->unsigned();

            $table->foreign('server_id')
                ->references('id')->on('servers')
                ->onDelete('cascade');

            $table->foreign('task_id')
                ->references('id')->on('tasks')
                ->onDelete('cascade');
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['server_id']);
            $table->dropColumn('server_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('server_task');

        Schema::table('tasks', function (Blueprint $table) {
            $table->unsignedBigInteger('server_id')->after('id')->unsigned()->nullable();

            $table->foreign('server_id')
                ->references('id')->on('servers')
                ->onDelete('cascade');
        });
    }
}
