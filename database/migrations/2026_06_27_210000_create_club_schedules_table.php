<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('club_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('code_club_id')->constrained('code_clubs')->cascadeOnDelete();
            $table->string('day_of_week', 20);
            $table->time('session_start')->nullable();
            $table->time('session_end')->nullable();
            $table->foreignId('instructor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['code_club_id', 'day_of_week']);
            $table->index('instructor_id');
        });

        $clubs = DB::table('code_clubs')
            ->whereNotNull('day_of_week')
            ->where('day_of_week', '!=', '')
            ->get(['id', 'day_of_week', 'session_start', 'session_end']);

        $now = now();

        foreach ($clubs as $club) {
            DB::table('club_schedules')->insert([
                'code_club_id' => $club->id,
                'day_of_week' => strtolower(trim($club->day_of_week)),
                'session_start' => $club->session_start,
                'session_end' => $club->session_end,
                'instructor_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('club_schedules');
    }
};
