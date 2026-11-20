<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceField;
use Illuminate\Http\Request;

class ServiceFieldController extends Controller
{
    /**
     * Store a newly created field in storage.
     */
    public function store(Request $request, Service $service)
    {
        $request->validate([
            'label' => 'required|string|max:255',
            'field_type' => 'required|in:text,textarea,number,date,select,radio,checkbox',
            'options' => 'required_if:field_type,select,radio,checkbox|nullable|string', // Comma separated for MVP
            'placeholder' => 'nullable|string',
            'is_required' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $data = $request->only(['label', 'field_type', 'options', 'placeholder', 'sort_order']);
        $data['is_required'] = $request->has('is_required');
        $data['sort_order'] = $request->sort_order ?? ($service->fields()->max('sort_order') + 1);

        // Convert options string to array if applicable (casted to JSON by ServiceField model)
        if (in_array($request->field_type, ['select', 'radio', 'checkbox']) && !empty($request->options)) {
            $optionsArray = array_values(array_filter(array_map('trim', explode(',', $request->options)), fn($v) => $v !== ''));
            $data['options'] = $optionsArray;
        } else {
            $data['options'] = null;
        }

        $service->fields()->create($data);

        return redirect()->route('admin.services.edit', $service)->with('success', 'Custom field berhasil ditambahkan.');
    }

    /**
     * Update the specified field in storage.
     */
    public function update(Request $request, Service $service, ServiceField $field)
    {
        if ($field->service_id !== $service->id) {
            abort(403, 'Field tidak sesuai dengan layanan target.');
        }

        $request->validate([
            'label' => 'required|string|max:255',
            'field_type' => 'required|in:text,textarea,number,date,select,radio,checkbox',
            'options' => 'required_if:field_type,select,radio,checkbox|nullable|string',
            'placeholder' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);

        $data = $request->only(['label', 'field_type', 'options', 'placeholder', 'sort_order']);
        $data['is_required'] = $request->has('is_required');

        if (in_array($request->field_type, ['select', 'radio', 'checkbox']) && !empty($request->options)) {
            $optionsArray = array_values(array_filter(array_map('trim', explode(',', $request->options)), fn($v) => $v !== ''));
            $data['options'] = $optionsArray;
        } else {
            $data['options'] = null;
        }

        $field->update($data);

        return redirect()->route('admin.services.edit', $service)->with('success', 'Custom field berhasil diperbarui.');
    }

    /**
     * Remove the specified field from storage.
     */
    public function destroy(Service $service, ServiceField $field)
    {
        if ($field->service_id !== $service->id) {
            abort(403, 'Field tidak sesuai dengan layanan target.');
        }
        if ($field->ticketValues()->exists()) {
            return redirect()->route('admin.services.edit', $service)->with('error', 'Field tidak dapat dihapus karena sudah memiliki data dari tiket yang diajukan.');
        }

        $field->delete();

        return redirect()->route('admin.services.edit', $service)->with('success', 'Custom field berhasil dihapus.');
    }
}
