<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'morong';

    public function up()
    {
        try {
            $schema = Schema::connection($this->connection);
            if ($schema->hasTable('morong_hitpay_transactions')) {
                return;
            }
            $schema->create('morong_hitpay_transactions', function (Blueprint $table) {
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
        } catch (\Throwable $e) {
            // Morong DB not configured or unreachable — skip (e.g. Access denied)
            return;
        }
    }

    public function down()
    {
        Schema::connection($this->connection)->dropIfExists('morong_hitpay_transactions');
    }
};
