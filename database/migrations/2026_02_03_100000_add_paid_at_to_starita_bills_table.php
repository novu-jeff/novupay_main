<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('starita')->table('starita_bills', function (Blueprint $table) {
            if (!Schema::connection('starita')->hasColumn('starita_bills', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('initiated_at');
            }
        });
    }

    public function down(): void
    {
        Schema::connection('starita')->table('starita_bills', function (Blueprint $table) {
            $table->dropColumn('paid_at');
        });
    }
};
