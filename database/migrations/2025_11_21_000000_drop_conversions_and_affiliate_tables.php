<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('conversions');
        Schema::dropIfExists('affiliate_integrations');
    }

    public function down(): void
    {
        // No-op: tables intentionally removed.
    }
};
