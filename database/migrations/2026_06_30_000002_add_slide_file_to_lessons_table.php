<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->string('slide_file_path')->nullable()->after('attachments');
        });

        // Make content nullable — lessons driven by slide files don't need body text
        DB::statement('ALTER TABLE lessons MODIFY COLUMN content LONGTEXT NULL');
    }

    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropColumn('slide_file_path');
        });

        DB::statement('ALTER TABLE lessons MODIFY COLUMN content LONGTEXT NOT NULL');
    }
};
