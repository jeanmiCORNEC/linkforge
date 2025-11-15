<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('clicks', function (Blueprint $table) {
            // Métadonnées analytics
            $table->string('country', 5)->nullable()->after('referrer');
            $table->string('city')->nullable()->after('country');
            $table->string('device')->nullable()->after('city');   // mobile / desktop / tablet
            $table->string('browser')->nullable()->after('device');
            $table->string('os')->nullable()->after('browser');
            $table->string('visitor_hash')->nullable()->after('os');

            $table->index('visitor_hash');
        });
    }

    public function down(): void
    {
        Schema::table('clicks', function (Blueprint $table) {
            $table->dropIndex(['visitor_hash']);

            $table->dropColumn([
                'country',
                'city',
                'device',
                'browser',
                'os',
                'visitor_hash',
            ]);
        });
    }
};
