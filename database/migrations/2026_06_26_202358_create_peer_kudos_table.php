<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('peer_kudos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('to_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('message', 100)->nullable();
            $table->date('given_on');
            $table->timestamps();

            // One kudos per sender per recipient per day
            $table->unique(['from_user_id', 'to_user_id', 'given_on'], 'kudos_daily_unique');
            // One kudos given by a person per day (across all recipients)
            $table->index(['from_user_id', 'given_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peer_kudos');
    }
};
