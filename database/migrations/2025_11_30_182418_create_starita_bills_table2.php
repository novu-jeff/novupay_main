<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // 🔥 FORCE THIS MIGRATION TO USE STARITA DATABASE
    protected $connection = 'starita';

    public function up()
    {
        if (Schema::connection($this->connection)->hasTable('starita_bills')) {
            return;
        }
        Schema::connection($this->connection)->create('starita_bills', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no')->unique();
            $table->string('account_no');
            $table->decimal('amount', 10, 2)->default(0);
            $table->integer('present_reading')->nullable();
            $table->boolean('is_high_consumption')->default(false);
            $table->string('status')->default('initiated');
            $table->json('payload')->nullable();
            $table->timestamp('initiated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::connection($this->connection)->dropIfExists('starita_bills');
    }
};
