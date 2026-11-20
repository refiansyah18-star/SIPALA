@extends('layouts.app')

@section('title', 'Edit Staf & Spesialisasi')
@section('page-title', 'Edit Staf & Perbarui Spesialisasi')

@section('content')
    <div style="max-width: 900px; margin: 0 auto;">
        <div style="margin-bottom: 20px;">
            <a href="{{ route('admin.staff.index') }}" class="back-link" style="color: var(--primary); font-size: 14px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; text-decoration: none;">
                <i class="fas fa-arrow-left"></i> Kembali ke Daftar Staf
            </a>
        </div>

        <form action="{{ route('admin.staff.update', $staff) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Card Info Akun Staf -->
            <div class="card" style="margin-bottom: 24px;">
                <div class="card-header">
                    <h2><i class="fas fa-user-edit" style="color: var(--primary); margin-right: 8px;"></i> Informasi Akun Staf</h2>
                </div>
                <div class="card-body">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label class="form-label">Nama Lengkap <span class="required">*</span></label>
                            <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" 
                                value="{{ old('nama', $staff->nama) }}" required>
                            @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">NIP / NIK / Nomor Identitas <span class="required">*</span></label>
                            <input type="text" name="nomor_identitas" class="form-control @error('nomor_identitas') is-invalid @enderror" 
                                value="{{ old('nomor_identitas', $staff->nomor_identitas) }}" required>
                            @error('nomor_identitas') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Alamat Email <span class="required">*</span></label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                            value="{{ old('email', $staff->email) }}" required>
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label class="form-label">Password Baru (Opsional)</label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" 
                                placeholder="Biarkan kosong jika tidak diubah">
                            <div style="font-size: 12px; color: var(--gray-500); margin-top: 4px;">Kosongkan jika password tidak ingin diganti.</div>
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Konfirmasi Password Baru</label>
                            <input type="password" name="password_confirmation" class="form-control" 
                                placeholder="Ketik ulang password baru">
                        </div>
                    </div>

                    <div style="margin-top: 10px;">
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: 500; font-size: 14px;">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $staff->is_active) ? 'checked' : '' }}
                                style="width: 18px; height: 18px; accent-color: var(--primary);">
                            <span>Akun Staf Aktif (Dapat login dan ditugaskan tiket)</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Card Spesialisasi Layanan -->
            <div class="card" style="margin-bottom: 24px;">
                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                    <div>
                        <h2><i class="fas fa-certificate" style="color: var(--accent); margin-right: 8px;"></i> Spesialisasi Layanan</h2>
                        <p style="color: var(--gray-500); font-size: 14px; margin: 4px 0 0 0;">
                            Centang jenis layanan yang menjadi keahlian staf ini.
                        </p>
                    </div>
                    <div>
                        <button type="button" class="btn btn-sm btn-outline" onclick="toggleAllSpecialties(true)">
                            <i class="fas fa-check-double"></i> Pilih Semua
                        </button>
                        <button type="button" class="btn btn-sm btn-outline" onclick="toggleAllSpecialties(false)">
                            <i class="fas fa-times"></i> Hapus Semua
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    @forelse($categories as $category)
                    <div style="margin-bottom: 24px; padding-bottom: 20px; border-bottom: 1px dashed var(--gray-200);">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                            <h4 style="font-size: 15px; font-weight: 600; color: var(--gray-800); margin: 0; display: flex; align-items: center; gap: 8px;">
                                <i class="fas fa-{{ $category->icon ?: 'folder' }}" style="color: var(--primary);"></i>
                                {{ $category->name }}
                                <span style="font-size: 12px; font-weight: 400; color: var(--gray-500);">
                                    ({{ $category->services->count() }} layanan)
                                </span>
                            </h4>
                            <button type="button" class="btn btn-sm" style="font-size: 12px; padding: 2px 8px; background: var(--gray-100);" 
                                onclick="toggleCategorySpecialties('cat-{{ $category->id }}')">
                                Toggle Kategori
                            </button>
                        </div>

                        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 10px;" id="cat-{{ $category->id }}">
                            @foreach($category->services as $service)
                            @php
                                $isChecked = in_array($service->id, old('specialties', $staffSpecialtyIds));
                            @endphp
                            <label style="display: flex; align-items: flex-start; gap: 10px; padding: 10px 12px; background: var(--surface-alt); border: 1px solid var(--gray-200); border-radius: 8px; cursor: pointer; transition: all 0.2s;"
                                onmouseover="this.style.borderColor='var(--primary-light)'" onmouseout="this.style.borderColor='var(--gray-200)'">
                                <input type="checkbox" name="specialties[]" value="{{ $service->id }}" 
                                    class="specialty-checkbox cat-{{ $category->id }}-item"
                                    {{ $isChecked ? 'checked' : '' }}
                                    style="margin-top: 3px; accent-color: var(--primary);">
                                <div style="font-size: 14px;">
                                    <div style="font-weight: 600; color: var(--gray-900);">{{ $service->name }}</div>
                                    <div style="font-size: 12px; color: var(--gray-500);">SLA: {{ $service->sla_description }}</div>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @empty
                    <p style="color: var(--gray-500); text-align: center; padding: 20px;">Belum ada kategori dan layanan yang terdaftar.</p>
                    @endforelse
                </div>
            </div>

            <!-- Submit Button -->
            <div style="display: flex; gap: 12px; justify-content: flex-end; margin-bottom: 40px;">
                <a href="{{ route('admin.staff.index') }}" class="btn btn-outline">Batal</a>
                <button type="submit" class="btn btn-primary" style="padding: 10px 24px;">
                    <i class="fas fa-save"></i> Perbarui Staf
                </button>
            </div>
        </form>
    </div>

    <script>
        function toggleAllSpecialties(checked) {
            document.querySelectorAll('.specialty-checkbox').forEach(cb => cb.checked = checked);
        }

        function toggleCategorySpecialties(catId) {
            const checkboxes = document.querySelectorAll('.' + catId + '-item');
            const anyUnchecked = Array.from(checkboxes).some(cb => !cb.checked);
            checkboxes.forEach(cb => cb.checked = anyUnchecked);
        }
    </script>
@endsection
