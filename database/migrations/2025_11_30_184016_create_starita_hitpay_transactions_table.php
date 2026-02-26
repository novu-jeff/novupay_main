<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // 🔥 force migration to starita database
    protected $connection = 'starita';

    public function up()
    {
        if (Schema::connection($this->connection)->hasTable('starita_hitpay_transactions')) {
            return;
        }
        Schema::connection($this->connection)->create('starita_hitpay_transactions', function (Blueprint $table) {
            $table->id();

            // Link to novustream bill reference
            $table->string('reference_no')->index();

            // HitPay Data
            $table->string('hitpay_id')->nullable()->index();
            $table->string('payment_request_id')->nullable();
            $table->string('payment_url')->nullable();

            // Transactional Data
            $table->decimal('amount', 10, 2)->default(0.00);
            $table->decimal('convenience_fee', 10, 2)->default(0.00);
            $table->decimal('final_amount', 10, 2)->default(0.00);

            $table->string('status')->default('initiated'); // initiated, paid, failed, cancelled

            // JSON logs
            $table->json('request_payload')->nullable();   // data sent TO HitPay
            $table->json('response_payload')->nullable();  // data returned FROM HitPay

            // Timestamps
            $table->timestamp('initiated_at')->nullable();
            $table->timestamp('paid_at')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::connection($this->connection)
            ->dropIfExists('starita_hitpay_transactions');
    }
};
