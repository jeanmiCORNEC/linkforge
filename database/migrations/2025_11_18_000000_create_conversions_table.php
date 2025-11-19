<?php

use App\Models\Campaign;
use App\Models\Link;
use App\Models\Source;
use App\Models\TrackedLink;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversions', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(User::class)
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignIdFor(TrackedLink::class)
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignIdFor(Link::class)
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignIdFor(Source::class)
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignIdFor(Campaign::class)
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('order_id')->nullable();
            $table->string('status')->default('pending');
            $table->string('currency', 3)->default('EUR');
            $table->decimal('revenue', 12, 2)->default(0);
            $table->decimal('commission', 12, 2)->default(0);
            $table->string('visitor_hash')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index(['tracked_link_id', 'status']);
            $table->index(['source_id', 'status']);
            $table->index(['campaign_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversions');
    }
};
