<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action'); // 'create', 'update', 'delete', 'restore'
            $table->string('model_type'); // 'Course', 'Lesson', 'Assessment', etc.
            $table->unsignedBigInteger('model_id');
            $table->string('model_name')->nullable(); // Title/name of the model
            $table->longText('old_values')->nullable(); // JSON of previous values
            $table->longText('new_values')->nullable(); // JSON of new values
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->index(['model_type', 'model_id']);
            $table->index(['user_id', 'created_at']);
            $table->index('action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
