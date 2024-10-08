<?php

use App\Constants\General\StatusConstants;
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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained("users")->cascadeOnDelete();
            $table->foreignId("currency_id")->nullable()->constrained("currencies")->nullOnDelete();
            $table->foreignId('transaction_id')->nullable()->constrained("transactions")->cascadeOnDelete();
            $table->string('reference')->unique();
            $table->double('discount')->default(0);
            $table->double('amount');
            $table->double('fees')->default(0);
            $table->string('gateway')->nullable();
            $table->string('activity');
            $table->string('action')->nullable();
            $table->text("metadata")->nullable();
            $table->dateTime('confirmed_at')->nullable();
            $table->string('status')->default(StatusConstants::PENDING);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
