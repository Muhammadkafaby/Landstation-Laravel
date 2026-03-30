<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->timestamp('hold_expires_at')->nullable()->after('status');
            $table->timestamp('confirmed_at')->nullable()->after('hold_expires_at');
            $table->timestamp('expired_at')->nullable()->after('confirmed_at');
            $table->string('status_reason')->nullable()->after('expired_at');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'hold_expires_at',
                'confirmed_at',
                'expired_at',
                'status_reason',
            ]);
        });
    }
};
