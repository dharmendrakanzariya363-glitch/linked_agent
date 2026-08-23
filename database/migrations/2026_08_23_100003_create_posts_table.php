<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('campaign_topic_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('linkedin_account_id')->constrained()->restrictOnDelete();
            $table->unsignedBigInteger('current_version_id')->nullable();
            $table->string('status', 32)->default('generating');
            $table->date('scheduled_for');
            $table->timestamp('generated_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->string('linkedin_post_id')->nullable();
            $table->string('published_url')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();

            $table->unique(['campaign_id', 'scheduled_for']);
            $table->index(['user_id', 'status']);
            $table->index(['status', 'scheduled_for']);
            $table->index('current_version_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
