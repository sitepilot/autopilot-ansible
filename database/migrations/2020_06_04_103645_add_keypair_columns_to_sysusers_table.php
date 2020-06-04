<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddKeypairColumnsToSysusersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sysusers', function (Blueprint $table) {
            $table->text('public_key')->after('mysql_password');
            $table->text('private_key')->after('public_key');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sysusers', function (Blueprint $table) {
            $table->dropColumn('public_key');
            $table->dropColumn('private_key');
        });
    }
}
