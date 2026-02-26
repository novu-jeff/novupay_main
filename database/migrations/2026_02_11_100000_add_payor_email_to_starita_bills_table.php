<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('starita')->table('starita_bills', function (Blueprint $table) {
            if (!Schema::connection('starita')->hasColumn('starita_bills', 'payor')) {
                $table->string('payor')->nullable()->after('account_no');
            }
            if (!Schema::connection('starita')->hasColumn('starita_bills', 'email')) {
                $table->string('email')->nullable()->after('payor');
            }
            if (!Schema::connection('starita')->hasColumn('starita_bills', 'contact_no')) {
                $table->string('contact_no')->nullable()->after('email');
            }
        });
    }

    public function down(): void
    {
        Schema::connection('starita')->table('starita_bills', function (Blueprint $table) {
            if (Schema::connection('starita')->hasColumn('starita_bills', 'contact_no')) {
                $table->dropColumn('contact_no');
            }
            if (Schema::connection('starita')->hasColumn('starita_bills', 'email')) {
                $table->dropColumn('email');
            }
            if (Schema::connection('starita')->hasColumn('starita_bills', 'payor')) {
                $table->dropColumn('payor');
            }
        });
    }
};
