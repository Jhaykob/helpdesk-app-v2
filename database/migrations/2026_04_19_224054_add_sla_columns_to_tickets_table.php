<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->timestamp('sla_deadline')->nullable()->after('status');
            $table->boolean('is_breaching_sla')->default(false)->after('sla_deadline');
        });
    }

    public function down()
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['sla_deadline', 'is_breaching_sla']);
        });
    }
};
