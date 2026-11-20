<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // =============================================
        // SUPPORT TABLES
        // =============================================

        // Staff Specialties (pivot: staff <-> service)
        Schema::create('staff_specialties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->timestamp('created_at')->nullable();

            $table->unique(['user_id', 'service_id']);
        });

        // Email Notifications Log
        Schema::create('email_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->string('type', 50);             // REJECTED, COMPLETED
            $table->string('subject', 255);
            $table->text('body');
            $table->timestamp('sent_at')->nullable();
            $table->string('status', 20)->default('PENDING'); // PENDING, SENT, FAILED
            $table->text('error_message')->nullable();
            $table->timestamps();
        });

        // Activity Logs (audit trail)
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->string('action', 100);           // LOGIN, CREATE, UPDATE, DELETE, VERIFY, REJECT, ASSIGN, etc.
            $table->string('module', 100);            // CATEGORY, SERVICE, TICKET, USER, etc.
            $table->unsignedBigInteger('reference_id')->nullable(); // ID of related entity
            $table->text('description')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index('user_id');
            $table->index('module');
            $table->index('created_at');
        });

        // Settings (system configuration)
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('email_notifications');
        Schema::dropIfExists('staff_specialties');
    }
};
