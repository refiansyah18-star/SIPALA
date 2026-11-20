<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    /**
     * Display a listing of Admin (Manager) accounts.
     */
    public function index(Request $request)
    {
        $adminTypeId = UserType::where('name', UserType::ADMIN)->value('id');

        $query = User::where('user_type_id', $adminTypeId);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('nomor_identitas', 'like', "%{$search}%");
            });
        }

        $admins = $query->orderBy('nama')->paginate(15);

        return view('admin.admins.index', compact('admins'));
    }

    /**
     * Store a newly created Admin.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'nomor_identitas' => 'required|string|max:50|unique:users,nomor_identitas',
            'password' => 'required|string|min:6',
        ]);

        $adminType = UserType::where('name', UserType::ADMIN)->firstOrFail();

        User::create([
            'user_type_id' => $adminType->id,
            'nama' => $request->nama,
            'email' => $request->email,
            'nomor_identitas' => $request->nomor_identitas,
            'password' => Hash::make($request->password),
            'auth_source' => User::AUTH_SOURCE_LOCAL,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.admins.index')->with('success', 'Admin Manajer baru berhasil ditambahkan.');
    }

    /**
     * Pastikan target akun benar-benar memiliki role ADMIN (Anti-Privilege Escalation & Anti-IDOR).
     */
    protected function ensureIsAdminUser(User $admin): void
    {
        if (!$admin->isAdmin()) {
            abort(403, 'Akses ditolak: Akun target bukan Admin.');
        }
    }

    /**
     * Update the specified Admin.
     */
    public function update(Request $request, User $admin)
    {
        $this->ensureIsAdminUser($admin);

        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($admin->id)],
            'nomor_identitas' => ['required', 'string', 'max:50', Rule::unique('users')->ignore($admin->id)],
            'password' => 'nullable|string|min:6',
        ]);

        $data = [
            'nama' => $request->nama,
            'email' => $request->email,
            'nomor_identitas' => $request->nomor_identitas,
            'is_active' => $request->has('is_active'),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $admin->update($data);

        return redirect()->route('admin.admins.index')->with('success', 'Data Admin berhasil diperbarui.');
    }

    /**
     * Remove or deactivate the specified Admin.
     */
    public function destroy(User $admin)
    {
        $this->ensureIsAdminUser($admin);

        if ($admin->id === auth()->id()) {
            return redirect()->route('admin.admins.index')->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $admin->delete();

        return redirect()->route('admin.admins.index')->with('success', 'Admin berhasil dihapus.');
    }
}
