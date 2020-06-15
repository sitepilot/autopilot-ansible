<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBackupColumnsToServersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->string('backup_s3_key')->nullable()->after('mysql_password');
            $table->text('backup_s3_secret')->nullable()->after('backup_s3_key');
            $table->string('backup_s3_bucket')->nullable()->after('backup_s3_secret');
            $table->text('backup_password')->nullable()->after('backup_s3_bucket');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->dropColumn('backup_s3_key');
            $table->dropColumn('backup_s3_secret');
            $table->dropColumn('backup_s3_bucket');
            $table->dropColumn('backup_password');
        });
    }
}
