<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AlumniRegistration;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AlumniProfileController extends Controller
{
    /**
     * Tampilkan halaman profil & pengaturan kata sandi khusus Alumni.
     */
    public function edit()
    {
        $user = $this->ensureAlumniUser(auth()->user());
        $user->load(['userType', 'programStudy']);

        return view('user.alumni_profile', compact('user'));
    }

    /**
     * Perbarui data kontak (email & no. HP) akun Alumni.
     */
    public function updateProfile(Request $request)
    {
        $user = $this->ensureAlumniUser(auth()->user());

        $request->merge([
            'email' => strtolower(trim((string) $request->input('email'))),
            'no_hp' => trim((string) $request->input('no_hp')),
        ]);

        $validated = $request->validate([
            'email' => ['required', 'email:rfc', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'no_hp' => ['required', 'string', 'min:9', 'max:20', 'regex:/^[0-9+\-\s()]+$/'],
        ], [
            'email.unique' => 'Email ini sudah digunakan oleh akun lain di SIPALA.',
            'no_hp.regex' => 'Format nomor HP/WA tidak valid.',
        ]);

        $pendingEmailCollision = AlumniRegistration::where('status', AlumniRegistration::STATUS_PENDING)
            ->whereRaw('LOWER(email) = ?', [$validated['email']])
            ->where('nim', '!=', $user->nomor_identitas)
            ->exists();

        if ($pendingEmailCollision) {
            throw ValidationException::withMessages([
                'email' => ['Email ini sedang digunakan pada pengajuan pendaftaran akun alumni lain.'],
            ]);
        }

        $user->update([
            'email' => $validated['email'],
            'no_hp' => $validated['no_hp'],
        ]);

        ActivityLog::log(
            userId: $user->id,
            action: ActivityLog::ACTION_UPDATE,
            module: ActivityLog::MODULE_USER,
            referenceId: $user->id,
            description: 'Alumni memperbarui informasi kontak profil'
        );

        return redirect()
            ->route('user.alumni-profile.edit')
            ->with('success', 'Informasi kontak profil berhasil diperbarui.');
    }

    /**
     * Ganti kata sandi akun Alumni yang sudah login.
     */
    public function updatePassword(Request $request)
    {
        $user = $this->ensureAlumniUser(auth()->user());

        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
        ], [
            'current_password.current_password' => 'Kata sandi saat ini tidak sesuai.',
            'password.min' => 'Kata sandi baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
            'password.different' => 'Kata sandi baru harus berbeda dari kata sandi saat ini.',
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        $request->session()->regenerate();

        ActivityLog::log(
            userId: $user->id,
            action: ActivityLog::ACTION_UPDATE,
            module: ActivityLog::MODULE_AUTH,
            referenceId: $user->id,
            description: 'Alumni mengubah kata sandi akun melalui halaman profil'
        );

        return redirect()
            ->route('user.alumni-profile.edit')
            ->with('success', 'Kata sandi berhasil diperbarui.');
    }

    /**
     * Pastikan hanya akun Alumni terautentikasi yang bisa mengakses kontroler ini.
     */
    protected function ensureAlumniUser(User $user): User
    {
        if (!$user->isAlumni()) {
            abort(403, 'Halaman profil ini dikhususkan untuk akun Alumni.');
        }

        return $user;
    }
}

