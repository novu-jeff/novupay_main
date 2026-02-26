<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('starita')->table('starita_bills', function (Blueprint $table) {
            if (!Schema::connection('starita')->hasColumn('starita_bills', 'hitpay_reference')) {
                $table->string('hitpay_reference')->nullable()->index()->after('reference_no');
            }
        });
    }

    public function down(): void
    {
        Schema::connection('starita')->table('starita_bills', function (Blueprint $table) {
            if (Schema::connection('starita')->hasColumn('starita_bills', 'hitpay_reference')) {
                $table->dropColumn('hitpay_reference');
            }
        });
    }
};
