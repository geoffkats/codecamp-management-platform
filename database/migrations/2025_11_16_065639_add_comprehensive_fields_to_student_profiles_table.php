<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->string('nationality')->nullable()->after('gender');
            $table->json('parent_data')->nullable()->after('parent_guardian_contact');
            $table->string('uniform_size')->nullable()->after('uniform_payment_date');
            $table->boolean('tshirt_collected')->default(false)->after('uniform_size');
            $table->string('payment_receipt_path')->nullable()->after('tshirt_collected');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->dropColumn(['nationality', 'parent_data', 'uniform_size', 'tshirt_collected', 'payment_receipt_path']);
        });
    }
};
