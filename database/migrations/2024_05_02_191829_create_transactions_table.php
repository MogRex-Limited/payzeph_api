<?php

use App\Constants\Finance\TransactionConstants;
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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")->constrained("users")->cascadeOnDelete();
            $table->foreignId("wallet_id")->nullable()->constrained("wallets")->cascadeOnDelete();
            $table->foreignId("currency_id")->nullable()->constrained("currencies")->nullOnDelete();
            $table->string("reference", 50)->unique();
            $table->double("amount")->default(0);
            $table->double("fee")->default(0);
            $table->double("profit")->default(0);
            $table->enum("type", [TransactionConstants::CREDIT, TransactionConstants::DEBIT]);
            $table->string("description")->nullable();
            $table->string("activity")->nullable();
            $table->string("category")->nullable();
            $table->string("batch_no")->nullable();
            $table->string("action")->nullable();
            $table->double("prev_balance")->nullable();
            $table->double("current_balance")->nullable();
            $table->string("status");
            $table->text("metadata")->nullable();
            $table->dateTime("completed_at")->nullable();
            $table->foreignId("sender_transaction_id")->nullable()->constrained("transactions")->nullOnDelete();
            $table->foreignId("recipient_transaction_id")->nullable()->constrained("transactions")->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
