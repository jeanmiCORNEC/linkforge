<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sources', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(User::class)
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('campaign_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('name');                     // "TikTok – Setup 1"
            $table->string('platform')->nullable();     // tiktok, youtube, instagram...
            $table->string('external_id')->nullable();  // id vidéo/post si un jour on le stocke
            $table->string('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sources');
    }
};
