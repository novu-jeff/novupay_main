<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'morong';

    public function up()
    {
        try {
            $schema = Schema::connection($this->connection);
            if ($schema->hasTable('morong_bills')) {
                return;
            }
            $schema->create('morong_bills', function (Blueprint $table) {
                $table->id();
                $table->string('reference_no')->unique();
                $table->string('account_no');
                $table->decimal('amount', 10, 2)->default(0);
                $table->integer('present_reading')->nullable();
                $table->integer('previous_reading')->nullable();
                $table->integer('consumption')->nullable();
                $table->boolean('is_high_consumption')->default(false);
                $table->string('status')->default('initiated');
                $table->string('hitpay_reference')->nullable()->index();
                $table->string('hitpay_url')->nullable();
                $table->json('payload')->nullable();
                $table->timestamp('initiated_at')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->timestamps();
            });
        } catch (\Throwable $e) {
            // Morong DB not configured or unreachable — skip (e.g. Access denied)
            return;
        }
    }

    public function down()
    {
        Schema::connection($this->connection)->dropIfExists('morong_bills');
    }
};
