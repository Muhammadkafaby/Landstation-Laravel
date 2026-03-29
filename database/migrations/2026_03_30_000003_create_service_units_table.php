<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('zone')->nullable();
            $table->string('status')->default('available')->index();
            $table->unsignedInteger('capacity')->nullable();
            $table->boolean('is_bookable')->default(true)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->index(['service_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_units');
    }
};
