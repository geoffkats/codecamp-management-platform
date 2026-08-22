<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('student_profiles')) {
            return;
        }

        DB::table('student_profiles')
            ->where('student_category', 'school_club')
            ->where(function ($q) {
                $q->whereNull('program_type')
                    ->orWhere('program_type', 'codecamp');
            })
            ->update(['program_type' => 'codeclub']);

        if (Schema::hasTable('users')) {
            $userIds = DB::table('student_profiles')
                ->where('program_type', 'codeclub')
                ->pluck('user_id');

            if ($userIds->isNotEmpty()) {
                DB::table('users')
                    ->whereIn('id', $userIds)
                    ->where('student_type', 'codecamp')
                    ->update(['student_type' => 'codeclub']);
            }
        }
    }

    public function down(): void
    {
        DB::table('student_profiles')
            ->where('student_category', 'school_club')
            ->where('program_type', 'codeclub')
            ->update(['program_type' => 'codecamp']);
    }
};
