<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // =============================================
        // SERVICE MANAGEMENT TABLES
        // =============================================

        // Categories
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->string('icon', 100)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Services
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories');
            $table->foreignId('unit_id')->nullable()->constrained('service_units')->nullOnDelete();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->integer('sla_duration')->default(3);
            $table->enum('sla_unit', ['jam', 'hari', 'hari_kerja'])->default('hari_kerja');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('category_id');
            $table->index('is_active');
        });

        // Service Targets (pivot: service <-> user_type)
        Schema::create('service_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->foreignId('user_type_id')->constrained('user_types')->cascadeOnDelete();
            $table->timestamp('created_at')->nullable();

            $table->unique(['service_id', 'user_type_id']);
        });

        // Service Requirements
        Schema::create('service_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->boolean('is_required')->default(true);
            $table->string('allowed_file_types', 255)->default('pdf,jpg,jpeg,png');
            $table->integer('max_file_size')->default(2048); // KB
            $table->integer('max_file_count')->default(1);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Service Fields (custom dynamic form fields)
        Schema::create('service_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->string('label', 255);
            $table->enum('field_type', ['text', 'textarea', 'number', 'date', 'select', 'radio', 'checkbox']);
            $table->json('options')->nullable(); // Options for select/radio/checkbox
            $table->string('placeholder', 255)->nullable();
            $table->boolean('is_required')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Statuses
        Schema::create('statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();   // Internal name
            $table->string('label', 100);             // Display label
            $table->string('color', 20)->default('#6b7280');
            $table->string('icon', 50)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_default')->default(false);   // Initial status for new tickets
            $table->boolean('is_terminal')->default(false);  // SELESAI/DITOLAK
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('statuses');
        Schema::dropIfExists('service_fields');
        Schema::dropIfExists('service_requirements');
        Schema::dropIfExists('service_targets');
        Schema::dropIfExists('services');
        Schema::dropIfExists('categories');
    }
};
