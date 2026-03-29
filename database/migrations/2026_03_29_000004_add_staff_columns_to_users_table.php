<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->foreignId('role_id')->nullable()->after('remember_token')->constrained('roles')->nullOnDelete();
            $table->string('status')->default(User::STATUS_ACTIVE)->after('role_id');
            $table->timestamp('last_login_at')->nullable()->after('status');

            $table->index(['role_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role_id', 'status']);
            $table->dropConstrainedForeignId('role_id');
            $table->dropColumn(['phone', 'status', 'last_login_at']);
        });
    }
};
