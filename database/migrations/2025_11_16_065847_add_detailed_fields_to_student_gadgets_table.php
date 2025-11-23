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
        Schema::table('student_gadgets', function (Blueprint $table) {
            $table->string('brand')->nullable()->after('device_type');
            $table->string('ram')->nullable()->after('serial_number');
            $table->string('storage')->nullable()->after('ram');
            $table->string('condition')->nullable()->after('storage');
            $table->text('accessories')->nullable()->after('condition');
            $table->string('photo_path')->nullable()->after('accessories');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_gadgets', function (Blueprint $table) {
            $table->dropColumn(['brand', 'ram', 'storage', 'condition', 'accessories', 'photo_path']);
        });
    }
};
