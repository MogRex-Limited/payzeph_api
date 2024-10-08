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
        Schema::create('bank_payment_proofs', function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")->constrained("users")->cascadeOnDelete();
            $table->foreignId("file_id")->nullable()->constrained("files")->nullOnDelete();
            $table->double("amount")->default(0);
            $table->string("type")->nullable();
            $table->text("description")->nullable();
            $table->string("reference")->unique();
            $table->foreignId("approved_by")->nullable()->constrained("admins")->nullOnDelete();
            $table->string("status")->default(StatusConstants::PENDING);
            $table->dateTime("approved_at")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_payment_proofs');
    }
};
