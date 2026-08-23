<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('linkedin_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('linkedin_id');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('headline')->nullable();
            $table->string('profile_url')->nullable();
            $table->string('avatar_url')->nullable();
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->timestamp('token_expires_at')->nullable();
            $table->json('scopes')->nullable();
            $table->timestamp('disconnected_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'linkedin_id']);
            $table->index(['user_id', 'disconnected_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('linkedin_accounts');
    }
};
