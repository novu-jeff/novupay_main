<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('kaelco')->table('bills', function (Blueprint $table) {
            $table->decimal('convenience_fee', 12, 2)->default(0)->after('surcharge');
        });
    }

    public function down(): void
    {
        Schema::connection('kaelco')->table('bills', function (Blueprint $table) {
            $table->dropColumn('convenience_fee');
        });
    }
};
