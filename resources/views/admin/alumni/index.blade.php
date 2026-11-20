@extends('layouts.app')

@section('title', 'Verifikasi & Akun Alumni')
@section('page-title', 'Verifikasi & Manajemen Alumni')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 22px; flex-wrap: wrap; gap: 16px;">
        <div>
            <h2 style="font-size: 22px; font-weight: 700; color: var(--gray-900); margin-bottom: 4px;">
                <i class="fas fa-user-graduate" style="color: var(--primary); margin-right: 8px;"></i>
                Verifikasi &amp; Akun Alumni
            </h2>
            <p style="color: var(--gray-500); font-size: 14px; margin: 0;">
                Periksa bukti kelulusan pendaftar alumni sebelum mengaktifkan akun, serta kelola akun alumni yang sudah terdaftar.
            </p>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <div>{{ $errors->first() }}</div>
        </div>
    @endif

    {{-- Tab Navigation --}}
    <div style="display: flex; gap: 10px; margin-bottom: 18px; flex-wrap: wrap;">
        <a href="{{ route('admin.alumni.index', ['tab' => 'pending', 'search' => $search]) }}"
           class="btn {{ $tab === 'pending' ? 'btn-primary' : 'btn-outline' }}">
            <i class="fas fa-hourglass-half"></i> Menunggu Verifikasi
            <span style="background: {{ $tab === 'pending' ? 'rgba(255,255,255,0.22)' : 'var(--status-diajukan-bg)' }}; color: {{ $tab === 'pending' ? '#fff' : 'var(--status-diajukan-fg)' }}; padding: 2px 8px; border-radius: 999px; font-size: 12px; font-weight: 700;">
                {{ $counts['pending'] }}
            </span>
        </a>

        <a href="{{ route('admin.alumni.index', ['tab' => 'approved', 'search' => $search]) }}"
           class="btn {{ $tab === 'approved' ? 'btn-primary' : 'btn-outline' }}">
            <i class="fas fa-check-circle"></i> Disetujui
            <span style="background: {{ $tab === 'approved' ? 'rgba(255,255,255,0.22)' : 'var(--sunken)' }}; padding: 2px 8px; border-radius: 999px; font-size: 12px; font-weight: 700;">
                {{ $counts['approved'] }}
            </span>
        </a>

        <a href="{{ route('admin.alumni.index', ['tab' => 'rejected', 'search' => $search]) }}"
           class="btn {{ $tab === 'rejected' ? 'btn-primary' : 'btn-outline' }}">
            <i class="fas fa-times-circle"></i> Ditolak
            <span style="background: {{ $tab === 'rejected' ? 'rgba(255,255,255,0.22)' : 'var(--sunken)' }}; padding: 2px 8px; border-radius: 999px; font-size: 12px; font-weight: 700;">
                {{ $counts['rejected'] }}
            </span>
        </a>

        <a href="{{ route('admin.alumni.index', ['tab' => 'accounts', 'search' => $search]) }}"
           class="btn {{ $tab === 'accounts' ? 'btn-primary' : 'btn-outline' }}">
            <i class="fas fa-users"></i> Daftar Akun Alumni
            <span style="background: {{ $tab === 'accounts' ? 'rgba(255,255,255,0.22)' : 'var(--sunken)' }}; padding: 2px 8px; border-radius: 999px; font-size: 12px; font-weight: 700;">
                {{ $counts['accounts'] }}
            </span>
        </a>
    </div>

    {{-- Search Bar --}}
    <div class="card" style="margin-bottom: 20px; padding: 14px 18px;">
        <form method="GET" action="{{ route('admin.alumni.index') }}" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <div style="flex: 1; min-width: 220px; position: relative;">
                <i class="fas fa-search" style="position: absolute; left: 13px; top: 50%; transform: translateY(-50%); color: var(--gray-400);"></i>
                <input type="text" name="search" class="form-control" style="padding-left: 38px;"
                    placeholder="Cari nama, NIM, email, atau nomor HP..." value="{{ $search }}">
            </div>
            <button type="submit" class="btn btn-primary" style="padding: 9px 18px;">
                <i class="fas fa-filter"></i> Cari
            </button>
            @if($search !== '')
                <a href="{{ route('admin.alumni.index', ['tab' => $tab]) }}" class="btn btn-outline" style="padding: 9px 16px;">
                    <i class="fas fa-times"></i> Reset
                </a>
            @endif
        </form>
    </div>

    {{-- Content Card --}}
    @if($tab === 'accounts')
        <div class="card">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 50px;">No</th>
                            <th>Alumni</th>
                            <th>NIM &amp; Program Studi</th>
                            <th>Tahun Lulus / HP</th>
                            <th>Login Terakhir</th>
                            <th style="text-align: center;">Status</th>
                            <th style="text-align: center; width: 200px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($alumniAccounts as $idx => $alumni)
                            <tr>
                                <td style="color: var(--gray-400); font-weight: 600;">{{ $alumniAccounts->firstItem() + $idx }}</td>
                                <td>
                                    <div style="font-weight: 600; color: var(--ink);">{{ $alumni->nama }}</div>
                                    <div style="font-size: 13px; color: var(--muted);">{{ $alumni->email }}</div>
                                </td>
                                <td>
                                    <div style="font-weight: 700; font-variant-numeric: tabular-nums;">{{ $alumni->nomor_identitas }}</div>
                                    <div style="font-size: 12.5px; color: var(--muted);">{{ $alumni->programStudy?->name ?? '-' }}</div>
                                </td>
                                <td style="font-size: 13px;">
                                    <div>Lulus: <strong>{{ $alumni->tahun_lulus ?? '-' }}</strong></div>
                                    <div style="color: var(--muted);">HP: {{ $alumni->no_hp ?? '-' }}</div>
                                </td>
                                <td style="font-size: 13px; color: var(--muted);">
                                    {{ $alumni->last_login_at ? $alumni->last_login_at->translatedFormat('d M Y, H:i') : 'Belum pernah login' }}
                                </td>
                                <td style="text-align: center;">
                                    @if($alumni->is_active)
                                        <span class="badge-status selesai">Aktif</span>
                                    @else
                                        <span class="badge-status ditolak">Nonaktif</span>
                                    @endif
                                </td>
                                <td style="text-align: center;">
                                    <div style="display: inline-flex; gap: 6px; flex-wrap: wrap; justify-content: center;">
                                            <button type="button" class="btn btn-sm btn-outline"
                                            onclick="openResetPwdModal('{{ route('admin.alumni.accounts.reset-password', $alumni) }}', @js($alumni->nama), @js($alumni->nomor_identitas))"
                                            title="Reset Kata Sandi">
                                            <i class="fas fa-key"></i> Reset Sandi
                                        </button>
                                        <form method="POST" action="{{ route('admin.alumni.accounts.toggle', $alumni) }}"
                                              onsubmit="return confirm('{{ $alumni->is_active ? 'Nonaktifkan' : 'Aktifkan' }} akun alumni {{ addslashes($alumni->nama) }}?');">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm {{ $alumni->is_active ? 'btn-danger' : 'btn-success' }}">
                                                <i class="fas {{ $alumni->is_active ? 'fa-ban' : 'fa-check' }}"></i>
                                                {{ $alumni->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 36px; color: var(--muted);">
                                    Belum ada akun alumni yang sesuai pencarian.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($alumniAccounts->hasPages())
                <div style="padding: 14px 18px; border-top: 1px solid var(--line);">
                    {{ $alumniAccounts->links() }}
                </div>
            @endif
        </div>
    @else
        <div class="card">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 50px;">No</th>
                            <th>Pendaftar</th>
                            <th>NIM / Prodi / Lulus</th>
                            <th>Dokumen Bukti</th>
                            <th>Tanggal / Status</th>
                            <th style="text-align: center; width: 190px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($registrations as $idx => $reg)
                            @php
                                $isStudentUpgrade = $tab === 'pending' && $existingStudentNims->has($reg->nim);
                            @endphp
                            <tr>
                                <td style="color: var(--gray-400); font-weight: 600;">{{ $registrations->firstItem() + $idx }}</td>
                                <td>
                                    <div style="font-weight: 700; color: var(--ink);">{{ $reg->nama }}</div>
                                    <div style="font-size: 13px; color: var(--muted);">{{ $reg->email }}</div>
                                    <div style="font-size: 12.5px; color: var(--muted);">WA/HP: {{ $reg->no_hp }}</div>
                                    @if($isStudentUpgrade)
                                        <span style="display: inline-flex; align-items: center; gap: 4px; margin-top: 4px; padding: 2px 8px; border-radius: 999px; font-size: 11px; font-weight: 700; background: var(--status-diproses-bg); color: var(--status-diproses-fg);">
                                            <i class="fas fa-arrow-up"></i> Eks-Mahasiswa SIPALA (Upgrade ke Alumni)
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div style="font-weight: 700; font-variant-numeric: tabular-nums;">{{ $reg->nim }}</div>
                                    <div style="font-size: 13px; color: var(--muted);">{{ $reg->programStudy?->name ?? '-' }}</div>
                                    <div style="font-size: 12.5px; color: var(--muted);">Lulus Tahun <strong>{{ $reg->tahun_lulus }}</strong></div>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-secondary"
                                        onclick="openProofModal('{{ route('admin.alumni.proof', $reg) }}', @js($reg->proof_original_name), @js($reg->proof_mime_type))">
                                        <i class="fas fa-file-alt"></i> Lihat Bukti
                                    </button>
                                    <div style="font-size: 11.5px; color: var(--muted); margin-top: 4px; max-width: 170px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                        {{ $reg->proof_original_name }}
                                    </div>
                                </td>
                                <td style="font-size: 13px;">
                                    <div style="margin-bottom: 4px;">
                                        @if($reg->isPending())
                                            <span class="badge-status diajukan">Menunggu</span>
                                        @elseif($reg->isApproved())
                                            <span class="badge-status selesai">Disetujui</span>
                                        @else
                                            <span class="badge-status ditolak">Ditolak</span>
                                        @endif
                                    </div>
                                    <div style="font-size: 12px; color: var(--muted);">
                                        Daftar: {{ $reg->created_at->translatedFormat('d M Y, H:i') }}
                                    </div>
                                    @if($reg->reviewed_at)
                                        <div style="font-size: 12px; color: var(--muted);">
                                            Review: {{ $reg->reviewed_at->translatedFormat('d M Y, H:i') }}
                                            @if($reg->reviewer)
                                                oleh {{ $reg->reviewer->nama }}
                                            @endif
                                        </div>
                                    @endif
                                    @if($reg->isRejected() && $reg->rejection_reason)
                                        <div style="font-size: 12px; color: var(--status-ditolak-fg); margin-top: 4px;">
                                            <strong>Alasan:</strong> {{ $reg->rejection_reason }}
                                        </div>
                                    @endif
                                </td>
                                <td style="text-align: center;">
                                    @if($reg->isPending())
                                        <div style="display: inline-flex; gap: 6px; flex-wrap: wrap; justify-content: center;">
                                            <form method="POST" action="{{ route('admin.alumni.approve', $reg) }}"
                                                  onsubmit="return confirm('{{ $isStudentUpgrade ? 'Setujui & upgrade akun mahasiswa NIM ' . $reg->nim . ' menjadi Alumni?' : 'Setujui dan aktifkan akun alumni ' . addslashes($reg->nama) . '?' }}');">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success">
                                                    <i class="fas fa-check"></i> Setujui
                                                </button>
                                            </form>
                                            <button type="button" class="btn btn-danger btn-sm"
                                                onclick="openRejectModal('{{ route('admin.alumni.reject', $reg) }}', @js($reg->nama), @js($reg->nim))">
                                                <i class="fas fa-times"></i> Tolak
                                            </button>
                                        </div>
                                    @else
                                        <span style="font-size: 12.5px; color: var(--muted);">Selesai diproses</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 40px; color: var(--muted);">
                                    <i class="fas fa-user-graduate" style="font-size: 32px; opacity: 0.35; display: block; margin-bottom: 10px;"></i>
                                    Tidak ada data pengajuan alumni pada tab ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($registrations->hasPages())
                <div style="padding: 14px 18px; border-top: 1px solid var(--line);">
                    {{ $registrations->links() }}
                </div>
            @endif
        </div>
    @endif

    {{-- Modal Preview Bukti Kelulusan --}}
    <div id="proofModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 1050; align-items: center; justify-content: center; padding: 20px;">
        <div class="card" style="width: 100%; max-width: 820px; max-height: 90vh; display: flex; flex-direction: column;">
            <div class="card-header">
                <h3 id="proofModalTitle" style="margin: 0; font-size: 16px; font-weight: 700;">Dokumen Bukti Kelulusan</h3>
                <div style="display: flex; gap: 10px; align-items: center;">
                    <a id="proofOpenNewTab" href="#" target="_blank" class="btn btn-sm btn-outline">
                        <i class="fas fa-external-link-alt"></i> Buka / Unduh
                    </a>
                    <button type="button" onclick="closeProofModal()" style="background: none; border: none; font-size: 20px; cursor: pointer; color: var(--muted);">&times;</button>
                </div>
            </div>
            <div id="proofModalBody" style="padding: 16px; overflow: auto; flex: 1; text-align: center; background: var(--sunken);">
            </div>
        </div>
    </div>

    {{-- Modal Tolak Pendaftaran --}}
    <div id="rejectModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.55); z-index: 1050; align-items: center; justify-content: center; padding: 20px;">
        <div class="card" style="width: 100%; max-width: 500px;">
            <div class="card-header">
                <h3 id="rejectModalTitle" style="margin: 0; font-size: 17px; font-weight: 700;">Tolak Pengajuan Alumni</h3>
                <button type="button" onclick="closeRejectModal()" style="background: none; border: none; font-size: 20px; cursor: pointer; color: var(--muted);">&times;</button>
            </div>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label" for="rejection_reason">Alasan Penolakan <span class="required">*</span></label>
                        <textarea id="rejection_reason" name="rejection_reason" class="form-control" required
                            placeholder="Jelaskan alasan penolakan agar pendaftar dapat memperbaikinya..."></textarea>
                    </div>
                    <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                        <button type="button" class="btn btn-sm btn-outline" onclick="setRejectReason('Dokumen bukti kelulusan tidak terbaca / buram. Silakan unggah ulang dengan resolusi lebih jelas.')">Foto buram</button>
                        <button type="button" class="btn btn-sm btn-outline" onclick="setRejectReason('Data NIM, nama, atau program studi tidak sesuai dengan bukti kelulusan yang diunggah.')">Data tidak sesuai</button>
                        <button type="button" class="btn btn-sm btn-outline" onclick="setRejectReason('Berkas yang diunggah bukan merupakan bukti kelulusan resmi (Ijazah/SKL/Transkrip).')">Berkas tidak valid</button>
                    </div>
                </div>
                <div style="padding: 14px 20px; background: var(--surface-alt); border-top: 1px solid var(--line); display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" class="btn btn-outline" onclick="closeRejectModal()">Batal</button>
                    <button type="submit" class="btn btn-danger"><i class="fas fa-ban"></i> Tolak Pengajuan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Reset Password Akun Alumni --}}
    <div id="resetPwdModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.55); z-index: 1050; align-items: center; justify-content: center; padding: 20px;">
        <div class="card" style="width: 100%; max-width: 460px;">
            <div class="card-header">
                <h3 id="resetPwdModalTitle" style="margin: 0; font-size: 17px; font-weight: 700;">Reset Kata Sandi Alumni</h3>
                <button type="button" onclick="closeResetPwdModal()" style="background: none; border: none; font-size: 20px; cursor: pointer; color: var(--muted);">&times;</button>
            </div>
            <form id="resetPwdForm" method="POST">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label" for="admin_reset_password">Kata Sandi Baru <span class="required">*</span></label>
                        <input type="password" id="admin_reset_password" name="password" class="form-control" minlength="8" required placeholder="Minimal 8 karakter">
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" for="admin_reset_password_confirmation">Konfirmasi Kata Sandi <span class="required">*</span></label>
                        <input type="password" id="admin_reset_password_confirmation" name="password_confirmation" class="form-control" minlength="8" required placeholder="Ulangi kata sandi baru">
                    </div>
                </div>
                <div style="padding: 14px 20px; background: var(--surface-alt); border-top: 1px solid var(--line); display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" class="btn btn-outline" onclick="closeResetPwdModal()">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-key"></i> Simpan Kata Sandi</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function openProofModal(url, originalName, mimeType) {
        document.getElementById('proofModalTitle').textContent = 'Bukti Kelulusan: ' + (originalName || 'Berkas');
        document.getElementById('proofOpenNewTab').href = url;

        const body = document.getElementById('proofModalBody');
        body.innerHTML = '';

        const isImage = (mimeType && mimeType.startsWith('image/')) || /\.(jpg|jpeg|png)$/i.test(originalName || '');
        if (isImage) {
            const img = document.createElement('img');
            img.src = url;
            img.alt = originalName || 'Bukti Kelulusan';
            img.style.maxWidth = '100%';
            img.style.maxHeight = '70vh';
            img.style.borderRadius = '8px';
            body.appendChild(img);
        } else {
            const iframe = document.createElement('iframe');
            iframe.src = url;
            iframe.style.width = '100%';
            iframe.style.height = '70vh';
            iframe.style.border = 'none';
            iframe.style.borderRadius = '8px';
            body.appendChild(iframe);
        }

        document.getElementById('proofModal').style.display = 'flex';
    }

    function closeProofModal() {
        document.getElementById('proofModal').style.display = 'none';
        document.getElementById('proofModalBody').innerHTML = '';
    }

    function openRejectModal(actionUrl, nama, nim) {
        document.getElementById('rejectModalTitle').textContent = 'Tolak Pengajuan: ' + nama + ' (' + nim + ')';
        document.getElementById('rejectForm').action = actionUrl;
        document.getElementById('rejection_reason').value = '';
        document.getElementById('rejectModal').style.display = 'flex';
    }

    function setRejectReason(text) {
        document.getElementById('rejection_reason').value = text;
    }

    function closeRejectModal() {
        document.getElementById('rejectModal').style.display = 'none';
    }

    function openResetPwdModal(actionUrl, nama, nim) {
        document.getElementById('resetPwdModalTitle').textContent = 'Reset Sandi: ' + nama + ' (' + nim + ')';
        document.getElementById('resetPwdForm').action = actionUrl;
        document.getElementById('admin_reset_password').value = '';
        document.getElementById('admin_reset_password_confirmation').value = '';
        document.getElementById('resetPwdModal').style.display = 'flex';
    }

    function closeResetPwdModal() {
        document.getElementById('resetPwdModal').style.display = 'none';
    }
</script>
@endpush
