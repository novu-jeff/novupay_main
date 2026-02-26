<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('kaelco')->table('bills', function (Blueprint $table) {
            if (!Schema::connection('kaelco')->hasColumn('bills', 'disconnection_fee')) {
                $table->decimal('disconnection_fee', 12, 2)->default(0)->after('amount');
            }
        });
    }

    public function down(): void
    {
        Schema::connection('kaelco')->table('bills', function (Blueprint $table) {
            $table->dropColumn('disconnection_fee');
        });
    }
};
