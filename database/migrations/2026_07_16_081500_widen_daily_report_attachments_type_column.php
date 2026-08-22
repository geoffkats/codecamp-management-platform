<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('daily_report_attachments')) {
            return;
        }

        // Office Open XML MIME types exceed the original varchar(50)
        DB::statement('ALTER TABLE daily_report_attachments MODIFY type VARCHAR(255) NULL');
    }

    public function down(): void
    {
        if (! Schema::hasTable('daily_report_attachments')) {
            return;
        }

        DB::statement('ALTER TABLE daily_report_attachments MODIFY type VARCHAR(50) NULL');
    }
};
