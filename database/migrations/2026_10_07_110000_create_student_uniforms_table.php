<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_uniforms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_profile_id')->constrained()->cascadeOnDelete();
            $table->string('size', 10)->nullable();
            $table->boolean('paid')->default(false);
            $table->date('paid_at')->nullable();
            $table->timestamps();
        });

        // Carry each student's existing single uniform over as their first entry.
        DB::table('student_profiles')
            ->where(fn ($q) => $q->whereNotNull('uniform_size')->where('uniform_size', '!=', '')->orWhere('uniform_paid', true))
            ->orderBy('id')
            ->chunkById(500, function ($profiles) {
                DB::table('student_uniforms')->insert($profiles->map(fn ($p) => [
                    'student_profile_id' => $p->id,
                    'size' => $p->uniform_size ? mb_substr($p->uniform_size, 0, 10) : null,
                    'paid' => (bool) $p->uniform_paid,
                    'paid_at' => $p->uniform_payment_date,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])->all());
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_uniforms');
    }
};
