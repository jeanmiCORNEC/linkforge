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
        if (! Schema::hasIndex('clicks', 'clicks_created_at_index')) {
            Schema::table('clicks', function (Blueprint $table) {
                $table->index('created_at');
            });
        }

        if (! Schema::hasIndex('clicks', 'clicks_tracked_link_id_created_at_index')) {
            Schema::table('clicks', function (Blueprint $table) {
                $table->index(['tracked_link_id', 'created_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clicks', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            $table->dropIndex(['tracked_link_id', 'created_at']);
        });
    }
};
