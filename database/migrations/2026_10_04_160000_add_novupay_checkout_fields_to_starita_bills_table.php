<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('starita')->table('starita_bills', function (Blueprint $table) {
            $schema = Schema::connection('starita');
            $after = $schema->hasColumn('starita_bills', 'hitpay_url')
                ? 'hitpay_url'
                : ($schema->hasColumn('starita_bills', 'hitpay_reference') ? 'hitpay_reference' : 'status');

            if (!$schema->hasColumn('starita_bills', 'novupay_id')) {
                $table->unsignedBigInteger('novupay_id')->nullable()->after($after);
            }
            if (!$schema->hasColumn('starita_bills', 'novupay_uid')) {
                $table->string('novupay_uid', 64)->nullable()->after('novupay_id');
            }
            if (!$schema->hasColumn('starita_bills', 'novupay_req_id')) {
                $table->string('novupay_req_id', 128)->nullable()->index()->after('novupay_uid');
            }
            if (!$schema->hasColumn('starita_bills', 'novupay_amount')) {
                $table->decimal('novupay_amount', 12, 2)->nullable()->after('novupay_req_id');
            }
            if (!$schema->hasColumn('starita_bills', 'novupay_status')) {
                $table->string('novupay_status', 32)->nullable()->after('novupay_amount');
            }
            if (!$schema->hasColumn('starita_bills', 'novupay_ref_code')) {
                $table->string('novupay_ref_code', 64)->nullable()->after('novupay_status');
            }
        });
    }

    public function down(): void
    {
        Schema::connection('starita')->table('starita_bills', function (Blueprint $table) {
            $schema = Schema::connection('starita');
            foreach (['novupay_id', 'novupay_uid', 'novupay_req_id', 'novupay_amount', 'novupay_status', 'novupay_ref_code'] as $column) {
                if ($schema->hasColumn('starita_bills', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
