<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // =============================================
        // TICKET TABLES
        // =============================================

        // Tickets
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number', 30)->unique();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('service_id')->constrained('services');
            $table->foreignId('category_id')->constrained('categories');
            $table->foreignId('program_study_id')->nullable()->constrained('program_studies')->nullOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained('service_units')->nullOnDelete();
            $table->foreignId('status_id')->constrained('statuses');
            $table->string('subject', 255);
            $table->text('description');
            $table->integer('sla_duration');           // Snapshot SLA saat dibuat
            $table->string('sla_unit', 20);            // Snapshot satuan SLA
            $table->dateTime('deadline');               // Dihitung saat ticket dibuat
            $table->text('rejection_reason')->nullable(); // Wajib jika DITOLAK
            $table->text('resolution_note')->nullable();  // Catatan penyelesaian
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index('ticket_number');
            $table->index('user_id');
            $table->index('service_id');
            $table->index('status_id');
            $table->index('deadline');
            $table->index('created_at');
        });

        // Ticket Field Values (custom field answers)
        Schema::create('ticket_field_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $table->foreignId('service_field_id')->constrained('service_fields');
            $table->text('value')->nullable();
            $table->timestamps();

            $table->unique(['ticket_id', 'service_field_id']);
        });

        // Ticket Attachments
        Schema::create('ticket_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $table->foreignId('requirement_id')->nullable()->constrained('service_requirements')->nullOnDelete();
            $table->string('original_name', 255);
            $table->string('stored_name', 255);
            $table->string('file_path', 500);
            $table->unsignedInteger('file_size');       // Bytes
            $table->string('mime_type', 100);
            $table->timestamps();
        });

        // Ticket Assignments
        Schema::create('ticket_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained('users');
            $table->foreignId('assigned_by')->constrained('users');
            $table->text('notes')->nullable();
            $table->timestamp('assigned_at');
            $table->timestamps();

            $table->index('ticket_id');
            $table->index('staff_id');
        });

        // Ticket Status Histories (timeline)
        Schema::create('ticket_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $table->foreignId('from_status_id')->nullable()->constrained('statuses'); // NULL = initial
            $table->foreignId('to_status_id')->constrained('statuses');
            $table->foreignId('changed_by')->constrained('users');
            $table->text('note')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['ticket_id', 'created_at']);
        });

        // Ticket Messages (chat)
        Schema::create('ticket_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->text('message'); // Max 50 kata (validated in backend)
            $table->timestamps();

            $table->index(['ticket_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_messages');
        Schema::dropIfExists('ticket_status_histories');
        Schema::dropIfExists('ticket_assignments');
        Schema::dropIfExists('ticket_attachments');
        Schema::dropIfExists('ticket_field_values');
        Schema::dropIfExists('tickets');
    }
};
