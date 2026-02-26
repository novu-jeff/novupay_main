<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $conn = 'morong';

        if (!Schema::connection($conn)->hasTable('morong_bills')) {
            Schema::connection($conn)->create('morong_bills', function (Blueprint $table) {
                $table->id();
                $table->string('reference_no')->unique();
                $table->string('account_no');
                $table->decimal('amount', 10, 2)->default(0);
                $table->integer('present_reading')->nullable();
                $table->integer('previous_reading')->nullable();
                $table->integer('consumption')->nullable();
                $table->boolean('is_high_consumption')->default(false);
                $table->string('status')->default('initiated');
                $table->string('hitpay_reference')->nullable()->index();
                $table->string('hitpay_url')->nullable();
                $table->json('payload')->nullable();
                $table->timestamp('initiated_at')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::connection($conn)->hasTable('morong_hitpay_transactions')) {
            Schema::connection($conn)->create('morong_hitpay_transactions', function (Blueprint $table) {
                $table->id();
                $table->string('reference_no')->index();
                $table->string('hitpay_id')->nullable()->index();
                $table->string('payment_request_id')->nullable();
                $table->string('payment_url')->nullable();
                $table->decimal('amount', 10, 2)->default(0.00);
                $table->decimal('convenience_fee', 10, 2)->default(0.00);
                $table->decimal('final_amount', 10, 2)->default(0.00);
                $table->string('status')->default('initiated');
                $table->json('request_payload')->nullable();
                $table->json('response_payload')->nullable();
                $table->timestamp('initiated_at')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::connection('morong')->dropIfExists('morong_hitpay_transactions');
        Schema::connection('morong')->dropIfExists('morong_bills');
    }
};
