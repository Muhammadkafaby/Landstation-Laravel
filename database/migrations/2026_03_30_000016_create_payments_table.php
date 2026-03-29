<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->string('payment_method_code');
            $table->foreign('payment_method_code')->references('code')->on('payment_methods')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('status')->index();
            $table->unsignedBigInteger('amount_rupiah');
            $table->timestamp('paid_at')->nullable();
            $table->string('reference_number')->nullable();
            $table->foreignId('verified_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->json('payload_json')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
