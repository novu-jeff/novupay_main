<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('account_no')->nullable()->after('reference_no');
            $table->decimal('base_amount', 10, 2)->nullable()->after('amount');
            $table->decimal('service_fee', 10, 2)->nullable()->after('base_amount');
            $table->string('payment_status')->default('pending')->after('by_method');
            $table->string('payer_name')->nullable()->after('payment_status');
            $table->string('payer_email')->nullable()->after('payer_name');
            $table->string('payer_contact')->nullable()->after('payer_email');
            $table->json('response_payload')->nullable()->after('callback_url');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn([
                'account_no',
                'base_amount',
                'service_fee',
                'payment_status',
                'payer_name',
                'payer_email',
                'payer_contact',
                'response_payload',
            ]);
        });
    }
};
