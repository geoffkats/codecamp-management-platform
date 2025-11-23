<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_collaborators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('role', ['editor', 'viewer'])->default('editor'); // editor can modify, viewer can only view
            $table->timestamp('invited_at')->nullable();
            $table->foreignId('invited_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            $table->unique(['course_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_collaborators');
    }
};
