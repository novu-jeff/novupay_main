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
        Schema::table('bills', function (Blueprint $table) {
            // Add new field from Pelco payload
            if (!Schema::hasColumn('bills', 'consumer_type')) {
                $table->string('consumer_type')->nullable()->after('payor');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bills', function (Blueprint $table) {
            if (Schema::hasColumn('bills', 'consumer_type')) {
                $table->dropColumn('consumer_type');
            }
        });
    }
};
