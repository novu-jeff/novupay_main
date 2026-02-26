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
        if (Schema::connection('kaelco')->hasTable('bills')) {
            return;
        }
        Schema::connection('kaelco')->create('bills', function (Blueprint $table) {
        $table->id();
        $table->string('reference_no');
        $table->string('account_no');
        $table->string('payor')->nullable();
        $table->string('email')->nullable();
        $table->string('contact_no')->nullable();
        $table->string('address')->nullable();
        $table->decimal('amount', 12, 2);
        $table->decimal('surcharge', 12, 2)->default(0);
        $table->string('bill_month')->nullable();
        $table->text('description')->nullable();
        $table->string('status')->default('initiated');
        $table->string('hitpay_reference')->nullable();
        $table->string('hitpay_url')->nullable();
        $table->timestamp('initiated_at')->nullable();
        $table->timestamp('paid_at')->nullable();
        $table->json('payload')->nullable();
        $table->timestamps();
    });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kaelco_bills');
    }
};
