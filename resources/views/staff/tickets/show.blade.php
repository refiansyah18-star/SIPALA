@extends('layouts.app')

@section('title', 'Proses Tiket ' . $ticket->ticket_number)
@section('page-title', 'Pengerjaan Tiket')

@section('content')
    <div style="margin-bottom: 20px;">
        <a href="{{ route('staff.tickets.index') }}" class="back-link" style="color: var(--primary); font-size: 14px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; text-decoration: none;">
            <i class="fas fa-arrow-left"></i> Kembali ke Daftar Tugas Saya
        </a>
    </div>

    <!-- Header Tiket Card -->
    <div class="card" style="margin-bottom: 24px;">
        <div style="padding: 24px; display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
            <div>
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                    <h1 style="font-size: 22px; font-weight: 700; color: var(--gray-900); margin: 0; font-variant-numeric: tabular-nums; font-feature-settings: 'tnum';">
                        {{ $ticket->ticket_number }}
                    </h1>
                    @php
                        $badgeClass = match($ticket->status->name) {
                            'DIAJUKAN' => 'diajukan',
                            'DIVERIFIKASI' => 'diverifikasi',
                            'DIPROSES' => 'diproses',
                            'SELESAI' => 'selesai',
                            'DITOLAK' => 'ditolak',
                            default => 'diajukan'
                        };
                    @endphp
                    <span class="badge-status {{ $badgeClass }}">
                        {{ $ticket->status->label }}
                    </span>
                    <span class="badge-sla {{ match($ticket->sla_status) { 'OVERDUE' => 'overdue', 'NEAR_DEADLINE' => 'near', 'COMPLETED' => 'completed', default => 'normal' } }}">
                        SLA: {{ $ticket->sla_duration }} {{ $ticket->sla_unit }}
                    </span>
                </div>
                <h3 style="font-size: 18px; font-weight: 600; color: var(--gray-800); margin: 0 0 8px 0;">
                    {{ $ticket->subject }}
                </h3>
                <div style="font-size: 14px; color: var(--gray-500); display: flex; gap: 16px; flex-wrap: wrap;">
                    <span><i class="fas fa-cogs"></i> Layanan: <strong>{{ $ticket->service->name }}</strong></span>
                    <span><i class="fas fa-calendar-alt"></i> Dibuat: {{ $ticket->created_at->translatedFormat('d M Y, H:i') }}</span>
                    <span><i class="fas fa-clock"></i> Batas Waktu: {{ $ticket->deadline ? $ticket->deadline->translatedFormat('d M Y, H:i') : '—' }}</span>
                </div>
            </div>

            <!-- Action Buttons for Staff -->
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                @if($ticket->status->name === \App\Models\Status::DIVERIFIKASI || $ticket->status->name === \App\Models\Status::DIAJUKAN)
                    <form action="{{ route('staff.tickets.status', $ticket) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="{{ \App\Models\Status::DIPROSES }}">
                        <input type="hidden" name="note" value="Staf mulai memproses pengaduan/layanan.">
                        <button type="submit" class="btn btn-primary" onclick="return confirm('Mulai proses pengerjaan tiket ini sekarang?')">
                            <i class="fas fa-play"></i> Mulai Kerjakan (DIPROSES)
                        </button>
                    </form>
                @endif

                @if($ticket->status->name === \App\Models\Status::DIPROSES)
                    <button type="button" class="btn btn-primary" style="background: var(--status-selesai); border-color: var(--status-selesai);" onclick="openSelesaiModal()">
                        <i class="fas fa-check-double"></i> Selesaikan Tiket (SELESAI)
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- 2 Kolom Layout -->
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; align-items: start;">
        
        <!-- Kolom Kiri: Rincian & Chat -->
        <div>
            <!-- Deskripsi & Isian Formulir -->
            <div class="card" style="margin-bottom: 24px;">
                <div class="card-header">
                    <h2><i class="fas fa-file-alt" style="color: var(--primary); margin-right: 8px;"></i> Rincian Pengaduan Pemohon</h2>
                </div>
                <div class="card-body">
                    <div style="font-size: 15px; line-height: 1.6; color: var(--gray-800); white-space: pre-line; background: var(--surface-alt); padding: 16px; border-radius: 8px; border: 1px solid var(--gray-200);">
                        {{ $ticket->description }}
                    </div>

                    @if($ticket->fieldValues->count() > 0)
                    <div style="margin-top: 20px;">
                        <h4 style="font-size: 14px; font-weight: 700; color: var(--gray-700); margin-bottom: 12px;">Data Isian Formulir:</h4>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                            @foreach($ticket->fieldValues as $fv)
                            <div style="background: var(--surface-alt); padding: 10px 14px; border-radius: 8px; border: 1px solid var(--gray-200);">
                                <div style="font-size: 12px; color: var(--gray-500); font-weight: 600;">{{ $fv->serviceField->label ?? 'Field' }}</div>
                                <div style="font-size: 14px; font-weight: 600; color: var(--gray-900); margin-top: 2px;">{{ $fv->value ?: '—' }}</div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if($ticket->attachments->count() > 0)
                    <div style="margin-top: 20px;">
                        <h4 style="font-size: 14px; font-weight: 700; color: var(--gray-700); margin-bottom: 12px;">
                            <i class="fas fa-paperclip" style="color: var(--primary); margin-right: 6px;"></i>
                            Berkas & Dokumen Lampiran:
                        </h4>
                        <div style="display: flex; flex-direction: column; gap: 10px;">
                            @foreach($ticket->attachments as $att)
                            @php
                                $isImg = Str::startsWith($att->mime_type, 'image/') || in_array(strtolower(pathinfo($att->original_name, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp', 'gif']);
                            @endphp
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 14px; background: var(--surface-alt); border: 1px solid var(--gray-200); border-radius: 10px; gap: 12px; flex-wrap: wrap;">
                                <div style="display: flex; align-items: center; gap: 12px; min-width: 0;">
                                    @if($isImg)
                                        <a href="javascript:void(0)" onclick="openPhotoViewer('{{ route('tickets.attachments.show', [$ticket, $att]) }}', '{{ addslashes($att->original_name) }}')" title="Klik untuk melihat foto">
                                            <img src="{{ route('tickets.attachments.show', [$ticket, $att]) }}" alt="{{ $att->original_name }}" style="width: 52px; height: 52px; object-fit: cover; border-radius: 8px; border: 1px solid var(--gray-300); display: block; cursor: pointer;">
                                        </a>
                                    @else
                                        <div style="width: 52px; height: 52px; border-radius: 8px; background: rgba(10, 94, 61, 0.1); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0;">
                                            <i class="fas fa-file-pdf"></i>
                                        </div>
                                    @endif
                                    <div style="min-width: 0;">
                                        <div style="font-weight: 600; font-size: 14px; color: var(--gray-900); word-break: break-all;">
                                            {{ $att->original_name }}
                                        </div>
                                        <div style="font-size: 12px; color: var(--gray-500); margin-top: 2px;">
                                            @if($att->requirement)
                                                <span style="background: rgba(10, 94, 61, 0.08); color: var(--primary); padding: 1px 6px; border-radius: 4px; font-weight: 600;">{{ $att->requirement->name }}</span>
                                            @else
                                                <span style="background: rgba(187, 143, 50, 0.12); color: #8A671C; padding: 1px 6px; border-radius: 4px; font-weight: 600;">Bukti Penyelesaian Staf</span>
                                            @endif
                                            &bull; {{ number_format($att->file_size / 1024, 1) }} KB
                                        </div>
                                    </div>
                                </div>
                                <div style="display: flex; gap: 8px;">
                                    @if($isImg)
                                        <button type="button" onclick="openPhotoViewer('{{ route('tickets.attachments.show', [$ticket, $att]) }}', '{{ addslashes($att->original_name) }}')" class="btn btn-sm btn-primary" style="background: var(--primary); border: none; padding: 6px 14px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;">
                                            <i class="fas fa-eye"></i> Lihat Foto
                                        </button>
                                    @endif
                                    <a href="{{ route('tickets.attachments.show', [$ticket, $att]) }}" target="_blank" download="{{ $att->original_name }}" class="btn btn-sm btn-outline" style="padding: 6px 12px; font-size: 12px;">
                                        <i class="fas fa-download"></i> Unduh
                                    </a>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if($ticket->resolution_note)
                    <div style="margin-top: 20px; padding: 14px; background: rgba(16,185,129,0.08); border-left: 4px solid var(--status-selesai); border-radius: 6px;">
                        <div style="font-weight: 700; font-size: 14px; color: var(--status-selesai); margin-bottom: 4px;">
                            <i class="fas fa-check-circle"></i> Catatan Hasil Penyelesaian:
                        </div>
                        <div style="font-size: 14px; color: var(--gray-800);">{{ $ticket->resolution_note }}</div>
                    </div>
                    @endif

                    <!-- Penilaian Kepuasan dari Pemohon jika ada -->
                    @if($ticket->rating)
                    <div style="margin-top: 16px; padding: 14px; background: rgba(187, 143, 50, 0.08); border-left: 4px solid #BB8F32; border-radius: 6px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                            <div style="font-weight: 700; font-size: 14px; color: #8A671C;">
                                <i class="fas fa-star" style="color: #BB8F32;"></i> Penilaian Kepuasan Pemohon:
                            </div>
                            <div style="display: flex; align-items: center; gap: 3px;">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star" style="font-size: 13px; color: {{ $i <= $ticket->rating->rating ? '#BB8F32' : '#D1D5DB' }};"></i>
                                @endfor
                                <span style="font-weight: 700; margin-left: 4px; color: #1A1305; font-size: 13px;">{{ $ticket->rating->rating }}/5</span>
                            </div>
                        </div>
                        @if($ticket->rating->comment)
                            <div style="font-size: 14px; color: var(--gray-800); font-style: italic;">"{{ $ticket->rating->comment }}"</div>
                        @else
                            <div style="font-size: 13px; color: var(--gray-500); font-style: italic;">Pemohon tidak menyertakan komentar tambahan.</div>
                        @endif
                        <div style="font-size: 11px; color: var(--gray-500); margin-top: 6px;">Dinilai pada {{ $ticket->rating->created_at->translatedFormat('d M Y, H:i') }}</div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Percakapan / Chat dengan Pemohon -->
            <div class="card">
                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <h2><i class="fas fa-comments" style="color: var(--primary); margin-right: 8px;"></i> Diskusi dengan Pemohon</h2>
                    <span style="font-size: 12px; color: var(--gray-500);">Maksimal 50 karakter per pesan</span>
                </div>
                <div class="card-body">
                    <div style="display: flex; flex-direction: column; gap: 14px; margin-bottom: 20px; max-height: 400px; overflow-y: auto; padding-right: 6px;">
                        @forelse($ticket->messages as $msg)
                            @php
                                $isMe = $msg->user_id === auth()->id();
                            @endphp
                            <div style="display: flex; flex-direction: column; align-items: {{ $isMe ? 'flex-end' : 'flex-start' }};">
                                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                                    <span style="font-weight: 600; font-size: 12px; color: var(--gray-700);">{{ $msg->user->nama }}</span>
                                    <span style="font-size: 12px; color: var(--gray-400);">{{ $msg->created_at->translatedFormat('d M, H:i') }}</span>
                                </div>
                                <div style="max-width: 80%; padding: 10px 14px; border-radius: 12px; font-size: 14px; line-height: 1.5; {{ $isMe ? 'background: var(--primary); color: white; border-bottom-right-radius: 2px;' : 'background: var(--surface-alt); border: 1px solid var(--gray-200); color: var(--gray-900); border-bottom-left-radius: 2px;' }}">
                                    {{ $msg->message }}
                                </div>
                            </div>
                        @empty
                            <div style="text-align: center; color: var(--gray-400); padding: 24px; font-size: 14px;">
                                Belum ada percakapan pada tiket ini. Anda dapat mengirim pesan klarifikasi ke pemohon di bawah.
                            </div>
                        @endforelse
                    </div>

                    @if($ticket->status->name === 'DIPROSES')
                    <form action="{{ route('staff.tickets.messages.store', $ticket) }}" method="POST" style="display: flex; gap: 10px;">
                        @csrf
                        <input type="text" name="message" class="form-control" required maxlength="50" placeholder="Ketik pesan ke pemohon (maksimal 50 karakter)..." autocomplete="off">
                        <button type="submit" class="btn btn-primary" style="padding: 10px 20px;">
                            <i class="fas fa-paper-plane"></i> Kirim
                        </button>
                    </form>
                    @else
                    <div style="padding: 12px 14px; background: var(--surface-alt); border: 1px solid var(--gray-200); border-radius: 8px; text-align: center; font-size: 12px; color: var(--gray-500);">
                        <i class="fas fa-lock" style="margin-right: 4px;"></i> Fitur percakapan hanya aktif saat status tiket <strong>DIPROSES</strong>.
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Data Pemohon & Instruksi Manajer -->
        <div>
            <!-- Info Pemohon -->
            <div class="card" style="margin-bottom: 24px;">
                <div class="card-header">
                    <h2><i class="fas fa-user" style="color: var(--primary); margin-right: 8px;"></i> Pemohon</h2>
                </div>
                <div class="card-body">
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
                        <div style="width: 44px; height: 44px; border-radius: 50%; background: linear-gradient(135deg, var(--primary), var(--accent)); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 18px;">
                            {{ strtoupper(substr($ticket->user->nama, 0, 2)) }}
                        </div>
                        <div>
                            <div style="font-weight: 700; color: var(--gray-900); font-size: 15px;">{{ $ticket->user->nama }}</div>
                            <div style="font-size: 12px; color: var(--primary); font-weight: 600;">{{ $ticket->user->userType->label }}</div>
                        </div>
                    </div>

                    <div style="font-size: 14px; display: flex; flex-direction: column; gap: 8px;">
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: var(--gray-500);">Identitas (NIM/NIP):</span>
                            <span style="font-weight: 600; font-variant-numeric: tabular-nums; font-feature-settings: 'tnum';">{{ $ticket->user->nomor_identitas }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: var(--gray-500);">Email:</span>
                            <span style="font-weight: 600;">{{ $ticket->user->email }}</span>
                        </div>
                        @if($ticket->user->programStudy)
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: var(--gray-500);">Prodi:</span>
                            <span style="font-weight: 600;">{{ $ticket->user->programStudy->name }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Instruksi dari Manajer & Tim Pelaksana -->
            @php
                $teamAssignments = $ticket->assignments->unique('staff_id')->values();
            @endphp
            @if($teamAssignments->count() > 0)
            <div class="card" style="margin-bottom: 24px; border-left: 4px solid var(--primary);">
                <div class="card-header">
                    <h2><i class="fas fa-clipboard-list" style="color: var(--primary); margin-right: 8px;"></i> Instruksi Admin &amp; Tim</h2>
                </div>
                <div class="card-body">
                    @if($teamAssignments->count() > 1)
                    <div style="margin-bottom: 12px; padding-bottom: 10px; border-bottom: 1px solid var(--gray-200);">
                        <div style="font-size: 12px; font-weight: 700; color: var(--gray-600); margin-bottom: 6px;">
                            <i class="fas fa-users" style="color: var(--primary); margin-right: 4px;"></i> Tim Staf Pelaksana ({{ $teamAssignments->count() }} orang):
                        </div>
                        <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                            @foreach($teamAssignments as $asg)
                                @if($asg->staff)
                                    <span style="display: inline-flex; align-items: center; gap: 5px; padding: 3px 9px; border-radius: 999px; font-size: 12px; font-weight: 600; background: {{ $asg->staff_id === auth()->id() ? 'rgba(10, 94, 61, 0.12)' : 'var(--surface-alt)' }}; color: {{ $asg->staff_id === auth()->id() ? 'var(--primary)' : 'var(--gray-800)' }}; border: 1px solid {{ $asg->staff_id === auth()->id() ? 'rgba(10, 94, 61, 0.25)' : 'var(--gray-200)' }};">
                                        <i class="fas fa-user-check" style="font-size: 10px;"></i>
                                        {{ $asg->staff->nama }}{{ $asg->staff_id === auth()->id() ? ' (Anda)' : '' }}
                                    </span>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    @endif
                    <div style="font-size: 14px; color: var(--gray-800); line-height: 1.5;">
                        {{ $ticket->latestAssignment->notes ?: 'Tidak ada instruksi khusus dari admin.' }}
                    </div>
                    <div style="font-size: 12px; color: var(--gray-400); margin-top: 10px;">
                        Ditugaskan oleh: {{ $ticket->latestAssignment->assignedByUser->nama ?? 'Admin' }} ({{ $ticket->latestAssignment->assigned_at->translatedFormat('d M, H:i') }})
                    </div>
                </div>
            </div>
            @endif

            <!-- Riwayat Status (Timeline) -->
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-history" style="color: var(--primary); margin-right: 8px;"></i> Riwayat Status</h2>
                </div>
                <div class="card-body">
                    <div style="display: flex; flex-direction: column; gap: 14px; position: relative; padding-left: 20px;">
                        <div style="position: absolute; left: 6px; top: 8px; bottom: 8px; width: 2px; background: var(--gray-200);"></div>
                        @foreach($ticket->statusHistories as $hist)
                        <div style="position: relative;">
                            <div style="position: absolute; left: -20px; top: 3px; width: 14px; height: 14px; border-radius: 50%; background: var(--primary); border: 2px solid white;"></div>
                            <div style="font-weight: 700; font-size: 14px; color: var(--gray-900);">
                                {{ $hist->toStatus->label }}
                            </div>
                            <div style="font-size: 12px; color: var(--gray-600); margin-top: 2px;">
                                {{ $hist->note ?: 'Perubahan status' }}
                            </div>
                            <div style="font-size: 12px; color: var(--gray-400); margin-top: 2px;">
                                {{ $hist->changedByUser->nama ?? 'Sistem' }} &bull; {{ $hist->created_at->translatedFormat('d M Y, H:i') }}
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Selesaikan Tiket (SELESAI) -->
    <div class="modal-backdrop" id="selesaiModalBackdrop" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
        <div class="card" style="width: 100%; max-width: 550px; margin: 20px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <h3 style="margin: 0; font-size: 18px; font-weight: 700; color: var(--status-selesai);">Selesaikan Pengerjaan Tiket</h3>
                <button type="button" onclick="closeSelesaiModal()" style="background: none; border: none; font-size: 18px; cursor: pointer; color: var(--gray-400);">&times;</button>
            </div>
            <form action="{{ route('staff.tickets.status', $ticket) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="{{ \App\Models\Status::SELESAI }}">
                <div class="card-body">
                    <p style="font-size: 14px; color: var(--gray-600); margin-top: 0;">
                        Tuliskan catatan penyelesaian atau tindak lanjut yang telah dilakukan agar pemohon mengetahuinya.
                    </p>
                    <div class="form-group">
                        <label class="form-label">Catatan Penyelesaian <span class="required">*</span></label>
                        <textarea name="resolution_note" class="form-control" rows="4" required placeholder="Contoh: Nilai telah diperbarui di portal akademik sesuai berita acara nilai dari dosen pengampu..."></textarea>
                    </div>
                    <div class="form-group" style="margin-top: 14px;">
                        <label class="form-label">Unggah Foto / Berkas Bukti Penyelesaian (Opsional)</label>
                        <input type="file" name="proofs[]" multiple accept=".jpg,.jpeg,.png,.pdf" class="form-control" style="padding: 8px;">
                        <small style="color: var(--gray-500); font-size: 12px; margin-top: 4px; display: block;">
                            Foto tangkapan layar, bukti nilai/berkas yang sudah diproses (JPG, PNG, PDF. Maks: 5MB).
                        </small>
                    </div>
                </div>
                <div style="padding: 16px 24px; background: var(--surface-alt); border-top: 1px solid var(--gray-200); display: flex; justify-content: flex-end; gap: 10px; border-radius: 0 0 12px 12px;">
                    <button type="button" class="btn btn-outline" onclick="closeSelesaiModal()">Batal</button>
                    <button type="submit" class="btn btn-primary" style="background: var(--status-selesai); border-color: var(--status-selesai);">
                        <i class="fas fa-check"></i> Konfirmasi Selesai
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Pratinjau Foto dengan Tombol Kembali -->
    <div id="photoViewerModal" style="display: none; position: fixed; inset: 0; background: rgba(0, 0, 0, 0.85); z-index: 9999; align-items: center; justify-content: center; padding: 20px; flex-direction: column;" onclick="if(event.target === this) closePhotoViewer();">
        <div style="background: #ffffff; border-radius: 16px; max-width: 800px; width: 100%; max-height: 90vh; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);">
            <!-- Topbar Modal Foto -->
            <div style="padding: 14px 20px; border-bottom: 1px solid var(--line); display: flex; justify-content: space-between; align-items: center; background: #FAF9F5;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-image" style="color: var(--primary);"></i>
                    <span id="photoViewerTitle" style="font-size: 14px; font-weight: 700; color: var(--gray-800); max-width: 420px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Pratinjau Foto</span>
                </div>
                <button type="button" onclick="closePhotoViewer()" style="background: none; border: none; font-size: 22px; color: var(--gray-400); cursor: pointer; line-height: 1;" title="Tutup">&times;</button>
            </div>

            <!-- Body Modal Foto -->
            <div style="padding: 20px; display: flex; align-items: center; justify-content: center; background: #111827; flex: 1; overflow: auto; min-height: 280px;">
                <img id="photoViewerImg" src="" alt="Foto" style="max-width: 100%; max-height: 65vh; object-fit: contain; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.4);">
            </div>

            <!-- Footer Modal Foto dengan Tombol Kembali Hijau & Unduh -->
            <div style="padding: 14px 20px; border-top: 1px solid var(--line); display: flex; justify-content: space-between; align-items: center; background: #FAF9F5;">
                <button type="button" onclick="closePhotoViewer()" style="background: var(--primary); color: #ffffff; border: none; border-radius: 9999px; padding: 9px 24px; font-size: 13px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fas fa-arrow-left"></i> Kembali
                </button>
                <div style="display: flex; gap: 8px;">
                    <a id="photoViewerDownload" href="" target="_blank" download class="btn btn-sm btn-outline" style="font-size: 13px; padding: 8px 16px;">
                        <i class="fas fa-download"></i> Unduh Foto
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openSelesaiModal() {
            document.getElementById('selesaiModalBackdrop').style.display = 'flex';
        }
        function closeSelesaiModal() {
            document.getElementById('selesaiModalBackdrop').style.display = 'none';
        }

        function openPhotoViewer(url, title) {
            document.getElementById('photoViewerImg').src = url;
            document.getElementById('photoViewerTitle').innerText = title || 'Pratinjau Foto';
            document.getElementById('photoViewerDownload').href = url;
            document.getElementById('photoViewerModal').style.display = 'flex';
        }
        function closePhotoViewer() {
            document.getElementById('photoViewerModal').style.display = 'none';
            document.getElementById('photoViewerImg').src = '';
        }
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closePhotoViewer();
        });
    </script>
@endsection
