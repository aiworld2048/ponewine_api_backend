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
        Schema::create('report_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('agent_id')->nullable();
            $table->string('member_account')->nullable();
            $table->decimal('transaction_amount', 12);
            $table->decimal('bet_amount', 12)->nullable();
            $table->decimal('valid_amount', 12)->nullable();
            $table->string('status')->default('0');
            $table->string('banker')->default('0');
            $table->string('wager_code')->nullable();
            $table->string('settled_status')->nullable();
            $table->decimal('before_balance', 20, 4)->nullable();
            $table->decimal('after_balance', 20, 4)->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('agent_id')->references('id')->on('users');
            // Indexes for better query performance
            $table->index('status');
            $table->index('banker');
            $table->index('created_at');
            // Composite indexes for common queries
            $table->index(['user_id', 'created_at']);
            $table->index(['status', 'created_at']);
            $table->index(['wager_code', 'created_at']);
            $table->index(['settled_status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_transactions');
    }
};
