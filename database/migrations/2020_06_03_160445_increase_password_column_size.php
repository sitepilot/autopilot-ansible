<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class IncreasePasswordColumnSize extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('servers', function ($table) {
            $table->text('admin_password')->change();
            $table->text('mysql_password')->change();
            $table->text('smtp_relay_password')->change();
        });

        Schema::table('sysusers', function ($table) {
            $table->text('password')->change();
            $table->text('mysql_password')->change();
        });

        Schema::table('databases', function ($table) {
            $table->text('password')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('servers', function ($table) {
            $table->string('admin_password')->change();
            $table->string('mysql_password')->change();
            $table->string('smtp_relay_password')->change();
        });

        Schema::table('sysusers', function ($table) {
            $table->string('password')->change();
            $table->string('mysql_password')->change();
        });

        Schema::table('databases', function ($table) {
            $table->string('password')->change();
        });
    }
}
