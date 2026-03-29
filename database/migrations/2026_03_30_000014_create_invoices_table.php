<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_code')->unique();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('service_session_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->index();
            $table->unsignedBigInteger('subtotal_rupiah')->default(0);
            $table->unsignedBigInteger('discount_rupiah')->default(0);
            $table->unsignedBigInteger('tax_rupiah')->default(0);
            $table->unsignedBigInteger('grand_total_rupiah')->default(0);
            $table->timestamp('issued_at');
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['customer_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
