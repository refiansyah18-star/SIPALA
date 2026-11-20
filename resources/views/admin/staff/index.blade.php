@extends('layouts.app')

@section('title', 'Kelola Staf & Spesialisasi')
@section('page-title', 'Manajemen Staf & Spesialisasi')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
        <div>
            <h2 style="font-size: 22px; font-weight: 700; color: var(--gray-900); margin-bottom: 4px;">
                <i class="fas fa-user-tie" style="color: var(--primary); margin-right: 8px;"></i>
                Daftar Staf Layanan
            </h2>
            <p style="color: var(--gray-500); font-size: 14px; margin: 0;">
                Kelola staf pelaksana dan tentukan keahlian/spesialisasi layanan yang dapat mereka tangani.
            </p>
        </div>
        <a href="{{ route('admin.staff.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Staf Baru
        </a>
    </div>

    <!-- Filter & Pencarian -->
    <div class="card" style="margin-bottom: 24px; padding: 16px 20px;">
        <form method="GET" action="{{ route('admin.staff.index') }}" style="display: flex; gap: 16px; align-items: center; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 240px; position: relative;">
                <i class="fas fa-search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--gray-400);"></i>
                <input type="text" name="search" class="form-control" style="padding-left: 36px;" 
                    placeholder="Cari berdasarkan nama, email, atau NIP/NIK..." value="{{ request('search') }}">
            </div>
            <div style="min-width: 220px;">
                <select name="service_id" class="form-control" onchange="this.form.submit()">
                    <option value="">-- Semua Spesialisasi Layanan --</option>
                    @foreach($services as $srv)
                        <option value="{{ $srv->id }}" {{ request('service_id') == $srv->id ? 'selected' : '' }}>
                            {{ $srv->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary" style="padding: 9px 18px;">
                <i class="fas fa-filter"></i> Filter
            </button>
            @if(request()->hasAny(['search', 'service_id']))
                <a href="{{ route('admin.staff.index') }}" class="btn btn-outline" style="padding: 9px 18px;">
                    <i class="fas fa-times"></i> Reset
                </a>
            @endif
        </form>
    </div>

    <!-- Tabel Staf -->
    <div class="card">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Informasi Staf</th>
                        <th>NIP / NIK</th>
                        <th>Spesialisasi Layanan</th>
                        <th style="text-align: center;">Beban Tiket</th>
                        <th style="text-align: center;">Status</th>
                        <th style="width: 130px; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($staffList as $index => $staff)
                    <tr>
                        <td style="color: var(--gray-400); font-weight: 500;">
                            {{ $staffList->firstItem() + $index }}
                        </td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div style="width: 38px; height: 38px; border-radius: 50%; background: linear-gradient(135deg, var(--primary), var(--accent)); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px;">
                                    {{ strtoupper(substr($staff->nama, 0, 2)) }}
                                </div>
                                <div>
                                    <div style="font-weight: 600; color: var(--gray-900);">{{ $staff->nama }}</div>
                                    <div style="font-size: 14px; color: var(--gray-500);">{{ $staff->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="font-variant-numeric: tabular-nums; font-size: 14px; font-weight: 600; color: var(--gray-700);">
                            {{ $staff->nomor_identitas }}
                        </td>
                        <td>
                            @if($staff->staffSpecialties->count() > 0)
                                <div style="display: flex; flex-wrap: wrap; gap: 6px; max-width: 450px;">
                                    @foreach($staff->staffSpecialties as $spec)
                                        <span style="display: inline-flex; align-items: center; gap: 4px; background: var(--primary-50); color: var(--primary); border: 1px solid var(--primary-100); border-radius: 6px; padding: 2px 8px; font-size: 12px; font-weight: 500;">
                                            <i class="fas fa-check-circle" style="font-size: 12px;"></i>
                                            {{ $spec->name }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <span style="font-size: 12px; color: var(--gray-400); font-style: italic;">
                                    Belum ada spesialisasi (bisa terima semua tiket umum)
                                </span>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            <span style="display: inline-block; background: var(--gray-100); color: var(--gray-800); font-weight: 700; padding: 4px 10px; border-radius: 20px; font-size: 12px;">
                                {{ $staff->assigned_tickets_count }} tiket
                            </span>
                        </td>
                        <td style="text-align: center;">
                            @if($staff->is_active)
                                <span class="badge-status selesai">Aktif</span>
                            @else
                                <span class="badge-status ditolak">Nonaktif</span>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            <div style="display: flex; gap: 6px; justify-content: center;">
                                <a href="{{ route('admin.staff.edit', $staff) }}" class="btn btn-sm btn-outline" style="padding: 5px 9px;" title="Edit Staf & Spesialisasi">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.staff.destroy', $staff) }}" method="POST" style="display: inline-block;"
                                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus/menonaktifkan staf ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline" style="padding: 5px 9px; color: var(--status-ditolak); border-color: rgba(239,68,68,0.2);" title="Hapus Staf">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 40px; color: var(--gray-400);">
                            <i class="fas fa-user-slash" style="font-size: 36px; margin-bottom: 12px; display: block; opacity: 0.5;"></i>
                            Tidak ada staf ditemukan sesuai filter pencarian.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($staffList->hasPages())
        <div style="padding: 16px 20px; border-top: 1px solid var(--gray-200);">
            {{ $staffList->links() }}
        </div>
        @endif
    </div>
@endsection
