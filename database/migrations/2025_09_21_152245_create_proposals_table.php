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
            $table->string('unique_token', 100)->unique(); // Unique URL token
            $table->string('title');
            $table->string('company_name');
            $table->string('client_phone', 50); // Required for OTP verification
            $table->string('pdf_path')->nullable(); // Path to uploaded PDF
            $table->text('notes')->nullable();
            $table->enum('status', ['draft', 'pending', 'verified'])->default('pending');
            
            // OTP fields for verification
            $table->string('otp_code', 6)->nullable();
            $table->timestamp('otp_expires_at')->nullable();
            
            // Track verification
            $table->timestamp('verified_at')->nullable();
            $table->integer('verification_count')->default(0);
            
            $table->timestamps();
            
            // Indexes
            $table->index('unique_token');
            $table->index('status');
            $table->index('client_phone');
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