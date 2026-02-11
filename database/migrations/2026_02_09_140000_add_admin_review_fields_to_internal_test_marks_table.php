<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('internal_test_marks', function (Blueprint $table) {
            $table->string('status')->default('pending_review')->after('passed');
            $table->boolean('is_locked')->default(true)->after('status');
            $table->foreignId('entered_by_teacher_id')->nullable()->after('is_locked')->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by_admin_id')->nullable()->after('entered_by_teacher_id')->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by_admin_id');
            $table->text('unlock_reason')->nullable()->after('reviewed_at');
            $table->foreignId('unlocked_by_admin_id')->nullable()->after('unlock_reason')->constrained('users')->nullOnDelete();
            $table->timestamp('unlocked_at')->nullable()->after('unlocked_by_admin_id');

            $table->index(['status', 'is_locked']);
        });
    }

    public function down(): void
    {
        Schema::table('internal_test_marks', function (Blueprint $table) {
            $table->dropIndex(['status', 'is_locked']);
            $table->dropConstrainedForeignId('entered_by_teacher_id');
            $table->dropConstrainedForeignId('reviewed_by_admin_id');
            $table->dropConstrainedForeignId('unlocked_by_admin_id');
            $table->dropColumn([
                'status',
                'is_locked',
                'reviewed_at',
                'unlock_reason',
                'unlocked_at',
            ]);
        });
    }
};
