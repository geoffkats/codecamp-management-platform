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
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('text'); // text, image, boolean, json
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Insert default settings
        DB::table('system_settings')->insert([
            [
                'key' => 'app_name',
                'value' => 'Code Academy Uganda',
                'type' => 'text',
                'description' => 'The name of your application',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'app_short_name',
                'value' => 'CAU',
                'type' => 'text',
                'description' => 'Short name or abbreviation',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'app_tagline',
                'value' => 'E-Learning Platform',
                'type' => 'text',
                'description' => 'Tagline or subtitle',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'favicon',
                'value' => null,
                'type' => 'image',
                'description' => 'Favicon image (ICO, PNG, or SVG)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'logo',
                'value' => null,
                'type' => 'image',
                'description' => 'Main logo image',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'logo_dark',
                'value' => null,
                'type' => 'image',
                'description' => 'Logo for dark mode',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'contact_email',
                'value' => 'info@codeacademy.ug',
                'type' => 'text',
                'description' => 'Contact email address',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'contact_phone',
                'value' => '+256 784 781926',
                'type' => 'text',
                'description' => 'Contact phone number',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'contact_address',
                'value' => 'Mpererwe, Mugalu Zone, Kampala',
                'type' => 'text',
                'description' => 'Physical address',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
