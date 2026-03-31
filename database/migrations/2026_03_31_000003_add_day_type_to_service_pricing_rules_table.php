<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_pricing_rules', function (Blueprint $table) {
            $table->string('day_type')->default('weekday')->after('service_unit_id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('service_pricing_rules', function (Blueprint $table) {
            $table->dropColumn('day_type');
        });
    }
};
