@extends('layouts.app')

@section('title', 'Kategori Layanan')
@section('page-title', 'Manajemen Kategori')

@section('content')
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h2><i class="fas fa-th-large" style="margin-right: 8px; color: var(--primary);"></i> Kategori Layanan</h2>
            <button type="button" class="btn btn-primary btn-sm" onclick="openCreateModal()">
                <i class="fas fa-plus"></i> Tambah Kategori
            </button>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th style="width: 60px; text-align: center;">Icon</th>
                        <th>Nama Kategori</th>
                        <th>Deskripsi</th>
                        <th style="text-align: center;">Jumlah Layanan</th>
                        <th style="text-align: center;">Urutan</th>
                        <th style="text-align: center;">Status</th>
                        <th style="width: 120px; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                    <tr>
                        <td style="text-align: center; font-size: 18px; color: var(--primary);">
                            <span style="display: inline-flex; width: 34px; height: 34px; border-radius: 8px; background: var(--primary-soft); color: var(--primary); align-items: center; justify-content: center;">
                                <i class="fas fa-{{ $category->icon ?: 'folder' }}"></i>
                            </span>
                        </td>
                        <td>
                            <strong style="color: var(--gray-900);">{{ $category->name }}</strong>
                        </td>
                        <td style="color: var(--gray-500); font-size: 14px;">
                            {{ Str::limit($category->description, 50) ?: '—' }}
                        </td>
                        <td style="text-align: center;">
                            <span style="background: var(--gray-100); padding: 2px 8px; border-radius: 99px; font-size: 12px; font-weight: 600;">
                                {{ $category->services_count }}
                            </span>
                        </td>
                        <td style="text-align: center;">{{ $category->sort_order }}</td>
                        <td style="text-align: center;">
                            @if($category->is_active)
                                <span class="badge-status selesai">Aktif</span>
                            @else
                                <span class="badge-status ditolak">Nonaktif</span>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            <button type="button" class="btn btn-sm btn-outline" style="padding: 4px 8px;" title="Edit" 
                                onclick="openEditModal({{ $category->toJson() }})">
                                <i class="fas fa-edit"></i>
                            </button>
                            
                            @if($category->services_count == 0)
                            <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kategori ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline" style="padding: 4px 8px; color: var(--status-ditolak); border-color: var(--status-ditolak);" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            @else
                            <button type="button" class="btn btn-sm btn-outline" style="padding: 4px 8px; color: var(--gray-400); border-color: var(--gray-200); cursor: not-allowed;" title="Tidak dapat dihapus karena memiliki layanan" disabled>
                                <i class="fas fa-trash"></i>
                            </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="empty-state">
                            <i class="fas fa-folder-open"></i>
                            <h3>Belum ada kategori</h3>
                            <p>Silakan tambahkan kategori layanan baru.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL FORM --}}
    @php
        $presetIcons = [
            ['icon' => 'book-open', 'label' => 'Akademik'],
            ['icon' => 'folder', 'label' => 'Dokumen'],
            ['icon' => 'desktop', 'label' => 'Sistem/IT'],
            ['icon' => 'laptop-code', 'label' => 'Aplikasi'],
            ['icon' => 'award', 'label' => 'Kelulusan'],
            ['icon' => 'graduation-cap', 'label' => 'Wisuda'],
            ['icon' => 'users', 'label' => 'Alumni'],
            ['icon' => 'briefcase', 'label' => 'Dosen/Pegawai'],
            ['icon' => 'file-alt', 'label' => 'Surat'],
            ['icon' => 'id-card', 'label' => 'Identitas'],
            ['icon' => 'server', 'label' => 'Server'],
            ['icon' => 'cogs', 'label' => 'Layanan'],
            ['icon' => 'wrench', 'label' => 'Perbaikan'],
            ['icon' => 'bug', 'label' => 'Bug/Error'],
            ['icon' => 'shield-alt', 'label' => 'Keamanan'],
            ['icon' => 'headset', 'label' => 'Helpdesk'],
            ['icon' => 'building', 'label' => 'Unit/Gedung'],
            ['icon' => 'clipboard-check', 'label' => 'Validasi'],
        ];
    @endphp
    <div id="categoryModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(4px); padding: 16px;">
        <div style="background: white; width: 100%; max-width: 540px; border-radius: var(--radius-lg); box-shadow: var(--shadow-xl); overflow: hidden; max-height: 92vh; display: flex; flex-direction: column;">
            <div style="padding: 18px 24px; border-bottom: 1px solid var(--gray-200); display: flex; justify-content: space-between; align-items: center;">
                <h3 id="modalTitle" style="font-size: 18px; font-weight: 700; color: var(--gray-900); margin: 0;">Tambah Kategori</h3>
                <button type="button" onclick="closeModal()" style="background: none; border: none; font-size: 20px; color: var(--gray-500); cursor: pointer; line-height: 1;">
                    &times;
                </button>
            </div>
            
            <form id="categoryForm" method="POST" action="{{ route('admin.categories.store') }}" style="overflow-y: auto;">
                @csrf
                <div id="methodContainer"></div>
                
                <div style="padding: 20px 24px;">
                    <div class="form-group">
                        <label class="form-label">Nama Kategori <span class="required">*</span></label>
                        <input type="text" name="name" id="cat_name" class="form-control" required placeholder="Contoh: Layanan Akademik">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" id="cat_desc" class="form-control" style="min-height: 70px;" placeholder="Penjelasan singkat mengenai kategori ini..."></textarea>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 140px; gap: 16px;">
                        <div class="form-group" style="margin-bottom: 10px;">
                            <label class="form-label">Ikon Kategori</label>
                            <div style="position: relative; display: flex; align-items: center;">
                                <span id="cat_icon_preview_box" style="position: absolute; left: 10px; width: 28px; height: 28px; border-radius: 6px; background: var(--primary-soft); color: var(--primary); display: inline-flex; align-items: center; justify-content: center; font-size: 14px;">
                                    <i id="cat_icon_preview" class="fas fa-folder"></i>
                                </span>
                                <input type="text" name="icon" id="cat_icon" class="form-control" style="padding-left: 46px;" placeholder="Pilih di bawah atau ketik (misal: desktop)" oninput="updateIconPreview(this.value)">
                            </div>
                        </div>
                        
                        <div class="form-group" style="margin-bottom: 10px;">
                            <label class="form-label">Urutan Tampil</label>
                            <input type="number" name="sort_order" id="cat_sort" class="form-control" placeholder="Otomatis">
                        </div>
                    </div>

                    <div class="form-group">
                        <div style="font-size: 12px; font-weight: 600; color: var(--gray-500); margin-bottom: 8px;">
                            Pilih Cepat Ikon:
                        </div>
                        <div style="display: grid; grid-template-columns: repeat(6, 1fr); gap: 6px;">
                            @foreach($presetIcons as $item)
                                <button type="button"
                                    class="icon-picker-btn"
                                    data-icon="{{ $item['icon'] }}"
                                    onclick="selectPresetIcon('{{ $item['icon'] }}')"
                                    style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 4px; padding: 8px 4px; border: 1px solid var(--gray-200); border-radius: 8px; background: var(--surface-alt); color: var(--gray-700); cursor: pointer; font-family: inherit; transition: all 0.15s ease;">
                                    <i class="fas fa-{{ $item['icon'] }}" style="font-size: 15px; color: var(--primary);"></i>
                                    <span style="font-size: 10px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%;">{{ $item['label'] }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 0; margin-top: 12px;">
                        <label class="form-label" style="display: flex; align-items: center; gap: 8px; cursor: pointer; margin: 0;">
                            <input type="checkbox" name="is_active" id="cat_active" value="1" style="width: 16px; height: 16px; accent-color: var(--primary);" checked>
                            Kategori Aktif
                        </label>
                    </div>
                </div>
                
                <div style="padding: 14px 24px; background: var(--gray-50); border-top: 1px solid var(--gray-200); display: flex; justify-content: flex-end; gap: 12px;">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSubmit">Simpan Kategori</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const modal = document.getElementById('categoryModal');
    const form = document.getElementById('categoryForm');
    const title = document.getElementById('modalTitle');
    const methodContainer = document.getElementById('methodContainer');
    const btnSubmit = document.getElementById('btnSubmit');

    const iconAliases = {
        'monitor': 'desktop',
        'screen': 'desktop',
        'computer': 'desktop',
        'komputer': 'desktop',
        'pc': 'desktop',
        'sistem': 'desktop',
        'system': 'desktop',
        'akademik': 'book-open',
        'dokumen': 'folder',
        'wisuda': 'graduation-cap',
        'kelulusan': 'award',
        'alumni': 'users',
        'dosen': 'briefcase'
    };

    function normalizeIconClient(val) {
        let icon = (val || '').trim().toLowerCase();
        if (!icon) return 'folder';
        icon = icon.replace(/^(fa-solid|fa-regular|fa-brands|fas|far|fab)\s+/i, '');
        icon = icon.replace(/^fa-/i, '').trim().replace(/\s+/g, '-');
        return iconAliases[icon] || icon || 'folder';
    }

    function updateIconPreview(val) {
        const normalized = normalizeIconClient(val);
        const previewEl = document.getElementById('cat_icon_preview');
        if (previewEl) {
            previewEl.className = 'fas fa-' + normalized;
        }
        document.querySelectorAll('.icon-picker-btn').forEach(function(btn) {
            if (btn.getAttribute('data-icon') === normalized) {
                btn.style.borderColor = 'var(--primary)';
                btn.style.background = 'var(--primary-soft)';
            } else {
                btn.style.borderColor = 'var(--gray-200)';
                btn.style.background = 'var(--surface-alt)';
            }
        });
    }

    function selectPresetIcon(iconName) {
        const input = document.getElementById('cat_icon');
        input.value = iconName;
        updateIconPreview(iconName);
    }
    
    function openCreateModal() {
        title.innerText = 'Tambah Kategori';
        form.action = '{{ route("admin.categories.store") }}';
        methodContainer.innerHTML = '';
        btnSubmit.innerText = 'Simpan Kategori';
        
        document.getElementById('cat_name').value = '';
        document.getElementById('cat_desc').value = '';
        document.getElementById('cat_icon').value = 'book-open';
        document.getElementById('cat_sort').value = '';
        document.getElementById('cat_active').checked = true;
        updateIconPreview('book-open');
        
        modal.style.display = 'flex';
    }
    
    function openEditModal(category) {
        title.innerText = 'Edit Kategori';
        form.action = '/admin/categories/' + category.id;
        methodContainer.innerHTML = '<input type="hidden" name="_method" value="PUT">';
        btnSubmit.innerText = 'Update Kategori';
        
        const iconVal = normalizeIconClient(category.icon || 'folder');
        document.getElementById('cat_name').value = category.name;
        document.getElementById('cat_desc').value = category.description || '';
        document.getElementById('cat_icon').value = iconVal;
        document.getElementById('cat_sort').value = category.sort_order;
        document.getElementById('cat_active').checked = category.is_active;
        updateIconPreview(iconVal);
        
        modal.style.display = 'flex';
    }
    
    function closeModal() {
        modal.style.display = 'none';
    }
</script>
@endpush
