<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('course_enrollments') && ! Schema::hasColumn('course_enrollments', 'club_id')) {
            Schema::table('course_enrollments', function (Blueprint $table) {
                $table->unsignedBigInteger('club_id')->nullable()->after('camp_id');
            });

            if (Schema::hasTable('code_clubs')) {
                try {
                    Schema::table('course_enrollments', function (Blueprint $table) {
                        $table->foreign('club_id')->references('id')->on('code_clubs')->nullOnDelete();
                    });
                } catch (\Throwable) {
                    //
                }
            }
        }

        if (Schema::hasTable('student_attendances') && ! Schema::hasColumn('student_attendances', 'club_id')) {
            Schema::table('student_attendances', function (Blueprint $table) {
                $table->unsignedBigInteger('club_id')->nullable()->after('camp_id');
            });

            if (Schema::hasTable('code_clubs')) {
                try {
                    Schema::table('student_attendances', function (Blueprint $table) {
                        $table->foreign('club_id')->references('id')->on('code_clubs')->nullOnDelete();
                    });
                } catch (\Throwable) {
                    //
                }
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('course_enrollments', 'club_id')) {
            Schema::table('course_enrollments', function (Blueprint $table) {
                try {
                    $table->dropForeign(['club_id']);
                } catch (\Throwable) {
                    //
                }
                $table->dropColumn('club_id');
            });
        }

        if (Schema::hasColumn('student_attendances', 'club_id')) {
            Schema::table('student_attendances', function (Blueprint $table) {
                try {
                    $table->dropForeign(['club_id']);
                } catch (\Throwable) {
                    //
                }
                $table->dropColumn('club_id');
            });
        }
    }
};
