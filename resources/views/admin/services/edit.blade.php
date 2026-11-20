@extends('layouts.app')

@section('title', 'Edit Layanan — ' . $service->name)
@section('page-title', 'Edit Layanan & Persyaratan')

@section('content')
    <div style="max-width: 960px; margin: 0 auto;">
        <div style="margin-bottom: 20px;">
            <a href="{{ route('admin.services.index') }}" class="back-link" style="color: var(--primary); font-size: 14px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; text-decoration: none;">
                <i class="fas fa-arrow-left"></i> Kembali ke Daftar Layanan
            </a>
        </div>

        {{-- 1. Form Utama Informasi Layanan --}}
        <div class="card" style="margin-bottom: 24px;">
            <div class="card-header">
                <h2><i class="fas fa-cogs" style="color: var(--primary); margin-right: 8px;"></i> Informasi Layanan Akademik</h2>
            </div>

            <form action="{{ route('admin.services.update', $service) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">Nama Layanan <span class="required">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $service->name) }}" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label class="form-label">Kategori <span class="required">*</span></label>
                            <select name="category_id" class="form-control @error('category_id') is-invalid @enderror" required>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', $service->category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Unit Pengolah</label>
                            <select name="unit_id" class="form-control @error('unit_id') is-invalid @enderror">
                                <option value="">-- Pilih Unit (Opsional) --</option>
                                @foreach($units as $unit)
                                    <option value="{{ $unit->id }}" {{ old('unit_id', $service->unit_id) == $unit->id ? 'selected' : '' }}>
                                        {{ $unit->name }} ({{ $unit->type }})
                                    </option>
                                @endforeach
                            </select>
                            @error('unit_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Deskripsi Layanan</label>
                        <textarea name="description" class="form-control">{{ old('description', $service->description) }}</textarea>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
                        <div class="form-group" style="grid-column: span 2;">
                            <label class="form-label">SLA (Service Level Agreement) <span class="required">*</span></label>
                            <div style="display: flex; gap: 10px;">
                                <input type="number" name="sla_duration" class="form-control" value="{{ old('sla_duration', $service->sla_duration) }}" required min="1" style="width: 100px;">
                                <select name="sla_unit" class="form-control" required style="flex: 1;">
                                    <option value="jam" {{ old('sla_unit', $service->sla_unit) == 'jam' ? 'selected' : '' }}>Jam</option>
                                    <option value="hari" {{ old('sla_unit', $service->sla_unit) == 'hari' ? 'selected' : '' }}>Hari Kalender</option>
                                    <option value="hari_kerja" {{ old('sla_unit', $service->sla_unit) == 'hari_kerja' ? 'selected' : '' }}>Hari Kerja</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Urutan Tampil</label>
                            <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $service->sort_order) }}">
                        </div>
                    </div>

                    <div class="form-group" style="margin-top: 10px; border-top: 1px solid var(--gray-200); padding-top: 20px;">
                        <label class="form-label">Target Pengguna (Siapa yang bisa mengajukan) <span class="required">*</span></label>
                        <div style="display: flex; gap: 16px; flex-wrap: wrap; margin-top: 10px;">
                            @php
                                $checkedTargets = old('targets', $selectedTargets);
                            @endphp
                            @foreach($userTypes as $type)
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; background: var(--gray-50); padding: 10px 16px; border-radius: 8px; border: 1px solid var(--gray-200);">
                                <input type="checkbox" name="targets[]" value="{{ $type->id }}" style="width: 16px; height: 16px; accent-color: var(--primary);"
                                    {{ in_array($type->id, $checkedTargets) ? 'checked' : '' }}>
                                <span style="font-weight: 500;">{{ $type->label }}</span>
                            </label>
                            @endforeach
                        </div>
                        @error('targets') <div class="invalid-feedback" style="margin-top: 10px;">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group" style="margin-top: 20px; margin-bottom: 0;">
                        <label class="form-label" style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                            <input type="checkbox" name="is_active" value="1" style="width: 18px; height: 18px; accent-color: var(--primary);" {{ old('is_active', $service->is_active) ? 'checked' : '' }}>
                            Layanan Aktif (Bisa dipilih oleh pengguna)
                        </label>
                    </div>
                </div>

                <div style="padding: 16px 24px; background: var(--gray-50); border-top: 1px solid var(--gray-200); display: flex; justify-content: flex-end;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Perubahan Layanan
                    </button>
                </div>
            </form>
        </div>

        {{-- 2. Persyaratan Dokumen Lampiran --}}
        <div class="card" style="margin-bottom: 24px;">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <h2><i class="fas fa-file-upload" style="color: var(--primary); margin-right: 8px;"></i> Persyaratan Dokumen Lampiran ({{ $service->requirements->count() }})</h2>
            </div>
            <div class="card-body">
                @if($service->requirements->count() > 0)
                <div style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 20px;">
                    @foreach($service->requirements as $req)
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 14px; background: var(--surface-alt); border: 1px solid var(--gray-200); border-radius: 10px; gap: 12px; flex-wrap: wrap;">
                        <div>
                            <div style="font-weight: 700; font-size: 14px; color: var(--gray-900);">
                                {{ $req->name }}
                                @if($req->is_required)
                                    <span style="background: rgba(239, 68, 68, 0.1); color: var(--status-ditolak); font-size: 11px; padding: 1px 6px; border-radius: 4px; margin-left: 4px;">Wajib</span>
                                @else
                                    <span style="background: var(--gray-100); color: var(--gray-600); font-size: 11px; padding: 1px 6px; border-radius: 4px; margin-left: 4px;">Opsional</span>
                                @endif
                            </div>
                            <div style="font-size: 12px; color: var(--gray-500); margin-top: 2px;">
                                Format: <strong>{{ strtoupper($req->allowed_file_types) }}</strong> &bull; Maks: {{ number_format($req->max_file_size / 1024, 1) }} MB
                                @if($req->description) &bull; {{ $req->description }} @endif
                            </div>
                        </div>
                        <form action="{{ route('admin.services.requirements.destroy', [$service, $req]) }}" method="POST" onsubmit="return confirm('Hapus persyaratan dokumen ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline" style="color: var(--status-ditolak); border-color: rgba(239,68,68,0.3); padding: 4px 10px;">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                    @endforeach
                </div>
                @endif

                <form action="{{ route('admin.services.requirements.store', $service) }}" method="POST" style="background: var(--surface-alt); padding: 16px; border-radius: 10px; border: 1px dashed var(--gray-300);">
                    @csrf
                    <div style="font-weight: 700; font-size: 13.5px; color: var(--gray-800); margin-bottom: 12px;">
                        <i class="fas fa-plus-circle" style="color: var(--primary);"></i> Tambah Persyaratan Dokumen Baru
                    </div>
                    <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 12px;">
                        <div class="form-group" style="margin-bottom: 10px;">
                            <label class="form-label" style="font-size: 12px;">Nama Dokumen <span class="required">*</span></label>
                            <input type="text" name="name" class="form-control" required placeholder="Contoh: Scan KTM / Transkrip">
                        </div>
                        <div class="form-group" style="margin-bottom: 10px;">
                            <label class="form-label" style="font-size: 12px;">Format File <span class="required">*</span></label>
                            <input type="text" name="allowed_file_types" class="form-control" value="pdf,jpg,jpeg,png" required>
                        </div>
                        <div class="form-group" style="margin-bottom: 10px;">
                            <label class="form-label" style="font-size: 12px;">Ukuran Maks (KB) <span class="required">*</span></label>
                            <input type="number" name="max_file_size" class="form-control" value="5120" min="100" required>
                        </div>
                    </div>
                    <input type="hidden" name="max_file_count" value="1">
                    <div style="display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap;">
                        <label style="display: flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 500; cursor: pointer;">
                            <input type="checkbox" name="is_required" value="1" checked style="accent-color: var(--primary);">
                            Wajib diunggah pemohon
                        </label>
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus"></i> Tambah Persyaratan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

