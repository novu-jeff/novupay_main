<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('bills', function (Blueprint $table) {
            $table->string('email')->nullable()->after('payor');
            $table->string('contact_no')->nullable()->after('email');
            $table->string('due_date')->nullable()->after('contact_no');
            $table->decimal('arrears', 12, 2)->nullable()->after('amount');
            $table->integer('prev_reading')->nullable()->after('arrears');
            $table->integer('present_reading')->nullable()->after('prev_reading');
            $table->string('billing_period_from')->nullable()->after('present_reading');
            $table->string('billing_period_to')->nullable()->after('billing_period_from');
        });
    }

    public function down(): void
    {
        Schema::table('bills', function (Blueprint $table) {
            $table->dropColumn([
                'email',
                'contact_no',
                'due_date',
                'arrears',
                'prev_reading',
                'present_reading',
                'billing_period_from',
                'billing_period_to',
            ]);
        });
    }
};
