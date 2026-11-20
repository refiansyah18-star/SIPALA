<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AlumniRegistration;
use App\Models\User;
use App\Models\UserType;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AlumniVerificationController extends Controller
{
    public function __construct(protected NotificationService $notifications)
    {
    }

    /**
     * Tampilkan antrean verifikasi alumni dan daftar akun alumni aktif.
     */
    public function index(Request $request)
    {
        $tab = $request->input('tab', 'pending');
        if (!in_array($tab, ['pending', 'approved', 'rejected', 'accounts'], true)) {
            $tab = 'pending';
        }

        $search = trim((string) $request->input('search', ''));

        $alumniTypeId = UserType::where('name', UserType::ALUMNI)->value('id');

        $counts = [
            'pending'  => AlumniRegistration::where('status', AlumniRegistration::STATUS_PENDING)->count(),
            'approved' => AlumniRegistration::where('status', AlumniRegistration::STATUS_APPROVED)->count(),
            'rejected' => AlumniRegistration::where('status', AlumniRegistration::STATUS_REJECTED)->count(),
            'accounts' => User::where('user_type_id', $alumniTypeId)->count(),
        ];

        $registrations = null;
        $alumniAccounts = null;
        $existingStudentNims = collect();

        if ($tab === 'accounts') {
            $query = User::where('user_type_id', $alumniTypeId)
                ->with('programStudy')
                ->withCount('tickets');

            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                      ->orWhere('nomor_identitas', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('no_hp', 'like', "%{$search}%");
                });
            }

            $alumniAccounts = $query->orderBy('nama')->paginate(15)->withQueryString();
        } else {
            $statusMap = [
                'pending'  => AlumniRegistration::STATUS_PENDING,
                'approved' => AlumniRegistration::STATUS_APPROVED,
                'rejected' => AlumniRegistration::STATUS_REJECTED,
            ];

            $query = AlumniRegistration::with(['programStudy', 'reviewer', 'user'])
                ->where('status', $statusMap[$tab])
                ->latest();

            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                      ->orWhere('nim', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('no_hp', 'like', "%{$search}%");
                });
            }

            $registrations = $query->paginate(15)->withQueryString();

            if ($tab === 'pending' && $registrations->isNotEmpty()) {
                $nims = $registrations->pluck('nim')->unique()->values();
                $mahasiswaTypeId = UserType::where('name', UserType::MAHASISWA)->value('id');
                $existingStudentNims = User::where('user_type_id', $mahasiswaTypeId)
                    ->whereIn('nomor_identitas', $nims)
                    ->pluck('nomor_identitas')
                    ->flip();
            }
        }

        return view('admin.alumni.index', compact(
            'tab',
            'search',
            'counts',
            'registrations',
            'alumniAccounts',
            'existingStudentNims'
        ));
    }

    /**
     * Tampilkan / unduh dokumen bukti kelulusan dari disk privat ('local').
     */
    public function showProof(AlumniRegistration $registration)
    {
        if (!Storage::disk('local')->exists($registration->proof_file_path)) {
            abort(404, 'Berkas bukti kelulusan tidak ditemukan.');
        }

        $detectedMime = strtolower((string) Storage::disk('local')->mimeType($registration->proof_file_path));
        $safeInlineMimes = ['application/pdf', 'image/jpeg', 'image/png'];
        $headers = [
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "default-src 'none'; style-src 'unsafe-inline';",
        ];

        if (in_array($detectedMime, $safeInlineMimes, true)) {
            return Storage::disk('local')->response(
                $registration->proof_file_path,
                $registration->proof_original_name,
                $headers
            );
        }

        return Storage::disk('local')->download(
            $registration->proof_file_path,
            $registration->proof_original_name,
            $headers
        );
    }

    /**
     * Setujui pengajuan akun alumni: buat akun baru atau upgrade akun MAHASISWA -> ALUMNI.
     */
    public function approve(AlumniRegistration $registration)
    {
        if (!$registration->isPending()) {
            return redirect()
                ->route('admin.alumni.index', ['tab' => strtolower($registration->status)])
                ->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        try {
            $approvedUser = DB::transaction(function () use ($registration) {
                $lockedRegistration = AlumniRegistration::whereKey($registration->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (!$lockedRegistration->isPending()) {
                    throw new \RuntimeException('Pengajuan ini sudah diproses sebelumnya.');
                }

                $alumniType = UserType::where('name', UserType::ALUMNI)->firstOrFail();
                $existingUser = User::where('nomor_identitas', $lockedRegistration->nim)
                    ->with('userType')
                    ->lockForUpdate()
                    ->first();

                if ($existingUser && ($existingUser->isPengelola() || $existingUser->isDosenPegawai())) {
                    throw new \RuntimeException('NIM ini tercatat sebagai akun pegawai/dosen/pengelola dan tidak dapat diubah menjadi alumni.');
                }

                $emailConflict = User::whereRaw('LOWER(email) = ?', [strtolower($lockedRegistration->email)])
                    ->when($existingUser, fn($q) => $q->where('id', '!=', $existingUser->id))
                    ->exists();

                if ($emailConflict) {
                    throw new \RuntimeException('Email pendaftar sudah dipakai oleh akun lain di SIPALA.');
                }

                if ($existingUser) {
                    // Upgrade eks-mahasiswa menjadi ALUMNI (riwayat tiket lama tetap utuh)
                    $existingUser->update([
                        'user_type_id'     => $alumniType->id,
                        'program_study_id' => $lockedRegistration->program_study_id,
                        'nama'             => $lockedRegistration->nama,
                        'email'            => $lockedRegistration->email,
                        'no_hp'            => $lockedRegistration->no_hp,
                        'tahun_lulus'      => $lockedRegistration->tahun_lulus,
                        'password'         => $lockedRegistration->password,
                        'auth_source'      => User::AUTH_SOURCE_LOCAL,
                        'is_active'        => true,
                    ]);
                    $user = $existingUser;
                } else {
                    $user = User::create([
                        'user_type_id'     => $alumniType->id,
                        'program_study_id' => $lockedRegistration->program_study_id,
                        'nama'             => $lockedRegistration->nama,
                        'email'            => $lockedRegistration->email,
                        'nomor_identitas'  => $lockedRegistration->nim,
                        'no_hp'            => $lockedRegistration->no_hp,
                        'tahun_lulus'      => $lockedRegistration->tahun_lulus,
                        'password'         => $lockedRegistration->password,
                        'auth_source'      => User::AUTH_SOURCE_LOCAL,
                        'is_active'        => true,
                    ]);
                }

                $lockedRegistration->update([
                    'status'      => AlumniRegistration::STATUS_APPROVED,
                    'reviewed_by' => auth()->id(),
                    'reviewed_at' => now(),
                    'user_id'     => $user->id,
                ]);

                ActivityLog::log(
                    userId: auth()->id(),
                    action: ActivityLog::ACTION_VERIFY,
                    module: ActivityLog::MODULE_ALUMNI,
                    referenceId: $lockedRegistration->id,
                    description: "Menyetujui pendaftaran alumni {$lockedRegistration->nama} (NIM: {$lockedRegistration->nim})"
                );

                return $user;
            });
        } catch (\RuntimeException $e) {
            return redirect()
                ->route('admin.alumni.index', ['tab' => 'pending'])
                ->with('error', $e->getMessage());
        }

        $registration->refresh();
        $this->notifications->sendAlumniRegistrationApproved($registration, $approvedUser);

        return redirect()
            ->route('admin.alumni.index', ['tab' => 'pending'])
            ->with('success', "Akun alumni {$registration->nama} (NIM: {$registration->nim}) berhasil disetujui dan diaktifkan.");
    }

    /**
     * Tolak pengajuan akun alumni beserta alasan penolakan yang jelas.
     */
    public function reject(Request $request, AlumniRegistration $registration)
    {
        if (!$registration->isPending()) {
            return redirect()
                ->route('admin.alumni.index', ['tab' => strtolower($registration->status)])
                ->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|min:5|max:1000',
        ], [
            'rejection_reason.required' => 'Alasan penolakan wajib diisi agar pendaftar mengetahui kendalanya.',
            'rejection_reason.min' => 'Alasan penolakan minimal 5 karakter.',
        ]);

        DB::transaction(function () use ($registration, $validated) {
            $locked = AlumniRegistration::whereKey($registration->id)->lockForUpdate()->firstOrFail();

            if (!$locked->isPending()) {
                return;
            }

            $locked->update([
                'status'           => AlumniRegistration::STATUS_REJECTED,
                'rejection_reason' => trim($validated['rejection_reason']),
                'reviewed_by'      => auth()->id(),
                'reviewed_at'      => now(),
            ]);

            ActivityLog::log(
                userId: auth()->id(),
                action: ActivityLog::ACTION_REJECT,
                module: ActivityLog::MODULE_ALUMNI,
                referenceId: $locked->id,
                description: "Menolak pendaftaran alumni {$locked->nama} (NIM: {$locked->nim}): " . trim($validated['rejection_reason'])
            );
        });

        $registration->refresh();
        $this->notifications->sendAlumniRegistrationRejected($registration);

        return redirect()
            ->route('admin.alumni.index', ['tab' => 'pending'])
            ->with('success', "Pengajuan alumni {$registration->nama} berhasil ditolak.");
    }

    /**
     * Aktifkan atau nonaktifkan akun alumni yang sudah terdaftar (Anti-IDOR).
     */
    public function toggleStatus(User $alumni)
    {
        $this->ensureAlumniTarget($alumni);

        $newStatus = !$alumni->is_active;
        $alumni->update([
            'is_active'   => $newStatus,
            'auth_source' => User::AUTH_SOURCE_LOCAL,
        ]);

        ActivityLog::log(
            userId: auth()->id(),
            action: ActivityLog::ACTION_UPDATE,
            module: ActivityLog::MODULE_ALUMNI,
            referenceId: $alumni->id,
            description: ($newStatus ? 'Mengaktifkan' : 'Menonaktifkan') . " akun alumni {$alumni->nama} (NIM: {$alumni->nomor_identitas})"
        );

        return redirect()
            ->route('admin.alumni.index', ['tab' => 'accounts'])
            ->with('success', 'Status akun alumni berhasil ' . ($newStatus ? 'diaktifkan' : 'dinonaktifkan') . '.');
    }

    /**
     * Reset kata sandi akun alumni oleh Admin (bantuan manual bila dibutuhkan).
     */
    public function resetPassword(Request $request, User $alumni)
    {
        $this->ensureAlumniTarget($alumni);

        $validated = $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ], [
            'password.min' => 'Kata sandi baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        $alumni->forceFill([
            'password'       => Hash::make($validated['password']),
            'auth_source'    => User::AUTH_SOURCE_LOCAL,
            'remember_token' => Str::random(60),
        ])->save();

        // Bersihkan lock login yang mungkin tersisa
        RateLimiter::clear(Str::transliterate(Str::lower($alumni->email) . '|' . $request->ip()));
        RateLimiter::clear(Str::transliterate(Str::lower($alumni->nomor_identitas) . '|' . $request->ip()));

        ActivityLog::log(
            userId: auth()->id(),
            action: ActivityLog::ACTION_UPDATE,
            module: ActivityLog::MODULE_ALUMNI,
            referenceId: $alumni->id,
            description: "Admin mereset kata sandi akun alumni {$alumni->nama} (NIM: {$alumni->nomor_identitas})"
        );

        return redirect()
            ->route('admin.alumni.index', ['tab' => 'accounts'])
            ->with('success', "Kata sandi akun alumni {$alumni->nama} berhasil direset.");
    }

    /**
     * Pastikan akun target benar-benar ber-role ALUMNI (Anti-Privilege Escalation & Anti-IDOR).
     */
    protected function ensureAlumniTarget(User $user): void
    {
        if (!$user->isAlumni()) {
            abort(403, 'Akses ditolak: Akun target bukan Alumni.');
        }
    }
}

