<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // =============================================
        // MASTER DATA TABLES
        // =============================================

        // User Types
        Schema::create('user_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique(); // MAHASISWA, ALUMNI, DOSEN_PEGAWAI, STAFF, ADMIN
            $table->string('label', 100);          // Label tampilan
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Program Studies
        Schema::create('program_studies', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique()->nullable();
            $table->string('name', 255);
            $table->string('level', 50)->nullable(); // S1, S2, S3, PJJ, PPG
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Service Units
        Schema::create('service_units', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('type', 100)->nullable(); // Rektorat, Lembaga, Pusat, Fakultas, dll
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Users (replace default Laravel users table)
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_type_id')->constrained('user_types');
            $table->foreignId('program_study_id')->nullable()->constrained('program_studies')->nullOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained('service_units')->nullOnDelete();
            $table->string('nama', 255);
            $table->string('email', 255)->unique();
            $table->string('nomor_identitas', 50)->unique(); // NIM / NIP / NIK
            $table->string('password', 255);
            $table->string('avatar', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->index('user_type_id');
            $table->index('email');
            $table->index('nomor_identitas');
        });

        // Password Reset Tokens
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // Sessions
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
        Schema::dropIfExists('service_units');
        Schema::dropIfExists('program_studies');
        Schema::dropIfExists('user_types');
    }
};
