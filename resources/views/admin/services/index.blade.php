@extends('layouts.app')

@section('title', 'Katalog Layanan')
@section('page-title', 'Manajemen Layanan')

@section('content')
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h2><i class="fas fa-cogs" style="margin-right: 8px; color: var(--primary);"></i> Katalog Layanan Akademik</h2>
            <a href="{{ route('admin.services.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Tambah Layanan
            </a>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Nama Layanan</th>
                        <th>Kategori</th>
                        <th>Unit Pengolah</th>
                        <th style="text-align: center;">SLA</th>
                        <th style="text-align: center;">Target User</th>
                        <th style="text-align: center;">Persyaratan</th>
                        <th style="text-align: center;">Status</th>
                        <th style="width: 100px; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($services as $service)
                    <tr>
                        <td>
                            <strong style="color: var(--gray-900);">{{ $service->name }}</strong>
                            @if($service->description)
                                <div style="color: var(--gray-500); font-size: 12px; margin-top: 2px;">{{ Str::limit($service->description, 40) }}</div>
                            @endif
                        </td>
                        <td>
                            <span style="font-size: 12px; font-weight: 600; color: var(--primary); background: var(--primary-50); padding: 2px 8px; border-radius: 4px;">
                                <i class="fas fa-{{ $service->category->icon ?? 'folder' }}"></i> {{ $service->category->name }}
                            </span>
                        </td>
                        <td style="font-size: 14px;">{{ $service->unit ? $service->unit->name : '—' }}</td>
                        <td style="text-align: center;">
                            <span style="font-weight: 600; color: var(--status-diproses);">
                                {{ $service->sla_duration }} {{ str_replace('_', ' ', $service->sla_unit) }}
                            </span>
                        </td>
                        <td style="text-align: center;">
                            <span style="background: var(--gray-100); padding: 2px 8px; border-radius: 99px; font-size: 12px; font-weight: 600;" title="Ditargetkan untuk {{ $service->targets_count }} tipe pengguna">
                                <i class="fas fa-users"></i> {{ $service->targets_count }}
                            </span>
                        </td>
                        <td style="text-align: center;">
                            <span style="background: var(--gray-100); padding: 2px 8px; border-radius: 99px; font-size: 12px; font-weight: 600;" title="{{ $service->requirements_count }} Dokumen, {{ $service->fields_count }} Custom Field">
                                <i class="fas fa-file-alt"></i> {{ $service->requirements_count }} | <i class="fas fa-wpforms"></i> {{ $service->fields_count }}
                            </span>
                        </td>
                        <td style="text-align: center;">
                            @if($service->is_active)
                                <span class="badge-status selesai">Aktif</span>
                            @else
                                <span class="badge-status ditolak">Nonaktif</span>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            <a href="{{ route('admin.services.edit', $service) }}" class="btn btn-sm btn-outline" style="padding: 4px 8px;" title="Edit Layanan & Persyaratan">
                                <i class="fas fa-edit"></i>
                            </a>
                            
                            <form action="{{ route('admin.services.destroy', $service) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus layanan ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline" style="padding: 4px 8px; color: var(--status-ditolak); border-color: var(--status-ditolak);" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="empty-state">
                            <i class="fas fa-cogs"></i>
                            <h3>Belum ada layanan</h3>
                            <p>Silakan buat layanan akademik baru.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
