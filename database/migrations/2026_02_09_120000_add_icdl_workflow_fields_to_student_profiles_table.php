<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('student_profiles', 'payment_amount')) {
                $table->decimal('payment_amount', 10, 2)->nullable()->after('payment_receipt_path');
            }
            if (!Schema::hasColumn('student_profiles', 'payment_reference')) {
                $table->string('payment_reference')->nullable()->after('payment_amount');
            }
            if (!Schema::hasColumn('student_profiles', 'payment_status')) {
                $table->string('payment_status', 24)->default('pending')->after('payment_reference');
            }
            if (!Schema::hasColumn('student_profiles', 'payment_submitted_at')) {
                $table->dateTime('payment_submitted_at')->nullable()->after('payment_status');
            }
            if (!Schema::hasColumn('student_profiles', 'payment_verified_at')) {
                $table->dateTime('payment_verified_at')->nullable()->after('payment_submitted_at');
            }

            if (!Schema::hasColumn('student_profiles', 'icdl_test_score')) {
                $table->decimal('icdl_test_score', 5, 2)->nullable()->after('payment_verified_at');
            }
            if (!Schema::hasColumn('student_profiles', 'icdl_test_status')) {
                $table->string('icdl_test_status', 24)->default('not_submitted')->after('icdl_test_score');
            }
            if (!Schema::hasColumn('student_profiles', 'icdl_test_submitted_at')) {
                $table->dateTime('icdl_test_submitted_at')->nullable()->after('icdl_test_status');
            }
            if (!Schema::hasColumn('student_profiles', 'icdl_test_reviewed_at')) {
                $table->dateTime('icdl_test_reviewed_at')->nullable()->after('icdl_test_submitted_at');
            }

            if (!Schema::hasColumn('student_profiles', 'exam_request_status')) {
                $table->string('exam_request_status', 24)->default('not_requested')->after('icdl_test_reviewed_at');
            }
            if (!Schema::hasColumn('student_profiles', 'exam_requested_at')) {
                $table->dateTime('exam_requested_at')->nullable()->after('exam_request_status');
            }
            if (!Schema::hasColumn('student_profiles', 'exam_approved_at')) {
                $table->dateTime('exam_approved_at')->nullable()->after('exam_requested_at');
            }
            if (!Schema::hasColumn('student_profiles', 'exam_payment_status')) {
                $table->string('exam_payment_status', 24)->default('not_submitted')->after('exam_approved_at');
            }
            if (!Schema::hasColumn('student_profiles', 'exam_payment_submitted_at')) {
                $table->dateTime('exam_payment_submitted_at')->nullable()->after('exam_payment_status');
            }
            if (!Schema::hasColumn('student_profiles', 'exam_payment_verified_at')) {
                $table->dateTime('exam_payment_verified_at')->nullable()->after('exam_payment_submitted_at');
            }
            if (!Schema::hasColumn('student_profiles', 'exam_scheduled_for')) {
                $table->dateTime('exam_scheduled_for')->nullable()->after('exam_payment_verified_at');
            }
        });

        $driver = DB::getDriverName();
        if ($driver === 'mysql' || $driver === 'pgsql') {
            DB::statement("ALTER TABLE student_profiles ADD CONSTRAINT student_profiles_payment_status_check CHECK (payment_status IN ('pending','verified','rejected'))");
            DB::statement("ALTER TABLE student_profiles ADD CONSTRAINT student_profiles_icdl_test_status_check CHECK (icdl_test_status IN ('not_submitted','pending_review','approved','rejected'))");
            DB::statement("ALTER TABLE student_profiles ADD CONSTRAINT student_profiles_exam_request_status_check CHECK (exam_request_status IN ('not_requested','requested','approved','declined'))");
            DB::statement("ALTER TABLE student_profiles ADD CONSTRAINT student_profiles_exam_payment_status_check CHECK (exam_payment_status IN ('not_submitted','submitted','verified'))");
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE student_profiles DROP CHECK student_profiles_payment_status_check');
            DB::statement('ALTER TABLE student_profiles DROP CHECK student_profiles_icdl_test_status_check');
            DB::statement('ALTER TABLE student_profiles DROP CHECK student_profiles_exam_request_status_check');
            DB::statement('ALTER TABLE student_profiles DROP CHECK student_profiles_exam_payment_status_check');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE student_profiles DROP CONSTRAINT student_profiles_payment_status_check');
            DB::statement('ALTER TABLE student_profiles DROP CONSTRAINT student_profiles_icdl_test_status_check');
            DB::statement('ALTER TABLE student_profiles DROP CONSTRAINT student_profiles_exam_request_status_check');
            DB::statement('ALTER TABLE student_profiles DROP CONSTRAINT student_profiles_exam_payment_status_check');
        }

        Schema::table('student_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('student_profiles', 'payment_amount')) {
                $table->dropColumn('payment_amount');
            }
            if (Schema::hasColumn('student_profiles', 'payment_reference')) {
                $table->dropColumn('payment_reference');
            }
            if (Schema::hasColumn('student_profiles', 'payment_status')) {
                $table->dropColumn('payment_status');
            }
            if (Schema::hasColumn('student_profiles', 'payment_submitted_at')) {
                $table->dropColumn('payment_submitted_at');
            }
            if (Schema::hasColumn('student_profiles', 'payment_verified_at')) {
                $table->dropColumn('payment_verified_at');
            }
            if (Schema::hasColumn('student_profiles', 'icdl_test_score')) {
                $table->dropColumn('icdl_test_score');
            }
            if (Schema::hasColumn('student_profiles', 'icdl_test_status')) {
                $table->dropColumn('icdl_test_status');
            }
            if (Schema::hasColumn('student_profiles', 'icdl_test_submitted_at')) {
                $table->dropColumn('icdl_test_submitted_at');
            }
            if (Schema::hasColumn('student_profiles', 'icdl_test_reviewed_at')) {
                $table->dropColumn('icdl_test_reviewed_at');
            }
            if (Schema::hasColumn('student_profiles', 'exam_request_status')) {
                $table->dropColumn('exam_request_status');
            }
            if (Schema::hasColumn('student_profiles', 'exam_requested_at')) {
                $table->dropColumn('exam_requested_at');
            }
            if (Schema::hasColumn('student_profiles', 'exam_approved_at')) {
                $table->dropColumn('exam_approved_at');
            }
            if (Schema::hasColumn('student_profiles', 'exam_payment_status')) {
                $table->dropColumn('exam_payment_status');
            }
            if (Schema::hasColumn('student_profiles', 'exam_payment_submitted_at')) {
                $table->dropColumn('exam_payment_submitted_at');
            }
            if (Schema::hasColumn('student_profiles', 'exam_payment_verified_at')) {
                $table->dropColumn('exam_payment_verified_at');
            }
            if (Schema::hasColumn('student_profiles', 'exam_scheduled_for')) {
                $table->dropColumn('exam_scheduled_for');
            }
        });
    }
};
