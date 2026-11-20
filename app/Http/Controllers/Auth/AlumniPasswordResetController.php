<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AlumniPasswordResetController extends Controller
{
    const MAX_RESET_REQUESTS = 5;
    const RESET_DECAY_SECONDS = 900; // 15 menit
    const TOKEN_EXPIRE_MINUTES = 60;

    public function __construct(protected NotificationService $notifications)
    {
    }

    /**
     * Tampilkan formulir permintaan tautan reset password khusus Alumni.
     */
    public function showLinkRequestForm()
    {
        return view('auth.alumni-forgot-password');
    }

    /**
     * Kirim tautan reset password ke email Alumni lokal yang terverifikasi.
     */
    public function sendResetLinkEmail(Request $request)
    {
        $rateKey = 'alumni-forgot|' . $request->ip();
        if (RateLimiter::tooManyAttempts($rateKey, self::MAX_RESET_REQUESTS)) {
            $minutes = max(1, (int) ceil(RateLimiter::availableIn($rateKey) / 60));
            throw ValidationException::withMessages([
                'email' => ["Terlalu banyak permintaan reset kata sandi. Silakan tunggu {$minutes} menit."],
            ]);
        }

        RateLimiter::hit($rateKey, self::RESET_DECAY_SECONDS);

        $validated = $request->validate([
            'email' => 'required|email|max:255',
        ]);

        $email = strtolower(trim($validated['email']));
        $user = User::whereRaw('LOWER(email) = ?', [$email])->with('userType')->first();

        // Jika akun adalah Mahasiswa atau Dosen SSO, arahkan ke Portal Kampus
        if ($user && ($user->isMahasiswa() || $user->isDosenPegawai()) && $user->isBridgeAuth()) {
            throw ValidationException::withMessages([
                'email' => ['Akun Mahasiswa dan Dosen/Pegawai menggunakan kata sandi SSO Portal Kampus. Silakan ubah kata sandi melalui Portal Kampus.'],
            ]);
        }

        // Demi keamanan, hanya proses akun ALUMNI aktif dengan auth_source = LOCAL
        if ($user && $user->is_active && $user->isAlumni() && $user->isLocalAuth()) {
            $existingToken = DB::table('password_reset_tokens')
                ->where('email', $user->email)
                ->first();

            // Throttling antar permintaan untuk email yang sama (60 detik)
            if ($existingToken && Carbon::parse($existingToken->created_at)->addSeconds(60)->isFuture()) {
                return back()->with('status', 'Tautan atur ulang kata sandi sudah dikirimkan baru-baru ini. Silakan periksa kotak masuk atau folder spam email Anda.');
            }

            $plainToken = Str::random(64);

            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $user->email],
                [
                    'token' => Hash::make($plainToken),
                    'created_at' => now(),
                ]
            );

            $resetUrl = route('alumni.password.reset', ['token' => $plainToken]) . '?email=' . urlencode($user->email);
            $this->notifications->sendAlumniPasswordReset($user, $resetUrl);
        }

        return back()->with('status', 'Jika email tersebut terdaftar sebagai akun Alumni aktif di SIPALA, tautan untuk mengatur ulang kata sandi telah dikirimkan ke email Anda.');
    }

    /**
     * Tampilkan formulir pengisian kata sandi baru berdasarkan token.
     */
    public function showResetForm(Request $request, string $token)
    {
        return view('auth.alumni-reset-password', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    /**
     * Validasi token dan simpan kata sandi baru khusus akun Alumni lokal.
     */
    public function reset(Request $request)
    {
        $rateKey = 'alumni-reset-submit|' . $request->ip();
        if (RateLimiter::tooManyAttempts($rateKey, 10)) {
            $minutes = max(1, (int) ceil(RateLimiter::availableIn($rateKey) / 60));
            throw ValidationException::withMessages([
                'email' => ["Terlalu banyak percobaan reset kata sandi. Silakan tunggu {$minutes} menit."],
            ]);
        }

        RateLimiter::hit($rateKey, self::RESET_DECAY_SECONDS);

        $validated = $request->validate([
            'token' => 'required|string',
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'password.min' => 'Kata sandi baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        $email = strtolower(trim($validated['email']));
        $user = User::whereRaw('LOWER(email) = ?', [$email])->with('userType')->first();

        if (!$user || !$user->is_active || !$user->isAlumni() || !$user->isLocalAuth()) {
            throw ValidationException::withMessages([
                'email' => ['Tautan reset kata sandi tidak valid atau hanya berlaku untuk akun Alumni aktif.'],
            ]);
        }

        $record = DB::table('password_reset_tokens')
            ->where('email', $user->email)
            ->first();

        if (!$record) {
            throw ValidationException::withMessages([
                'email' => ['Token reset kata sandi tidak ditemukan atau sudah digunakan.'],
            ]);
        }

        if (Carbon::parse($record->created_at)->addMinutes(self::TOKEN_EXPIRE_MINUTES)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $user->email)->delete();
            throw ValidationException::withMessages([
                'email' => ['Tautan reset kata sandi sudah kedaluwarsa. Silakan ajukan permintaan baru.'],
            ]);
        }

        if (!Hash::check($validated['token'], $record->token)) {
            throw ValidationException::withMessages([
                'email' => ['Token reset kata sandi tidak valid.'],
            ]);
        }

        DB::transaction(function () use ($user, $validated) {
            $user->forceFill([
                'password' => Hash::make($validated['password']),
                'remember_token' => Str::random(60),
            ])->save();

            DB::table('password_reset_tokens')->where('email', $user->email)->delete();

            ActivityLog::log(
                userId: $user->id,
                action: ActivityLog::ACTION_UPDATE,
                module: ActivityLog::MODULE_AUTH,
                referenceId: $user->id,
                description: 'Alumni mengatur ulang kata sandi melalui tautan reset email'
            );
        });

        // Bersihkan lock login jika sebelumnya sempat gagal masuk
        RateLimiter::clear(Str::transliterate(Str::lower($user->email) . '|' . $request->ip()));
        RateLimiter::clear(Str::transliterate(Str::lower($user->nomor_identitas) . '|' . $request->ip()));

        return redirect()
            ->route('login')
            ->with('status', 'Kata sandi akun Alumni berhasil diperbarui! Silakan masuk menggunakan kata sandi baru Anda.');
    }
}

