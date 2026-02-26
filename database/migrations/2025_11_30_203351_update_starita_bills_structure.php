<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::connection('starita')->table('starita_bills', function (Blueprint $table) {

            // Ensure required columns exist
            if (!Schema::connection('starita')->hasColumn('starita_bills', 'account_no')) {
                $table->string('account_no')->after('reference_no');
            }

            if (!Schema::connection('starita')->hasColumn('starita_bills', 'present_reading')) {
                $table->integer('present_reading')->nullable()->after('amount');
            }

            if (!Schema::connection('starita')->hasColumn('starita_bills', 'is_high_consumption')) {
                $table->boolean('is_high_consumption')->default(0)->after('present_reading');
            }

            if (!Schema::connection('starita')->hasColumn('starita_bills', 'status')) {
                $table->string('status')->default('initiated')->after('is_high_consumption');
            }

            if (!Schema::connection('starita')->hasColumn('starita_bills', 'payload')) {
                $table->json('payload')->nullable()->after('status');
            }

            if (!Schema::connection('starita')->hasColumn('starita_bills', 'initiated_at')) {
                $table->timestamp('initiated_at')->nullable()->after('payload');
            }
        });
    }

    public function down(): void
    {
        // No down logic for safety
    }
};
