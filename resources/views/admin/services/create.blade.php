@extends('layouts.app')

@section('title', 'Tambah Layanan Baru')
@section('page-title', 'Tambah Layanan')

@section('content')
    <div style="max-width: 800px; margin: 0 auto;">
        <div style="margin-bottom: 24px;">
            <a href="{{ route('admin.services.index') }}" class="back-link" style="color: var(--primary); font-size: 14px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; text-decoration: none;">
                <i class="fas fa-arrow-left"></i> Kembali ke Daftar Layanan
            </a>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>Informasi Layanan Akademik</h2>
            </div>

            <form action="{{ route('admin.services.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">Nama Layanan <span class="required">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required placeholder="Contoh: Pembuatan Surat Keterangan Aktif Kuliah">
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label class="form-label">Kategori <span class="required">*</span></label>
                            <select name="category_id" class="form-control @error('category_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
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
                                    <option value="{{ $unit->id }}" {{ old('unit_id') == $unit->id ? 'selected' : '' }}>
                                        {{ $unit->name }} ({{ $unit->type }})
                                    </option>
                                @endforeach
                            </select>
                            <div style="font-size: 12px; color: var(--gray-500); margin-top: 4px;">Kosongkan jika ditangani oleh admin pusat.</div>
                            @error('unit_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Deskripsi Layanan</label>
                        <textarea name="description" class="form-control" placeholder="Jelaskan secara singkat layanan ini... (akan ditampilkan ke pengguna)">{{ old('description') }}</textarea>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
                        <div class="form-group" style="grid-column: span 2;">
                            <label class="form-label">SLA (Service Level Agreement) <span class="required">*</span></label>
                            <div style="display: flex; gap: 10px;">
                                <input type="number" name="sla_duration" class="form-control" value="{{ old('sla_duration', config('sipala.default_sla_duration')) }}" required min="1" style="width: 100px;">
                                <select name="sla_unit" class="form-control" required style="flex: 1;">
                                    <option value="jam" {{ old('sla_unit') == 'jam' ? 'selected' : '' }}>Jam</option>
                                    <option value="hari" {{ old('sla_unit') == 'hari' ? 'selected' : '' }}>Hari Kalender</option>
                                    <option value="hari_kerja" {{ old('sla_unit', config('sipala.default_sla_unit')) == 'hari_kerja' ? 'selected' : '' }}>Hari Kerja</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Urutan Tampil</label>
                            <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order') }}" placeholder="Otomatis">
                        </div>
                    </div>

                    <div class="form-group" style="margin-top: 10px; border-top: 1px solid var(--gray-200); padding-top: 20px;">
                        <label class="form-label">Target Pengguna (Siapa yang bisa mengajukan) <span class="required">*</span></label>
                        <div style="display: flex; gap: 20px; flex-wrap: wrap; margin-top: 10px;">
                            @foreach($userTypes as $type)
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; background: var(--gray-50); padding: 10px 16px; border-radius: 8px; border: 1px solid var(--gray-200);">
                                <input type="checkbox" name="targets[]" value="{{ $type->id }}" style="width: 16px; height: 16px; accent-color: var(--primary);" 
                                    {{ (is_array(old('targets')) && in_array($type->id, old('targets'))) ? 'checked' : '' }}>
                                <span style="font-weight: 500;">{{ $type->label }}</span>
                            </label>
                            @endforeach
                        </div>
                        @error('targets') <div class="invalid-feedback" style="margin-top: 10px;">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group" style="margin-top: 20px; margin-bottom: 0;">
                        <label class="form-label" style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                            <input type="checkbox" name="is_active" value="1" style="width: 18px; height: 18px; accent-color: var(--primary);" {{ old('is_active', true) ? 'checked' : '' }}>
                            Layanan Aktif (Bisa dipilih oleh pengguna)
                        </label>
                    </div>
                </div>

                <div style="padding: 20px 24px; background: var(--gray-50); border-top: 1px solid var(--gray-200); display: flex; justify-content: flex-end;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Layanan & Lanjut ke Persyaratan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
