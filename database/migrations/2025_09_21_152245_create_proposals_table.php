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
        Schema::create('proposals', function (Blueprint $table) {
            $table->id();
            $table->string('proposal_no', 50)->unique();
            $table->string('title');
            $table->decimal('amount', 15, 2)->nullable();
            $table->string('currency_code', 3)->default('BDT');
            $table->string('client_org')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('client_email')->nullable();
            $table->string('client_phone', 50); // Required for OTP verification
            $table->text('notes')->nullable();
            $table->enum('status', ['draft', 'pending', 'verified'])->default('pending');
            
            // OTP fields for verification
            $table->string('otp_code', 6)->nullable();
            $table->timestamp('otp_expires_at')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('status');
            $table->index('client_phone');
            $table->index(['proposal_no', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proposals');
    }
};