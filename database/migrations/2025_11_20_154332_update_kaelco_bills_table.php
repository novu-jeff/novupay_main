<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('kaelco')->table('bills', function (Blueprint $table) {
            // bill_month must be text, not array
            $table->text('bill_month')->nullable()->change();

            // payload must be JSON (required because you store $hitpayData)
            $table->json('payload')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::connection('kaelco')->table('bills', function (Blueprint $table) {
            // revert to previous state if needed
            $table->string('bill_month')->nullable()->change();
            $table->text('payload')->nullable()->change();
        });
    }
};
