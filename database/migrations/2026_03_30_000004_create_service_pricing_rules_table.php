<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_pricing_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_unit_id')->nullable()->constrained('service_units')->nullOnDelete();
            $table->string('pricing_model')->index();
            $table->unsignedInteger('billing_interval_minutes')->nullable();
            $table->unsignedBigInteger('base_price_rupiah')->default(0);
            $table->unsignedBigInteger('price_per_interval_rupiah')->nullable();
            $table->unsignedBigInteger('minimum_charge_rupiah')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->unsignedSmallInteger('priority')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->index(['service_id', 'service_unit_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_pricing_rules');
    }
};
