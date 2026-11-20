<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AlumniRegistration;
use App\Models\ProgramStudy;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class AlumniRegistrationController extends Controller
{
    const MAX_REGISTER_ATTEMPTS = 5;
    const REGISTER_DECAY_SECONDS = 3600; // 1 jam

    const MAX_STATUS_CHECKS = 15;
    const STATUS_CHECK_DECAY_SECONDS = 600; // 10 menit

    public function __construct(protected NotificationService $notifications)
    {
    }

    /**
     * Tampilkan formulir pendaftaran mandiri alumni.
     */
    public function showRegistrationForm()
    {
        $programStudies = ProgramStudy::active()->orderBy('name')->get();

        return view('auth.alumni-register', compact('programStudies'));
    }

    /**
     * Proses pendaftaran mandiri akun alumni dan simpan bukti kelulusan ke storage privat.
     */
    public function register(Request $request)
    {
        $rateKey = 'alumni-register|' . $request->ip();
        if (RateLimiter::tooManyAttempts($rateKey, self::MAX_REGISTER_ATTEMPTS)) {
            $minutes = max(1, (int) ceil(RateLimiter::availableIn($rateKey) / 60));
            throw ValidationException::withMessages([
                'nim' => ["Terlalu banyak pengajuan pendaftaran dari perangkat ini. Silakan coba kembali dalam {$minutes} menit."],
            ]);
        }

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'nim' => ['required', 'string', 'max:50', 'regex:/^[A-Za-z0-9._-]+$/'],
            'program_study_id' => 'required|integer|exists:program_studies,id',
            'tahun_lulus' => ['required', 'integer', 'min:1970', 'max:' . (int) date('Y')],
            'email' => 'required|email:rfc|max:255',
            'no_hp' => ['required', 'string', 'min:9', 'max:20', 'regex:/^[0-9+\-\s()]+$/'],
            'proof_file' => 'required|file|mimes:pdf,jpg,jpeg,png|mimetypes:application/pdf,image/jpeg,image/png|max:2048',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'nim.regex' => 'NIM hanya boleh berisi huruf, angka, titik, garis bawah, atau tanda hubung.',
            'no_hp.regex' => 'Format nomor HP/WA tidak valid.',
            'proof_file.required' => 'Dokumen bukti kelulusan (Ijazah / SKL / Transkrip) wajib diunggah.',
            'proof_file.mimes' => 'Bukti kelulusan harus berupa berkas PDF, JPG, JPEG, atau PNG.',
            'proof_file.max' => 'Ukuran maksimal berkas bukti kelulusan adalah 2 MB.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        // Pastikan program studi aktif
        $activeProdi = ProgramStudy::active()->whereKey($validated['program_study_id'])->exists();
        if (!$activeProdi) {
            throw ValidationException::withMessages([
                'program_study_id' => ['Program studi yang dipilih tidak aktif atau tidak valid.'],
            ]);
        }

        $nim = trim($validated['nim']);
        $email = strtolower(trim($validated['email']));
        $nama = trim($validated['nama']);
        $noHp = trim($validated['no_hp']);

        // 1. Cegah duplikasi pengajuan PENDING
        $pendingByNim = AlumniRegistration::where('nim', $nim)
            ->where('status', AlumniRegistration::STATUS_PENDING)
            ->exists();
        if ($pendingByNim) {
            throw ValidationException::withMessages([
                'nim' => ['NIM ini sudah memiliki pengajuan yang sedang menunggu verifikasi Admin.'],
            ]);
        }

        $pendingByEmail = AlumniRegistration::whereRaw('LOWER(email) = ?', [$email])
            ->where('status', AlumniRegistration::STATUS_PENDING)
            ->exists();
        if ($pendingByEmail) {
            throw ValidationException::withMessages([
                'email' => ['Email ini sudah terdaftar pada pengajuan yang sedang menunggu verifikasi Admin.'],
            ]);
        }

        // 2. Cek bentrok dengan akun users eksisting
        $existingUserByNim = User::where('nomor_identitas', $nim)->with('userType')->first();
        if ($existingUserByNim) {
            if ($existingUserByNim->isPengelola() || $existingUserByNim->isDosenPegawai()) {
                throw ValidationException::withMessages([
                    'nim' => ['Nomor identitas ini terdaftar sebagai akun pegawai/dosen/pengelola dan tidak dapat didaftarkan sebagai alumni.'],
                ]);
            }

            if ($existingUserByNim->isAlumni()) {
                throw ValidationException::withMessages([
                    'nim' => ['Akun alumni dengan NIM ini sudah aktif. Silakan masuk melalui halaman Login atau gunakan fitur Lupa Kata Sandi.'],
                ]);
            }
            // Jika role adalah MAHASISWA: diizinkan mendaftar untuk proses alih status ke ALUMNI setelah diverifikasi Admin.
        }

        $existingUserByEmail = User::whereRaw('LOWER(email) = ?', [$email])->first();
        if ($existingUserByEmail && (!$existingUserByNim || $existingUserByEmail->id !== $existingUserByNim->id)) {
            throw ValidationException::withMessages([
                'email' => ['Email ini sudah digunakan oleh akun lain di SIPALA. Gunakan email pribadi aktif lainnya.'],
            ]);
        }

        // 3. Simpan file ke disk privat ('local') dan buat record secara atomik
        $storedPath = null;

        try {
            $file = $request->file('proof_file');
            $storedPath = $file->store('alumni-proofs/' . date('Y/m'), 'local');

            $registration = DB::transaction(function () use ($nama, $nim, $email, $noHp, $validated, $file, $storedPath) {
                return AlumniRegistration::create([
                    'nama' => $nama,
                    'nim' => $nim,
                    'email' => $email,
                    'no_hp' => $noHp,
                    'program_study_id' => $validated['program_study_id'],
                    'tahun_lulus' => (int) $validated['tahun_lulus'],
                    'proof_file_path' => $storedPath,
                    'proof_original_name' => $file->getClientOriginalName(),
                    'proof_mime_type' => $file->getMimeType(),
                    'password' => Hash::make($validated['password']),
                    'status' => AlumniRegistration::STATUS_PENDING,
                ]);
            });
        } catch (Throwable $e) {
            if ($storedPath && Storage::disk('local')->exists($storedPath)) {
                Storage::disk('local')->delete($storedPath);
            }

            Log::error('Gagal menyimpan pendaftaran alumni: ' . $e->getMessage());

            return back()
                ->withInput($request->except(['password', 'password_confirmation', 'proof_file']))
                ->withErrors(['nim' => 'Terjadi kesalahan saat memproses pendaftaran. Silakan coba kembali.']);
        }

        RateLimiter::hit($rateKey, self::REGISTER_DECAY_SECONDS);

        // Kirim email konfirmasi (non-blocking jika SMTP bermasalah)
        $this->notifications->sendAlumniRegistrationSubmitted($registration);

        return redirect()
            ->route('alumni.status')
            ->with('status_success', 'Pendaftaran akun alumni berhasil dikirim! Admin SIPALA akan memverifikasi data dan berkas kelulusan Anda.')
            ->with('checked_nim', $nim)
            ->with('checked_email', $email);
    }

    /**
     * Tampilkan halaman cek status verifikasi pendaftaran alumni.
     */
    public function showStatusForm(Request $request)
    {
        $registration = null;

        $nim = session('checked_nim');
        $email = session('checked_email');

        if ($nim && $email) {
            $registration = AlumniRegistration::with('programStudy')
                ->where('nim', trim((string) $nim))
                ->whereRaw('LOWER(email) = ?', [strtolower(trim((string) $email))])
                ->latest()
                ->first();
        }

        return view('auth.alumni-status', compact('registration', 'nim', 'email'));
    }

    /**
     * Proses pencarian status verifikasi pendaftaran alumni (memerlukan NIM dan Email cocok).
     */
    public function checkStatus(Request $request)
    {
        $rateKey = 'alumni-status|' . $request->ip();
        if (RateLimiter::tooManyAttempts($rateKey, self::MAX_STATUS_CHECKS)) {
            $minutes = max(1, (int) ceil(RateLimiter::availableIn($rateKey) / 60));
            throw ValidationException::withMessages([
                'nim' => ["Terlalu banyak percobaan pengecekan status. Silakan tunggu {$minutes} menit."],
            ]);
        }

        RateLimiter::hit($rateKey, self::STATUS_CHECK_DECAY_SECONDS);

        $validated = $request->validate([
            'nim' => 'required|string|max:50',
            'email' => 'required|email|max:255',
        ]);

        $nim = trim($validated['nim']);
        $email = strtolower(trim($validated['email']));

        $registration = AlumniRegistration::with('programStudy')
            ->where('nim', $nim)
            ->whereRaw('LOWER(email) = ?', [$email])
            ->latest()
            ->first();

        if (!$registration) {
            return redirect()
                ->route('alumni.status')
                ->withInput($validated)
                ->withErrors(['nim' => 'Data pengajuan dengan kombinasi NIM dan Email tersebut tidak ditemukan.']);
        }

        return redirect()
            ->route('alumni.status')
            ->with('checked_nim', $nim)
            ->with('checked_email', $email);
    }
}

