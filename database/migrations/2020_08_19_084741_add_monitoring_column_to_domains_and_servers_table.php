<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMonitoringColumnToDomainsAndServersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->boolean('monitor')->default(true)->after('health_email');
        });

        Schema::table('domains', function (Blueprint $table) {
            $table->boolean('monitor')->default(true)->after('name');
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
            $table->dropColumn('monitor');
        });

        Schema::table('domains', function (Blueprint $table) {
            $table->dropColumn('monitor');
        });
    }
}
