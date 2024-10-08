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
            $table->double("fees")->default(0);
            $table->double("amount")->default(0);
            $table->double("unit_quantity")->default(0);
            $table->string("description")->nullable();
            $table->string("activity")->nullable();
            $table->string("batch_no")->nullable();
            $table->double("prev_balance")->nullable();
            $table->double("current_balance")->nullable();
            $table->enum("type", [TransactionConstants::CREDIT, TransactionConstants::DEBIT]);
            $table->text("metadata")->nullable();
            $table->string("status");
            $table->dateTime("completed_at")->nullable();
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
