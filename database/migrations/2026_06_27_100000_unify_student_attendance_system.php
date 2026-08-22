<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('student_attendances', 'camp_id')) {
            Schema::table('student_attendances', function (Blueprint $table) {
                $table->unsignedBigInteger('camp_id')->nullable()->after('course_id');
            });
        }

        Schema::table('student_attendances', function (Blueprint $table) {
            if (! Schema::hasColumn('student_attendances', 'source')) {
                $table->string('source', 20)->default('manual')->after('status');
            }
            if (! Schema::hasColumn('student_attendances', 'code_used')) {
                $table->string('code_used', 20)->nullable()->after('source');
            }
            if (! Schema::hasColumn('student_attendances', 'recorded_at')) {
                $table->timestamp('recorded_at')->nullable()->after('recorded_by');
            }
        });

        if (Schema::hasTable('code_camps')) {
            try {
                Schema::table('student_attendances', function (Blueprint $table) {
                    $table->foreign('camp_id')->references('id')->on('code_camps')->nullOnDelete();
                });
            } catch (\Throwable) {
                // FK may already exist or engine mismatch — column still usable without constraint.
            }
        }

        if (Schema::hasTable('attendance_logs')) {
            $logs = DB::table('attendance_logs')->orderBy('id')->get();

            foreach ($logs as $log) {
                $profile = DB::table('student_profiles')
                    ->where('id', $log->student_profile_id)
                    ->first();

                $campId = null;
                if ($profile && Schema::hasTable('camp_enrollments')) {
                    $campId = DB::table('camp_enrollments')
                        ->where('student_id', $profile->user_id)
                        ->where('status', 'active')
                        ->orderByDesc('enrolled_at')
                        ->value('camp_id');
                }

                $existing = DB::table('student_attendances')
                    ->where('student_profile_id', $log->student_profile_id)
                    ->where('attendance_date', $log->attendance_date)
                    ->whereNull('course_id')
                    ->first();

                $payload = [
                    'status'      => $log->check_in_time ? 'present' : 'absent',
                    'clock_in'    => $log->check_in_time,
                    'clock_out'   => $log->check_out_time,
                    'code_used'   => $log->code_used,
                    'source'      => ($log->code_used ?? '') === 'MANUAL' ? 'bulk' : 'check_in',
                    'camp_id'     => $campId,
                    'recorded_at' => $log->created_at ?? now(),
                    'updated_at'  => now(),
                ];

                if ($existing) {
                    if (! $existing->clock_in && $log->check_in_time) {
                        DB::table('student_attendances')->where('id', $existing->id)->update($payload);
                    }
                } else {
                    DB::table('student_attendances')->insert(array_merge($payload, [
                        'student_profile_id' => $log->student_profile_id,
                        'attendance_date'    => $log->attendance_date,
                        'course_id'          => null,
                        'created_at'         => $log->created_at ?? now(),
                    ]));
                }
            }
        }
    }

    public function down(): void
    {
        Schema::table('student_attendances', function (Blueprint $table) {
            if (Schema::hasColumn('student_attendances', 'camp_id')) {
                try {
                    $table->dropForeign(['camp_id']);
                } catch (\Throwable) {
                    //
                }
                $table->dropColumn('camp_id');
            }
            foreach (['source', 'code_used', 'recorded_at'] as $col) {
                if (Schema::hasColumn('student_attendances', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
