<?php

use App\Models\TrackedLink;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clicks', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(TrackedLink::class)
                ->constrained()
                ->cascadeOnDelete();

            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('referrer')->nullable();
            // Plus tard : $table->string('country_code', 2)->nullable(); etc.

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clicks');
    }
};
