<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('referral_code')->nullable()->unique()->after('username');
            $table->foreignId('referrer_id')->nullable()->after('referral_code')->constrained('users')->nullOnDelete();
            $table->timestamp('referral_rewarded_at')->nullable()->after('referrer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['referrer_id']);
            $table->dropColumn(['referral_code', 'referrer_id', 'referral_rewarded_at']);
        });
    }
};
