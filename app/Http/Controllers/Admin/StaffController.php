<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Service;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    /**
     * Display a listing of staff and their specialties.
     */
    public function index(Request $request)
    {
        $staffTypeId = UserType::where('name', UserType::STAFF)->value('id');

        $query = User::where('user_type_id', $staffTypeId)
            ->with(['staffSpecialties.category', 'assignedTickets'])
            ->withCount('assignedTickets');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('nomor_identitas', 'like', "%{$search}%");
            });
        }

        if ($request->filled('service_id')) {
            $query->whereHas('staffSpecialties', function ($q) use ($request) {
                $q->where('services.id', $request->service_id);
            });
        }

        $staffList = $query->orderBy('nama')->paginate(15)->withQueryString();
        $services = Service::active()->orderBy('name')->get();

        return view('admin.staff.index', compact('staffList', 'services'));
    }

    /**
     * Show form for creating a new staff member.
     */
    public function create()
    {
        $categories = Category::with(['services' => function ($q) {
            $q->active()->orderBy('name');
        }])->active()->orderBy('sort_order')->get();

        return view('admin.staff.create', compact('categories'));
    }

    /**
     * Store a newly created staff member.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'nomor_identitas' => 'required|string|max:50|unique:users,nomor_identitas',
            'password' => 'required|string|min:6|confirmed',
            'specialties' => 'nullable|array',
            'specialties.*' => 'exists:services,id',
        ]);

        $staffType = UserType::where('name', UserType::STAFF)->firstOrFail();

        $user = User::create([
            'user_type_id' => $staffType->id,
            'nama' => $request->nama,
            'email' => $request->email,
            'nomor_identitas' => $request->nomor_identitas,
            'password' => Hash::make($request->password),
            'auth_source' => User::AUTH_SOURCE_LOCAL,
            'is_active' => $request->has('is_active'),
        ]);

        if ($request->has('specialties')) {
            $user->staffSpecialties()->sync($request->specialties);
        }

        return redirect()->route('admin.staff.index')
            ->with('success', "Staff {$user->nama} berhasil ditambahkan beserta spesialisasi layanannya.");
    }

    /**
     * Pastikan target akun benar-benar memiliki role STAFF (Anti-Privilege Escalation & Anti-IDOR).
     */
    protected function ensureIsStaffUser(User $staff): void
    {
        if (!$staff->isStaff()) {
            abort(403, 'Akses ditolak: Akun target bukan Staf Pelaksana.');
        }
    }

    /**
     * Show the form for editing the specified staff member.
     */
    public function edit(User $staff)
    {
        $this->ensureIsStaffUser($staff);

        $categories = Category::with(['services' => function ($q) {
            $q->active()->orderBy('name');
        }])->active()->orderBy('sort_order')->get();

        $staffSpecialtyIds = $staff->staffSpecialties()->pluck('services.id')->toArray();

        return view('admin.staff.edit', compact('staff', 'categories', 'staffSpecialtyIds'));
    }

    /**
     * Update the specified staff member in storage.
     */
    public function update(Request $request, User $staff)
    {
        $this->ensureIsStaffUser($staff);

        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($staff->id)],
            'nomor_identitas' => ['required', 'string', 'max:50', Rule::unique('users')->ignore($staff->id)],
            'password' => 'nullable|string|min:6|confirmed',
            'specialties' => 'nullable|array',
            'specialties.*' => 'exists:services,id',
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

        $staff->update($data);

        // Sync service specialties
        $specialties = $request->input('specialties', []);
        $staff->staffSpecialties()->sync($specialties);

        return redirect()->route('admin.staff.index')
            ->with('success', "Data dan spesialisasi staff {$staff->nama} berhasil diperbarui.");
    }

    /**
     * Remove the specified staff member from storage.
     */
    public function destroy(User $staff)
    {
        $this->ensureIsStaffUser($staff);

        $hasHistory = $staff->assignedTickets()->exists()
            || \App\Models\TicketStatusHistory::where('changed_by', $staff->id)->exists()
            || \App\Models\TicketMessage::where('user_id', $staff->id)->exists()
            || \App\Models\ActivityLog::where('user_id', $staff->id)->exists()
            || \App\Models\EmailNotification::where('user_id', $staff->id)->exists()
            || $staff->tickets()->exists();

        if ($hasHistory) {
            // Jika memiliki riwayat tiket/audit trail, nonaktifkan akun agar integritas log terjaga
            $staff->update(['is_active' => false]);
            return redirect()->route('admin.staff.index')
                ->with('info', "Staff {$staff->nama} memiliki riwayat aktivitas/tiket, status diubah menjadi Nonaktif agar jejak audit tetap terjaga.");
        }

        try {
            $staff->staffSpecialties()->detach();
            $staff->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            $staff->update(['is_active' => false]);
            return redirect()->route('admin.staff.index')
                ->with('info', "Staff {$staff->nama} memiliki riwayat data terkait, status diubah menjadi Nonaktif.");
        }

        return redirect()->route('admin.staff.index')
            ->with('success', "Staff {$staff->nama} berhasil dihapus.");
    }
}
