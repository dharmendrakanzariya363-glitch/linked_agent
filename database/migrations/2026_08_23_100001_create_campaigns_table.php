<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('linkedin_account_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('timezone', 64)->default('UTC');
            $table->time('daily_post_time');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('content_type', 32);
            $table->string('status', 32)->default('draft');
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['status', 'start_date', 'end_date']);
            $table->index('linkedin_account_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
