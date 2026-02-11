<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assessment_attempts', function (Blueprint $table) {
            if (!Schema::hasColumn('assessment_attempts', 'school_id')) {
                $table->unsignedBigInteger('school_id')
                    ->nullable()
                    ->after('user_id');
            }

            if (!Schema::hasColumn('assessment_attempts', 'teacher_id')) {
                $table->unsignedBigInteger('teacher_id')
                    ->nullable()
                    ->after('school_id');
            }

            if (!Schema::hasColumn('assessment_attempts', 'student_type')) {
                $table->string('student_type', 20)
                    ->default('codecamp')
                    ->after('teacher_id');
            }

            if (!Schema::hasColumn('assessment_attempts', 'auto_scored')) {
                $table->boolean('auto_scored')
                    ->default(false)
                    ->after('student_type');
            }

            if (!Schema::hasColumn('assessment_attempts', 'is_locked')) {
                $table->boolean('is_locked')
                    ->default(false)
                    ->after('auto_scored');
            }

        });

        if (Schema::hasTable('users')
            && Schema::hasColumn('assessment_attempts', 'teacher_id')
            && !$this->foreignKeyExists('assessment_attempts', 'assessment_attempts_teacher_id_foreign')) {
            Schema::table('assessment_attempts', function (Blueprint $table) {
                $table->foreign('teacher_id')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();
            });
        }

        if (Schema::hasColumn('assessment_attempts', 'school_id')
            && Schema::hasColumn('assessment_attempts', 'student_type')
            && !$this->indexExists('assessment_attempts', 'idx_attempts_school_student_type')) {
            Schema::table('assessment_attempts', function (Blueprint $table) {
                $table->index(['school_id', 'student_type'], 'idx_attempts_school_student_type');
            });
        }

        if (Schema::hasColumn('assessment_attempts', 'teacher_id')
            && !$this->indexExists('assessment_attempts', 'idx_attempts_teacher')) {
            Schema::table('assessment_attempts', function (Blueprint $table) {
                $table->index('teacher_id', 'idx_attempts_teacher');
            });
        }

        if (Schema::hasColumn('assessment_attempts', 'student_type')
            && !$this->indexExists('assessment_attempts', 'idx_attempts_student_type')) {
            Schema::table('assessment_attempts', function (Blueprint $table) {
                $table->index('student_type', 'idx_attempts_student_type');
            });
        }

        if (Schema::hasTable('users') && Schema::hasColumn('assessment_attempts', 'student_type')) {
            DB::table('assessment_attempts')
                ->join('users', 'assessment_attempts.user_id', '=', 'users.id')
                ->update([
                    'assessment_attempts.student_type' => DB::raw("COALESCE(users.student_type, 'codecamp')")
                ]);
        }

        if (Schema::hasTable('student_profiles') && Schema::hasColumn('assessment_attempts', 'school_id')) {
            DB::table('assessment_attempts')
                ->join('student_profiles', 'assessment_attempts.user_id', '=', 'student_profiles.user_id')
                ->update([
                    'assessment_attempts.school_id' => DB::raw('student_profiles.school_id')
                ]);
        }

        if (Schema::hasTable('assessments') && Schema::hasColumn('assessment_attempts', 'auto_scored')) {
            DB::table('assessment_attempts')
                ->join('assessments', 'assessment_attempts.assessment_id', '=', 'assessments.id')
                ->update([
                    'assessment_attempts.auto_scored' => DB::raw("CASE WHEN assessments.assessment_type = 'assignment' THEN 0 ELSE 1 END")
                ]);
        }
    }

    private function foreignKeyExists(string $table, string $constraint): bool
    {
        $connection = DB::connection()->getDatabaseName();

        $result = DB::select(
            'SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_NAME = ?',
            [$connection, $table, $constraint]
        );

        return !empty($result);
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $result = DB::select('SHOW INDEX FROM ' . $table . ' WHERE Key_name = ?', [$indexName]);

        return !empty($result);
    }

    public function down(): void
    {
        Schema::table('assessment_attempts', function (Blueprint $table) {
            if (Schema::hasColumn('assessment_attempts', 'teacher_id')) {
                if ($this->foreignKeyExists('assessment_attempts', 'assessment_attempts_teacher_id_foreign')) {
                    $table->dropForeign('assessment_attempts_teacher_id_foreign');
                }
                $table->dropColumn('teacher_id');
            }

            if (Schema::hasColumn('assessment_attempts', 'school_id')) {
                if ($this->foreignKeyExists('assessment_attempts', 'assessment_attempts_school_id_foreign')) {
                    $table->dropForeign('assessment_attempts_school_id_foreign');
                }
                $table->dropColumn('school_id');
            }

            if (Schema::hasColumn('assessment_attempts', 'auto_scored')) {
                $table->dropColumn('auto_scored');
            }

            if (Schema::hasColumn('assessment_attempts', 'is_locked')) {
                $table->dropColumn('is_locked');
            }

            if (Schema::hasColumn('assessment_attempts', 'student_type')) {
                $table->dropColumn('student_type');
            }
        });
    }
};
