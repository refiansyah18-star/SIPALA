<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AlumniRegistration;
use App\Models\User;
use App\Models\UserType;
use App\Services\BridgeApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Maksimal percobaan login gagal sebelum akun dikunci sementara.
     */
    const MAX_LOGIN_ATTEMPTS = 5;

    /**
     * Durasi kunci sementara (lockout) dalam detik: 15 menit (900 detik).
     */
    const LOCKOUT_DECAY_SECONDS = 900;

    protected BridgeApiService $bridge;

    public function __construct(BridgeApiService $bridge)
    {
        $this->bridge = $bridge;
    }

    /**
     * Show login form.
     */
    public function showLoginForm(Request $request)
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        $request->session()->forget('url.intended');

        return view('auth.login');
    }

    /**
     * Handle login request.
     *
     * Flow:
     * 1. Kirim username + password ke Bridge API v2 (portal kampus)
     * 2. Jika Bridge API sukses → dapatkan token → ambil profil
     * 3. Cari/buat user di database lokal SIPALA
     * 4. Login user ke session Laravel
     *
     * Fallback: Jika Bridge API tidak tersedia, login tetap bisa pakai
     * credentials lokal (untuk ADMIN/STAFF yang mungkin tidak punya akun portal).
     */
    public function login(Request $request)
    {
        $request->validate([
            'login'    => 'required|string',
            'password' => 'required|string',
        ]);

        // Jika di tab browser yang sama masih ada sesi akun lain yang aktif,
        // lepaskan autentikasi sesi lama terlebih dahulu agar tidak bentrok saat berganti akun.
        if (Auth::check()) {
            Auth::guard('web')->logout();
            $request->session()->forget('bridge_token');
        }

        // Proteksi Brute-Force: Cek apakah akun/IP sedang dikunci sementara (maks 5x gagal, tunggu 15 menit)
        $this->ensureIsNotRateLimited($request);

        $username = trim($request->login);
        $password = $request->password;
        $key = $this->throttleKey($request);

        // Identifikasi target akun di DB lokal SIPALA (case-insensitive untuk email)
        $isEmailLogin = (bool) filter_var($username, FILTER_VALIDATE_EMAIL);
        $localUser = $isEmailLogin
            ? User::whereRaw('LOWER(email) = ?', [Str::lower($username)])->with('userType')->first()
            : User::where('nomor_identitas', $username)->with('userType')->first();

        $isInternalStaffOrAdmin = $localUser && ($localUser->isAdmin() || $localUser->isSuperAdmin() || $localUser->isStaff());
        $isLocalAlumni = $localUser && $localUser->isAlumni() && $localUser->isLocalAuth();

        // Akun Alumni hasil verifikasi Admin diautentikasi langsung via DB lokal SIPALA
        if ($isLocalAlumni) {
            return $this->handleLocalLogin($request, $isEmailLogin ? $localUser->email : $username, $password);
        }

        // =============================================
        // STEP 1: Coba login via Bridge API (SSO Portal Kampus)
        // =============================================
        $authResult = $this->bridge->authenticate($username, $password);

        if ($authResult['success'] && !empty($authResult['data'])) {
            return $this->handleBridgeLogin($request, $authResult['data'], $username, $password);
        }

        // =============================================
        // STEP 2: Tangani Kegagalan Autentikasi vs Gangguan Koneksi
        // =============================================

        // 1. Jika Bridge API secara eksplisit MENOLAK kredensial (HTTP 401/403/invalid)
        if (($authResult['error_type'] ?? '') === 'INVALID_CREDENTIALS') {
            // Hanya staf/admin internal yang diizinkan mencoba fallback kredensial lokal SIPALA
            if ($isInternalStaffOrAdmin) {
                return $this->handleLocalLogin($request, $isEmailLogin ? $localUser->email : $username, $password);
            }

            // Cek apakah pendaftar sedang mencoba masuk sebelum/ditolak verifikasi Alumni
            $this->throwIfPendingOrRejectedAlumni($request, $username, $isEmailLogin);

            // Untuk Mahasiswa/Dosen SSO: Dilarang fallback ke DB lokal (mencegah bypass password lama)
            RateLimiter::hit($key, self::LOCKOUT_DECAY_SECONDS);
            if (RateLimiter::tooManyAttempts($key, self::MAX_LOGIN_ATTEMPTS)) {
                $this->ensureIsNotRateLimited($request);
            }
            $remaining = RateLimiter::remaining($key, self::MAX_LOGIN_ATTEMPTS);

            throw ValidationException::withMessages([
                'login' => ["NIM/NIP atau kata sandi portal kampus salah. (Sisa percobaan: {$remaining} kali sebelum dikunci 15 menit)"],
            ]);
        }

        // 2. Jika Bridge API mengalami gangguan koneksi / timeout / server error (5xx)
        // Izinkan akun staf/admin masuk via DB lokal agar sistem tetap bisa dioperasikan
        if ($isInternalStaffOrAdmin) {
            return $this->handleLocalLogin($request, $isEmailLogin ? $localUser->email : $username, $password);
        }

        $this->throwIfPendingOrRejectedAlumni($request, $username, $isEmailLogin);

        // Mahasiswa/Dosen SSO tidak boleh login dengan password lokal jika portal kampus sedang down
        RateLimiter::hit($key, self::LOCKOUT_DECAY_SECONDS);
        throw ValidationException::withMessages([
            'login' => ['Layanan autentikasi portal kampus sedang mengalami kendala jaringan atau timeout. Silakan coba kembali beberapa saat lagi.'],
        ]);
    }

    /**
     * Berikan pesan status verifikasi yang jelas bila email/NIM milik pengajuan alumni yang belum disetujui.
     */
    protected function throwIfPendingOrRejectedAlumni(Request $request, string $username, bool $isEmailLogin): void
    {
        $registration = $isEmailLogin
            ? AlumniRegistration::whereRaw('LOWER(email) = ?', [Str::lower($username)])->latest()->first()
            : AlumniRegistration::where('nim', $username)->latest()->first();

        if (!$registration) {
            return;
        }

        $key = $this->throttleKey($request);

        if ($registration->isPending()) {
            RateLimiter::hit($key, self::LOCKOUT_DECAY_SECONDS);
            if (RateLimiter::tooManyAttempts($key, self::MAX_LOGIN_ATTEMPTS)) {
                $this->ensureIsNotRateLimited($request);
            }
            throw ValidationException::withMessages([
                'login' => ['Pendaftaran akun alumni Anda masih menunggu verifikasi Admin SIPALA. Jika Anda masih mahasiswa aktif, masuk menggunakan NIM dan kata sandi SSO Portal Kampus.'],
            ]);
        }

        if ($registration->isRejected()) {
            RateLimiter::hit($key, self::LOCKOUT_DECAY_SECONDS);
            if (RateLimiter::tooManyAttempts($key, self::MAX_LOGIN_ATTEMPTS)) {
                $this->ensureIsNotRateLimited($request);
            }
            throw ValidationException::withMessages([
                'login' => ["Pengajuan akun alumni Anda ditolak: {$registration->rejection_reason}. Silakan ajukan pendaftaran ulang melalui halaman Daftar Alumni."],
            ]);
        }
    }

    /**
     * Kunci unik RateLimiter berdasarkan kombinasi input login + alamat IP.
     */
    protected function throttleKey(Request $request): string
    {
        return Str::transliterate(Str::lower(trim((string) $request->input('login'))) . '|' . $request->ip());
    }

    /**
     * Pastikan percobaan login tidak melebihi batas maksimal (Anti Brute-Force).
     */
    protected function ensureIsNotRateLimited(Request $request): void
    {
        $key = $this->throttleKey($request);

        if (!RateLimiter::tooManyAttempts($key, self::MAX_LOGIN_ATTEMPTS)) {
            return;
        }

        $seconds = RateLimiter::availableIn($key);
        $minutes = (int) floor($seconds / 60);
        $remainingSeconds = $seconds % 60;

        $timeText = $minutes > 0
            ? "{$minutes} menit {$remainingSeconds} detik"
            : "{$remainingSeconds} detik";

        throw ValidationException::withMessages([
            'login' => ["Terlalu banyak percobaan login yang gagal (maksimal " . self::MAX_LOGIN_ATTEMPTS . " kali). Demi keamanan akun, silakan tunggu {$timeText} sebelum mencoba kembali."],
        ]);
    }

    /**
     * Handle login berhasil dari Bridge API.
     *
     * Setelah Bridge API memberikan token, ambil profil user,
     * lalu cari/buat user di DB lokal dan login ke session.
     */
    protected function handleBridgeLogin(Request $request, array $bridgeData, string $username, string $password)
    {
        // Ambil token dari response Bridge API
        // Response bisa di: data.token atau langsung token
        $token = data_get($bridgeData, 'data.token')
              ?? data_get($bridgeData, 'token');

        if (!$token) {
            Log::error('Bridge API login response tidak mengandung token', [
                'username' => $username,
                'response' => $bridgeData,
            ]);

            throw ValidationException::withMessages([
                'login' => ['Terjadi kesalahan saat menghubungi portal kampus. Silakan coba lagi.'],
            ]);
        }

        // Tolak role 'orangtua' / 'wali' karena SIPALA hanya untuk Mahasiswa, Dosen/Pegawai, dan Alumni
        $loginRole = strtolower(trim((string) (data_get($bridgeData, 'data.role') ?? data_get($bridgeData, 'role') ?? '')));
        if (str_contains($loginRole, 'orangtua') || str_contains($loginRole, 'orang_tua') || str_contains($loginRole, 'wali')) {
            $this->bridge->logout($token);
            throw ValidationException::withMessages([
                'login' => ['Akses SIPALA dikhususkan untuk Mahasiswa, Dosen/Pegawai, dan Alumni. Akun Portal Orang Tua/Wali tidak dapat digunakan.'],
            ]);
        }

        // Ambil profil dari Bridge API
        $profilResponse = $this->bridge->getProfil($token);
        $profil = data_get($profilResponse, 'data', $profilResponse);

        // Cari atau buat user di database lokal (sertakan payload login untuk deteksi role & fallback nama)
        $user = $this->findOrCreateUserFromBridge($profil, $username, $password, $token, $bridgeData);

        if (!$user) {
            throw ValidationException::withMessages([
                'login' => ['Gagal menyinkronkan data dari portal kampus.'],
            ]);
        }

        // Check if user is active
        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'login' => ['Akun Anda tidak aktif. Silakan hubungi administrator.'],
            ]);
        }

        // Bersihkan penghitung percobaan gagal setelah login berhasil
        RateLimiter::clear($this->throttleKey($request));

        // Login user ke session Laravel
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();
        $request->session()->forget('url.intended');

        // Simpan bridge token ke session (untuk API calls lanjutan)
        session(['bridge_token' => $token]);

        // Update last login
        $user->update(['last_login_at' => now()]);

        // Log activity
        ActivityLog::log(
            userId: $user->id,
            action: ActivityLog::ACTION_LOGIN,
            module: ActivityLog::MODULE_AUTH,
            description: 'Login berhasil via Bridge API Portal Kampus'
        );

        return redirect()->route('dashboard');
    }

    /**
     * Fallback: Login menggunakan credentials lokal.
     *
     * Digunakan untuk ADMIN/STAFF yang tidak memiliki akun portal kampus,
     * atau ketika Bridge API sedang down.
     */
    protected function handleLocalLogin(Request $request, string $username, string $password)
    {
        $key = $this->throttleKey($request);

        // Try login with email or nomor_identitas
        $loginField = filter_var($username, FILTER_VALIDATE_EMAIL) ? 'email' : 'nomor_identitas';

        $credentials = [
            $loginField => $username,
            'password'  => $password,
        ];

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            // Catat percobaan gagal (decay: 15 menit = 900 detik)
            RateLimiter::hit($key, self::LOCKOUT_DECAY_SECONDS);

            if (RateLimiter::tooManyAttempts($key, self::MAX_LOGIN_ATTEMPTS)) {
                Log::warning('Proteksi Brute-Force aktif: akun/IP dikunci sementara 15 menit', [
                    'login' => $username,
                    'ip'    => $request->ip(),
                ]);
                $this->ensureIsNotRateLimited($request);
            }

            $remaining = RateLimiter::remaining($key, self::MAX_LOGIN_ATTEMPTS);

            throw ValidationException::withMessages([
                'login' => ["Username/NIM/NIP atau kata sandi salah. (Sisa percobaan: {$remaining} kali sebelum dikunci 15 menit)"],
            ]);
        }

        $user = Auth::user();
        $user->loadMissing('userType');

        // Batasi autentikasi lokal HANYA untuk manajemen internal (SUPER_ADMIN/ADMIN/STAFF)
        // atau akun ALUMNI terverifikasi yang memiliki auth_source = LOCAL
        $isAllowedLocalAccount = $user->isSuperAdmin()
            || $user->isAdmin()
            || $user->isStaff()
            || ($user->isAlumni() && $user->isLocalAuth());

        if (!$isAllowedLocalAccount) {
            Auth::logout();
            RateLimiter::hit($key, self::LOCKOUT_DECAY_SECONDS);
            throw ValidationException::withMessages([
                'login' => ['Autentikasi akun Mahasiswa/Dosen wajib melalui SSO Portal Kampus.'],
            ]);
        }

        // Check if user is active
        if (!$user->is_active) {
            Auth::logout();
            throw ValidationException::withMessages([
                'login' => ['Akun Anda tidak aktif. Silakan hubungi administrator.'],
            ]);
        }

        // Bersihkan penghitung percobaan gagal setelah login berhasil
        RateLimiter::clear($key);

        // Auth::attempt() sudah memigrasikan ID sesi secara otomatis;
        // perbarui token CSRF dan bersihkan intended URL yang basi.
        $request->session()->regenerateToken();
        $request->session()->forget('url.intended');

        // Update last login
        $user->update(['last_login_at' => now()]);

        // Log activity
        ActivityLog::log(
            userId: $user->id,
            action: ActivityLog::ACTION_LOGIN,
            module: ActivityLog::MODULE_AUTH,
            description: 'Login berhasil via autentikasi lokal'
        );

        return redirect()->route('dashboard');
    }

    /**
     * Cari user di DB lokal berdasarkan data profil dari Bridge API.
     * Jika belum ada, buat user baru (auto-register).
     *
     * Mapping data profil Bridge API → tabel users:
     *   - nama           → nama
     *   - email          → email
     *   - nim/nip/nik    → nomor_identitas
     *   - role/jenis     → user_type_id (mapping ke user_types)
     */
    protected function findOrCreateUserFromBridge(?array $profil, string $username, string $password, string $token, ?array $bridgeData = null): ?User
    {
        try {
            // Cari user berdasarkan nomor_identitas (NIM/NIP/NIK)
            // Prioritas: data dari profil (nim/nip), fallback ke data login, lalu username input
            $nomorIdentitas = data_get($profil, 'nim')
                           ?? data_get($profil, 'nip')
                           ?? data_get($profil, 'nik')
                           ?? data_get($profil, 'nomor_identitas')
                           ?? data_get($profil, 'username')
                           ?? data_get($bridgeData, 'data.username')
                           ?? $username;

            $nomorIdentitas = trim((string) $nomorIdentitas);
            $user = User::where('nomor_identitas', $nomorIdentitas)->first();

            // Tentukan user type dari response login Bridge API v2 (data.role) dan profil Bridge API
            $userTypeName = $this->mapBridgeRoleToUserType($profil, $bridgeData);
            $userType = UserType::where('name', $userTypeName)->first();

            if (!$userType) {
                // Default ke MAHASISWA jika role tidak dikenali
                $userType = UserType::where('name', UserType::MAHASISWA)->first();
            }

            // Ambil data nama (sertakan gelar depan/belakang untuk Dosen jika tersedia)
            $nama = $this->extractBridgeName($profil, $bridgeData, $username);

            // Ambil email yang valid (abaikan nilai "-" dari email_kampus pada Bridge API v2)
            $email = $this->extractBridgeEmail($profil, $nomorIdentitas, $user?->id);

            // Ambil nomor HP jika tersedia di profil Bridge API v2
            $rawHp = trim((string) (data_get($profil, 'no_hp') ?? data_get($profil, 'no_telp') ?? ''));
            $noHp = ($rawHp !== '' && $rawHp !== '-') ? $rawHp : null;

            // Pencocokan program studi dari profil Bridge API v2
            $prodiId = $this->findProgramStudyId($profil);

            if ($user) {
                $user->loadMissing('userType');

                // Jangan pernah menimpa role/password akun manajemen internal maupun alumni lokal terverifikasi
                $keepExistingLocalAccount = $user->isSuperAdmin()
                    || $user->isAdmin()
                    || $user->isStaff()
                    || ($user->isAlumni() && $user->isLocalAuth());

                if ($keepExistingLocalAccount) {
                    return $user;
                }

                // Update data profil dari Bridge API untuk pengguna SSO (Mahasiswa / Dosen)
                $user->update([
                    'nama'             => $nama,
                    'email'            => $email,
                    'no_hp'            => $noHp ?? $user->no_hp,
                    'program_study_id' => $prodiId ?? $user->program_study_id,
                    'user_type_id'     => $userType->id,
                    'auth_source'      => User::AUTH_SOURCE_BRIDGE,
                    'password'         => Hash::make($password), // Sync password lokal
                ]);

                Log::info('User profil disinkronkan dari Bridge API', [
                    'user_id'          => $user->id,
                    'nomor_identitas'  => $nomorIdentitas,
                    'user_type'        => $userTypeName,
                    'program_study_id' => $user->program_study_id,
                ]);

                return $user;
            }

            // Buat user baru (auto-register)
            $user = User::create([
                'user_type_id'     => $userType->id,
                'program_study_id' => $prodiId,
                'nama'             => $nama,
                'email'            => $email,
                'nomor_identitas'  => $nomorIdentitas,
                'no_hp'            => $noHp,
                'password'         => Hash::make($password),
                'auth_source'      => User::AUTH_SOURCE_BRIDGE,
                'is_active'        => true,
            ]);

            Log::info('User baru dibuat dari Bridge API (auto-register)', [
                'user_id'          => $user->id,
                'nomor_identitas'  => $nomorIdentitas,
                'user_type'        => $userTypeName,
            ]);

            return $user;

        } catch (\Exception $e) {
            Log::error('Error saat findOrCreateUserFromBridge', [
                'username' => $username,
                'error'    => $e->getMessage(),
                'trace'    => $e->getTraceAsString(),
            ]);

            return null;
        }
    }

    /**
     * Ekstrak nama lengkap dari profil Bridge API v2 (termasuk gelar depan/belakang untuk Dosen).
     */
    protected function extractBridgeName(?array $profil, ?array $bridgeData, string $fallbackUsername): string
    {
        $baseName = trim((string) (
            data_get($profil, 'nama')
            ?? data_get($profil, 'nama_lengkap')
            ?? data_get($profil, 'name')
            ?? data_get($bridgeData, 'data.nama')
            ?? data_get($bridgeData, 'nama')
            ?? $fallbackUsername
        ));

        $gelarDepan = trim((string) data_get($profil, 'gelar_depan', ''));
        $gelarBelakang = trim((string) data_get($profil, 'gelar_belakang', ''));

        if ($gelarDepan !== '' && $gelarDepan !== '-' && !str_starts_with($baseName, $gelarDepan)) {
            $baseName = "{$gelarDepan} {$baseName}";
        }

        if ($gelarBelakang !== '' && $gelarBelakang !== '-' && !str_ends_with($baseName, $gelarBelakang)) {
            $baseName = "{$baseName}, {$gelarBelakang}";
        }

        return $baseName;
    }

    /**
     * Ekstrak email valid dari profil Bridge API v2 (mengabaikan placeholder "-" atau email kosong).
     */
    protected function extractBridgeEmail(?array $profil, string $nomorIdentitas, ?int $ignoreUserId = null): string
    {
        $candidates = [
            data_get($profil, 'email'),
            data_get($profil, 'email_kampus'),
        ];

        $fallbackEmail = "{$nomorIdentitas}@uinssc.ac.id";
        $selectedEmail = $fallbackEmail;

        foreach ($candidates as $candidate) {
            $candidate = strtolower(trim((string) $candidate));
            if ($candidate !== '' && $candidate !== '-' && filter_var($candidate, FILTER_VALIDATE_EMAIL)) {
                $selectedEmail = $candidate;
                break;
            }
        }

        // Pastikan tidak menabrak email milik user lain di tabel users
        $emailTaken = User::whereRaw('LOWER(email) = ?', [strtolower($selectedEmail)])
            ->when($ignoreUserId, fn($q) => $q->where('id', '!=', $ignoreUserId))
            ->exists();

        return $emailTaken ? $fallbackEmail : $selectedEmail;
    }

    /**
     * Mapping role/jenis dari Bridge API v2 ke UserType SIPALA.
     *
     * Memeriksa field `data.role` dari response login (`POST /api/v2/portal/login`)
     * serta field dari response profil (`GET /api/v2/profil`).
     */
    protected function mapBridgeRoleToUserType(?array $profil, ?array $bridgeData = null): string
    {
        $role = data_get($bridgeData, 'data.role')
             ?? data_get($bridgeData, 'role')
             ?? data_get($profil, 'role')
             ?? data_get($profil, 'jenis')
             ?? data_get($profil, 'jenis_user')
             ?? data_get($profil, 'tipe')
             ?? data_get($profil, 'status')
             ?? '';

        $role = strtoupper(trim((string) $role));

        // Jika di profil terdapat field 'nip' atau 'nidn', maka pasti Dosen/Pegawai
        $hasNipOrNidn = !empty(data_get($profil, 'nip')) || !empty(data_get($profil, 'nidn'));

        // Demi keamanan (Anti-Privilege Escalation), Bridge API hanya diizinkan memetakan ke role sivitas pemohon.
        // Role manajemen internal (SUPER_ADMIN, ADMIN, STAFF) hanya dapat dibuat/diatur dari database internal SIPALA.
        return match (true) {
            str_contains($role, 'DOSEN'),
            str_contains($role, 'PEGAWAI'),
            str_contains($role, 'TENDIK'),
            $hasNipOrNidn                                         => UserType::DOSEN_PEGAWAI,
            str_contains($role, 'ALUMNI')                         => UserType::ALUMNI,
            str_contains($role, 'MAHASISWA')                      => UserType::MAHASISWA,
            default                                               => UserType::MAHASISWA,
        };
    }

    /**
     * Cari program_study_id dari data profil Bridge API v2 & v1.
     *
     * Mendukung struktur JSON asli Bridge API v2:
     *   - Mahasiswa: `program_studi_kode` ("105"), `program_studi_nama` ("TADRIS MATEMATIKA"), `jenjang` ("S1")
     *   - Dosen:     `program_studi` ("S1 - INFORMATIKA")
     */
    protected function findProgramStudyId(?array $profil): ?int
    {
        if (!$profil) {
            return null;
        }

        // 1. Cek kecocokan langsung berdasarkan kode prodi jika sama dengan kolom code
        $prodiKode = trim((string) (
            data_get($profil, 'program_studi_kode')
            ?? data_get($profil, 'prodi_kode')
            ?? data_get($profil, 'kode_prodi')
            ?? data_get($profil, 'program_studi.kode')
            ?? ''
        ));

        if ($prodiKode !== '') {
            $byCode = \App\Models\ProgramStudy::where('code', $prodiKode)->first();
            if ($byCode) {
                return $byCode->id;
            }
        }

        // 2. Ekstrak nama prodi & jenjang dari profil Mahasiswa atau Dosen
        $prodiNama = trim((string) (
            data_get($profil, 'program_studi_nama')
            ?? data_get($profil, 'nama_prodi')
            ?? ''
        ));
        $jenjang = strtoupper(trim((string) data_get($profil, 'jenjang', '')));

        // Pada profil Dosen, field `program_studi` berformat "S1 - INFORMATIKA"
        if ($prodiNama === '') {
            $rawProdiDosen = trim((string) data_get($profil, 'program_studi', ''));
            if ($rawProdiDosen !== '') {
                if (preg_match('/^(S1|S2|S3|PJJ|PPG|PROFESI)\s*-\s*(.+)$/i', $rawProdiDosen, $matches)) {
                    $jenjang = $jenjang ?: strtoupper($matches[1]);
                    $prodiNama = trim($matches[2]);
                } else {
                    $prodiNama = $rawProdiDosen;
                }
            }
        }

        if ($jenjang === 'PROFESI') {
            $jenjang = 'PPG';
        }

        if ($prodiNama === '') {
            return null;
        }

        $normalize = static function (string $value): string {
            $value = strtolower(trim($value));
            $value = preg_replace('/^prodi\s+/i', '', $value);
            $value = str_replace(['/', '(', ')', '-', '.'], ' ', $value);
            $value = preg_replace('/\bdan\b/', ' ', $value);
            return trim(preg_replace('/\s+/', ' ', $value));
        };

        $targetNorm = $normalize($prodiNama);
        $activeProdis = \App\Models\ProgramStudy::active()->get();

        // Prioritas A: Nama ternormalisasi sama persis + jenjang cocok
        foreach ($activeProdis as $prodi) {
            $levelMatch = ($jenjang === '') || (strtoupper($prodi->level) === $jenjang);
            if ($levelMatch && $normalize($prodi->name) === $targetNorm) {
                return $prodi->id;
            }
        }

        // Prioritas B: Nama diawali / mengandung nama prodi dari API (misal "Hukum Keluarga" -> "Hukum Keluarga/Akhwal Syaksiyah")
        foreach ($activeProdis as $prodi) {
            $levelMatch = ($jenjang === '') || (strtoupper($prodi->level) === $jenjang);
            $candidateNorm = $normalize($prodi->name);
            if ($levelMatch && (str_starts_with($candidateNorm, $targetNorm) || str_starts_with($targetNorm, $candidateNorm))) {
                return $prodi->id;
            }
        }

        // Prioritas C: Tanpa filter jenjang jika jenjang di API berbeda penulisan (misal PJJ)
        foreach ($activeProdis as $prodi) {
            $candidateNorm = $normalize($prodi->name);
            if ($candidateNorm === $targetNorm || str_starts_with($candidateNorm, $targetNorm)) {
                return $prodi->id;
            }
        }

        return null;
    }

    /**
     * Handle logout.
     *
     * Logout dari session Laravel DAN dari Bridge API (revoke token).
     */
    public function logout(Request $request)
    {
        // Revoke token di Bridge API jika ada
        $bridgeToken = session('bridge_token');
        if ($bridgeToken) {
            $this->bridge->logout($bridgeToken);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
