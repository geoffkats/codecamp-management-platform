<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registration_requests', function (Blueprint $table) {
            $table->string('public_token', 64)->nullable()->unique()->after('id');
            $table->string('payment_status')->nullable()->after('status');
        });

        Schema::create('registration_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_request_id')->constrained()->cascadeOnDelete();
            $table->string('gateway')->default('pesapal');
            $table->string('merchant_reference', 50)->unique();
            $table->string('order_tracking_id')->nullable()->index();
            $table->unsignedInteger('amount');
            $table->string('currency', 3)->default('UGX');
            $table->string('status')->default('pending');
            $table->string('payment_method')->nullable();
            $table->string('confirmation_code')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->json('gateway_response')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registration_payments');

        Schema::table('registration_requests', function (Blueprint $table) {
            $table->dropUnique(['public_token']);
            $table->dropColumn(['public_token', 'payment_status']);
        });
    }
};
