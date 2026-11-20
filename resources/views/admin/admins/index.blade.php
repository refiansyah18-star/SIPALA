@extends('layouts.app')

@section('title', 'Kelola Admin (Manajer)')
@section('page-title', 'Manajemen Administrator')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
        <div>
            <h2 style="font-size: 22px; font-weight: 700; color: var(--gray-900); margin-bottom: 4px;">
                <i class="fas fa-user-shield" style="color: var(--primary); margin-right: 8px;"></i>
                Daftar Admin (Manajer Layanan)
            </h2>
            <p style="color: var(--gray-500); font-size: 14px; margin: 0;">
                Kelola akun manajer layanan yang bertugas memverifikasi tiket dan menugaskan tiket kepada staf.
            </p>
        </div>
        <button type="button" class="btn btn-primary" onclick="openCreateAdminModal()">
            <i class="fas fa-plus"></i> Tambah Admin Baru
        </button>
    </div>

    <!-- Tabel Admin -->
    <div class="card">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Informasi Administrator</th>
                        <th>NIP / Identitas</th>
                        <th>Login Terakhir</th>
                        <th style="text-align: center;">Status</th>
                        <th style="width: 120px; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($admins as $index => $admin)
                    <tr>
                        <td style="color: var(--gray-400); font-weight: 500;">
                            {{ $admins->firstItem() + $index }}
                        </td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div style="width: 38px; height: 38px; border-radius: 50%; background: linear-gradient(135deg, #f59e0b, #ef4444); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px;">
                                    {{ strtoupper(substr($admin->nama, 0, 2)) }}
                                </div>
                                <div>
                                    <div style="font-weight: 600; color: var(--gray-900);">{{ $admin->nama }}</div>
                                    <div style="font-size: 14px; color: var(--gray-500);">{{ $admin->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="font-variant-numeric: tabular-nums; font-size: 14px; font-weight: 600;">
                            {{ $admin->nomor_identitas }}
                        </td>
                        <td style="font-size: 14px; color: var(--gray-500);">
                            {{ $admin->last_login_at ? $admin->last_login_at->translatedFormat('d M Y, H:i') : 'Belum pernah login' }}
                        </td>
                        <td style="text-align: center;">
                            @if($admin->is_active)
                                <span class="badge-status selesai">Aktif</span>
                            @else
                                <span class="badge-status ditolak">Nonaktif</span>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            <button type="button" class="btn btn-sm btn-outline" style="padding: 4px 8px;" title="Edit"
                                onclick="openEditAdminModal({{ $admin->toJson() }})">
                                <i class="fas fa-edit"></i>
                            </button>
                            @if($admin->id !== auth()->id())
                            <form action="{{ route('admin.admins.destroy', $admin) }}" method="POST" style="display: inline-block;"
                                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun admin ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline" style="padding: 4px 8px; color: var(--status-ditolak); border-color: rgba(239,68,68,0.2);" title="Hapus">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 30px; color: var(--gray-400);">
                            Belum ada akun Administrator (Manajer).
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($admins->hasPages())
        <div style="padding: 16px 20px; border-top: 1px solid var(--gray-200);">
            {{ $admins->links() }}
        </div>
        @endif
    </div>

    <!-- Modal Form Admin -->
    <div class="modal-backdrop" id="adminModalBackdrop" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
        <div class="card" style="width: 100%; max-width: 500px; margin: 20px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <h3 id="adminModalTitle" style="margin: 0; font-size: 18px; font-weight: 700;">Tambah Admin Manajer</h3>
                <button type="button" onclick="closeAdminModal()" style="background: none; border: none; font-size: 18px; cursor: pointer; color: var(--gray-400);">&times;</button>
            </div>
            <form id="adminForm" method="POST">
                @csrf
                <div id="adminMethodSpoof"></div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">Nama Lengkap <span class="required">*</span></label>
                        <input type="text" name="nama" id="adminNama" class="form-control" required placeholder="Nama Administrator">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email <span class="required">*</span></label>
                        <input type="email" name="email" id="adminEmail" class="form-control" required placeholder="admin@sipala.ac.id">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nomor Identitas (NIP/NIK) <span class="required">*</span></label>
                        <input type="text" name="nomor_identitas" id="adminIdentitas" class="form-control" required placeholder="ADM002">
                    </div>
                    <div class="form-group">
                        <label class="form-label" id="adminPasswordLabel">Password <span class="required">*</span></label>
                        <input type="password" name="password" id="adminPassword" class="form-control" placeholder="Minimal 6 karakter">
                        <div id="adminPasswordHelp" style="display: none; font-size: 12px; color: var(--gray-500); margin-top: 4px;">Kosongkan jika password tidak diubah.</div>
                    </div>
                    <div class="form-group" style="margin-top: 12px;">
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                            <input type="checkbox" name="is_active" id="adminIsActive" value="1" checked style="accent-color: var(--primary);">
                            <span>Akun Aktif</span>
                        </label>
                    </div>
                </div>
                <div style="padding: 16px 24px; background: var(--surface-alt); border-top: 1px solid var(--gray-200); display: flex; justify-content: flex-end; gap: 10px; border-radius: 0 0 12px 12px;">
                    <button type="button" class="btn btn-outline" onclick="closeAdminModal()">Batal</button>
                    <button type="submit" class="btn btn-primary" id="adminSubmitBtn">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openCreateAdminModal() {
            document.getElementById('adminModalTitle').innerText = 'Tambah Admin Manajer';
            document.getElementById('adminForm').action = "{{ route('admin.admins.store') }}";
            document.getElementById('adminMethodSpoof').innerHTML = '';
            document.getElementById('adminNama').value = '';
            document.getElementById('adminEmail').value = '';
            document.getElementById('adminIdentitas').value = '';
            document.getElementById('adminPassword').value = '';
            document.getElementById('adminPassword').required = true;
            document.getElementById('adminPasswordHelp').style.display = 'none';
            document.getElementById('adminIsActive').checked = true;
            document.getElementById('adminModalBackdrop').style.display = 'flex';
        }

        function openEditAdminModal(admin) {
            document.getElementById('adminModalTitle').innerText = 'Edit Admin: ' + admin.nama;
            document.getElementById('adminForm').action = "/admin/admins/" + admin.id;
            document.getElementById('adminMethodSpoof').innerHTML = '<input type="hidden" name="_method" value="PUT">';
            document.getElementById('adminNama').value = admin.nama;
            document.getElementById('adminEmail').value = admin.email;
            document.getElementById('adminIdentitas').value = admin.nomor_identitas;
            document.getElementById('adminPassword').value = '';
            document.getElementById('adminPassword').required = false;
            document.getElementById('adminPasswordHelp').style.display = 'block';
            document.getElementById('adminIsActive').checked = !!admin.is_active;
            document.getElementById('adminModalBackdrop').style.display = 'flex';
        }

        function closeAdminModal() {
            document.getElementById('adminModalBackdrop').style.display = 'none';
        }
    </script>
@endsection
