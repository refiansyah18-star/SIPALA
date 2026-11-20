<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah kolom sumber autentikasi & profil alumni pada tabel users
        Schema::table('users', function (Blueprint $table) {
            $table->string('auth_source', 20)->default('BRIDGE')->after('password');
            $table->string('no_hp', 30)->nullable()->after('nomor_identitas');
            $table->unsignedSmallInteger('tahun_lulus')->nullable()->after('no_hp');
        });

        // Backfill akun internal dan akun alumni demo/eksisting menjadi LOCAL
        $localTypeIds = DB::table('user_types')
            ->whereIn('name', ['SUPER_ADMIN', 'ADMIN', 'STAFF', 'ALUMNI'])
            ->pluck('id');

        if ($localTypeIds->isNotEmpty()) {
            DB::table('users')
                ->whereIn('user_type_id', $localTypeIds)
                ->update(['auth_source' => 'LOCAL']);
        }

        // 2. Buat tabel antrean pendaftaran & verifikasi mandiri alumni
        Schema::create('alumni_registrations', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 255);
            $table->string('nim', 50)->index();
            $table->string('email', 255)->index();
            $table->string('no_hp', 30);
            $table->foreignId('program_study_id')->constrained('program_studies');
            $table->unsignedSmallInteger('tahun_lulus');
            $table->string('proof_file_path', 500);
            $table->string('proof_original_name', 255);
            $table->string('proof_mime_type', 100)->nullable();
            $table->string('password', 255);
            $table->string('status', 20)->default('PENDING')->index(); // PENDING, APPROVED, REJECTED
            $table->text('rejection_reason')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 3. Izinkan email_notifications mencatat notifikasi non-tiket (pendaftaran/approval/reset alumni)
        Schema::table('email_notifications', function (Blueprint $table) {
            $table->unsignedBigInteger('ticket_id')->nullable()->change();
            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->foreignId('alumni_registration_id')
                ->nullable()
                ->after('ticket_id')
                ->constrained('alumni_registrations')
                ->nullOnDelete();
            $table->string('recipient_email', 255)->nullable()->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('email_notifications', function (Blueprint $table) {
            $table->dropForeign(['alumni_registration_id']);
            $table->dropColumn(['alumni_registration_id', 'recipient_email']);
        });

        Schema::dropIfExists('alumni_registrations');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['auth_source', 'no_hp', 'tahun_lulus']);
        });
    }
};

