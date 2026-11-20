<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Service;
use App\Models\ServiceUnit;
use App\Models\UserType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServiceController extends Controller
{
    /**
     * Display a listing of the services.
     */
    public function index()
    {
        $services = Service::with(['category', 'unit'])->withCount(['targets', 'requirements', 'fields'])->orderBy('category_id')->orderBy('sort_order')->get();
        return view('admin.services.index', compact('services'));
    }

    /**
     * Show the form for creating a new service.
     */
    public function create()
    {
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();
        $units = ServiceUnit::where('is_active', true)->orderBy('name')->get();
        $userTypes = UserType::where('is_active', true)->whereNotIn('name', [UserType::SUPER_ADMIN, UserType::ADMIN, UserType::STAFF])->get();
        
        return view('admin.services.create', compact('categories', 'units', 'userTypes'));
    }

    /**
     * Store a newly created service in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'unit_id' => 'nullable|exists:service_units,id',
            'description' => 'nullable|string',
            'sla_duration' => 'required|integer|min:1',
            'sla_unit' => 'required|in:jam,hari,hari_kerja',
            'sort_order' => 'nullable|integer',
            'targets' => 'required|array|min:1',
            'targets.*' => 'exists:user_types,id',
        ]);

        try {
            DB::beginTransaction();

            $data = $request->except('targets');
            $data['is_active'] = $request->has('is_active');
            $data['sort_order'] = $request->sort_order ?? (Service::where('category_id', $request->category_id)->max('sort_order') + 1);

            $service = Service::create($data);

            // Sync targets (hanya izinkan end-user: MAHASISWA, ALUMNI, DOSEN_PEGAWAI)
            $allowedTargetIds = UserType::whereNotIn('name', [UserType::SUPER_ADMIN, UserType::ADMIN, UserType::STAFF])
                ->whereIn('id', $request->targets)
                ->pluck('id');

            foreach ($allowedTargetIds as $targetId) {
                $service->targets()->create(['user_type_id' => $targetId]);
            }

            DB::commit();
            return redirect()->route('admin.services.index')->with('success', 'Layanan berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Show the form for editing the specified service.
     */
    public function edit(Service $service)
    {
        $service->load(['targets', 'requirements', 'fields']);
        
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();
        $units = ServiceUnit::where('is_active', true)->orderBy('name')->get();
        $userTypes = UserType::where('is_active', true)->whereNotIn('name', [UserType::SUPER_ADMIN, UserType::ADMIN, UserType::STAFF])->get();
        
        $selectedTargets = $service->targets->pluck('user_type_id')->toArray();

        return view('admin.services.edit', compact('service', 'categories', 'units', 'userTypes', 'selectedTargets'));
    }

    /**
     * Update the specified service in storage.
     */
    public function update(Request $request, Service $service)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'unit_id' => 'nullable|exists:service_units,id',
            'description' => 'nullable|string',
            'sla_duration' => 'required|integer|min:1',
            'sla_unit' => 'required|in:jam,hari,hari_kerja',
            'sort_order' => 'nullable|integer',
            'targets' => 'required|array|min:1',
            'targets.*' => 'exists:user_types,id',
        ]);

        try {
            DB::beginTransaction();

            $data = $request->except('targets');
            $data['is_active'] = $request->has('is_active');

            $service->update($data);

            // Sync targets (hanya izinkan end-user: MAHASISWA, ALUMNI, DOSEN_PEGAWAI)
            $allowedTargetIds = UserType::whereNotIn('name', [UserType::SUPER_ADMIN, UserType::ADMIN, UserType::STAFF])
                ->whereIn('id', $request->targets)
                ->pluck('id');

            $service->targets()->delete();
            foreach ($allowedTargetIds as $targetId) {
                $service->targets()->create(['user_type_id' => $targetId]);
            }

            DB::commit();
            return redirect()->route('admin.services.index')->with('success', 'Layanan berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified service from storage.
     */
    public function destroy(Service $service)
    {
        if ($service->tickets()->exists()) {
            return redirect()->route('admin.services.index')->with('error', 'Layanan tidak dapat dihapus karena sudah memiliki tiket pengaduan. Silakan nonaktifkan layanan ini.');
        }

        $service->delete();

        return redirect()->route('admin.services.index')->with('success', 'Layanan berhasil dihapus.');
    }
}
