<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceRequirement;
use Illuminate\Http\Request;

class ServiceRequirementController extends Controller
{
    /**
     * Store a newly created requirement in storage.
     */
    public function store(Request $request, Service $service)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_required' => 'boolean',
            'allowed_file_types' => 'required|string', // e.g. "pdf,jpg,png"
            'max_file_size' => 'required|integer|min:100', // in KB
            'max_file_count' => 'required|integer|min:1',
            'sort_order' => 'nullable|integer',
        ]);

        $data = $request->only(['name', 'description', 'allowed_file_types', 'max_file_size', 'max_file_count', 'sort_order']);
        $data['is_required'] = $request->has('is_required');
        $data['sort_order'] = $request->sort_order ?? ($service->requirements()->max('sort_order') + 1);

        $service->requirements()->create($data);

        return redirect()->route('admin.services.edit', $service)->with('success', 'Persyaratan dokumen berhasil ditambahkan.');
    }

    /**
     * Update the specified requirement in storage.
     */
    public function update(Request $request, Service $service, ServiceRequirement $requirement)
    {
        if ($requirement->service_id !== $service->id) {
            abort(403, 'Persyaratan tidak sesuai dengan layanan target.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'allowed_file_types' => 'required|string',
            'max_file_size' => 'required|integer|min:100',
            'max_file_count' => 'required|integer|min:1',
            'sort_order' => 'nullable|integer',
        ]);

        $data = $request->only(['name', 'description', 'allowed_file_types', 'max_file_size', 'max_file_count', 'sort_order']);
        $data['is_required'] = $request->has('is_required');

        $requirement->update($data);

        return redirect()->route('admin.services.edit', $service)->with('success', 'Persyaratan dokumen berhasil diperbarui.');
    }

    /**
     * Remove the specified requirement from storage.
     */
    public function destroy(Service $service, ServiceRequirement $requirement)
    {
        if ($requirement->service_id !== $service->id) {
            abort(403, 'Persyaratan tidak sesuai dengan layanan target.');
        }
        // Don't allow deletion if there are already tickets with this requirement attached
        if ($requirement->attachments()->exists()) {
            return redirect()->route('admin.services.edit', $service)->with('error', 'Persyaratan tidak dapat dihapus karena sudah digunakan pada tiket yang ada.');
        }

        $requirement->delete();

        return redirect()->route('admin.services.edit', $service)->with('success', 'Persyaratan dokumen berhasil dihapus.');
    }
}
