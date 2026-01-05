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
        Schema::create('application', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
            ->constrained()
            ->onDelete('cascade');

            $table->string('company_name');
            $table->string('role_title');
            $table->string('job_url')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->date('applied_at');
            $table->date('last_activity_at');
            $table->date('follow_up_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application');
    }
};
