<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_booking_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedInteger('slot_interval_minutes');
            $table->unsignedInteger('min_duration_minutes');
            $table->unsignedInteger('max_duration_minutes')->nullable();
            $table->unsignedInteger('lead_time_minutes')->default(0);
            $table->unsignedInteger('max_advance_days')->default(30);
            $table->boolean('requires_unit_assignment')->default(true);
            $table->boolean('walk_in_allowed')->default(true);
            $table->boolean('online_booking_allowed')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_booking_policies');
    }
};
