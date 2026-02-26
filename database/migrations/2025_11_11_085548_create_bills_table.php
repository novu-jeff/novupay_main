<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no')->unique();
            $table->string('account_no')->nullable();
            $table->string('payor')->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('status')->default('pending'); // pending, paid, failed
            $table->string('payment_type')->nullable(); // cash, online, etc.

            // HitPay related
            $table->string('hitpay_reference')->nullable();
            $table->string('hitpay_url')->nullable();
            $table->timestamp('initiated_at')->nullable();
            $table->timestamp('paid_at')->nullable();

            // Audit
            $table->json('payload')->nullable(); // to store HitPay full response or QR params
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};
