@extends('layouts.app')

@section('title', 'Detail Tiket ' . $ticket->ticket_number)
@section('page-title', 'Detail Tiket Layanan')

@section('content')
    <div style="margin-bottom: 20px;">
        <a href="{{ route('admin.tickets.index') }}" class="back-link" style="color: var(--primary); font-size: 14px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; text-decoration: none;">
            <i class="fas fa-arrow-left"></i> Kembali ke Daftar Tiket
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
                    <span class="badge-status {{ $badgeClass }}" style="font-size: 12px; padding: 4px 10px;">
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
                    <span><i class="fas fa-folder"></i> {{ $ticket->category->name }}</span>
                    <span><i class="fas fa-cogs"></i> {{ $ticket->service->name }}</span>
                    <span><i class="fas fa-calendar-alt"></i> Diajukan: {{ $ticket->created_at->translatedFormat('d M Y, H:i') }}</span>
                    <span><i class="fas fa-clock"></i> Batas SLA: {{ $ticket->deadline ? $ticket->deadline->translatedFormat('d M Y, H:i') : '—' }}</span>
                </div>
            </div>

            <!-- Action Buttons for Admin Manager -->
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                @if($ticket->status->name === \App\Models\Status::DIAJUKAN)
                    <form action="{{ route('admin.tickets.verify', $ticket) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-primary" onclick="return confirm('Verifikasi tiket ini dan lanjutkan ke penugasan staf?')">
                            <i class="fas fa-check-circle"></i> Verifikasi Tiket
                        </button>
                    </form>
                @endif

                @if(!$ticket->status->is_terminal)
                    <button type="button" class="btn btn-outline" style="color: var(--status-ditolak); border-color: rgba(239,68,68,0.3);" onclick="openRejectModal()">
                        <i class="fas fa-times-circle"></i> Tolak Tiket
                    </button>

                    <button type="button" class="btn btn-primary" onclick="openAssignModal()">
                        <i class="fas fa-user-plus"></i> {{ $ticket->assignments->isNotEmpty() ? 'Ubah Penugasan Staf (' . $ticket->assignments->unique('staff_id')->count() . ')' : 'Tugaskan ke Staf' }}
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- 2 Kolom Layout -->
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; align-items: start;">
        
        <!-- Kolom Kiri: Detail Pengaduan & Pesan Chat -->
        <div>
            <!-- Deskripsi Pengaduan -->
            <div class="card" style="margin-bottom: 24px;">
                <div class="card-header">
                    <h2><i class="fas fa-align-left" style="color: var(--primary); margin-right: 8px;"></i> Rincian Pengaduan</h2>
                </div>
                <div class="card-body">
                    <div style="font-size: 15px; line-height: 1.6; color: var(--gray-800); white-space: pre-line; background: var(--surface-alt); padding: 16px; border-radius: 8px; border: 1px solid var(--gray-200);">
                        {{ $ticket->description }}
                    </div>

                    <!-- Custom Fields (jika ada) -->
                    @if($ticket->fieldValues->count() > 0)
                    <div style="margin-top: 20px;">
                        <h4 style="font-size: 14px; font-weight: 700; color: var(--gray-700); margin-bottom: 12px;">Data Isian Formulir Tambahan:</h4>
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

                    <!-- Dokumen Persyaratan / Lampiran -->
                    @if($ticket->attachments->count() > 0)
                    <div style="margin-top: 20px;">
                        <h4 style="font-size: 14px; font-weight: 700; color: var(--gray-700); margin-bottom: 12px;">
                            <i class="fas fa-paperclip" style="color: var(--primary); margin-right: 6px;"></i>
                            Dokumen Lampiran & Berkas Bukti:
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
                                        <div style="width: 52px; height: 52px; border-radius: 8px; background: rgba(239, 68, 68, 0.1); color: #EF4444; display: flex; align-items: center; justify-content: center; font-size: 24px; flex-shrink: 0;">
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
                                                <span style="background: rgba(187, 143, 50, 0.12); color: #8A671C; padding: 1px 6px; border-radius: 4px; font-weight: 600;">Berkas / Bukti Penyelesaian</span>
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

                    <!-- Catatan Penyelesaian / Penolakan jika terminal -->
                    @if($ticket->resolution_note)
                    <div style="margin-top: 20px; padding: 14px; background: rgba(16,185,129,0.08); border-left: 4px solid var(--status-selesai); border-radius: 6px;">
                        <div style="font-weight: 700; font-size: 14px; color: var(--status-selesai); margin-bottom: 4px;">
                            <i class="fas fa-check-circle"></i> Catatan Penyelesaian Staf:
                        </div>
                        <div style="font-size: 14px; color: var(--gray-800);">{{ $ticket->resolution_note }}</div>
                    </div>
                    @endif

                    <!-- Penilaian Kepuasan dari Pemohon jika ada -->
                    @if($ticket->rating)
                    <div style="margin-top: 16px; padding: 14px; background: rgba(187, 143, 50, 0.08); border-left: 4px solid #BB8F32; border-radius: 6px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                            <div style="font-weight: 700; font-size: 14px; color: #8A671C;">
                                <i class="fas fa-star" style="color: #BB8F32;"></i> Penilaian Pemohon:
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
                    @elseif($ticket->status->name === \App\Models\Status::SELESAI)
                    <div style="margin-top: 16px; padding: 14px 16px; background: rgba(187, 143, 50, 0.07); border: 1px dashed rgba(187, 143, 50, 0.45); border-radius: 8px; display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap;">
                        <div>
                            <div style="font-weight: 700; font-size: 13.5px; color: #8A671C;">
                                <i class="fas fa-bell" style="color: #BB8F32; margin-right: 4px;"></i> Pemohon Belum Memberikan Penilaian (Rating)
                            </div>
                            <div style="font-size: 12px; color: var(--gray-600); margin-top: 2px;">
                                Sistem mengirimkan pengingat otomatis setiap minggu, atau kirim pengingat sekarang ke <strong>{{ $ticket->user->email }}</strong>.
                            </div>
                        </div>
                        <form action="{{ route('admin.tickets.remind-rating', $ticket) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-primary" style="white-space: nowrap;">
                                <i class="fas fa-paper-plane"></i> Kirim Pengingat Rating
                            </button>
                        </form>
                    </div>
                    @endif

                    @if($ticket->rejection_reason)
                    <div style="margin-top: 20px; padding: 14px; background: rgba(239,68,68,0.08); border-left: 4px solid var(--status-ditolak); border-radius: 6px;">
                        <div style="font-weight: 700; font-size: 14px; color: var(--status-ditolak); margin-bottom: 4px;">
                            <i class="fas fa-times-circle"></i> Alasan Penolakan:
                        </div>
                        <div style="font-size: 14px; color: var(--gray-800);">{{ $ticket->rejection_reason }}</div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Percakapan / Chat Tiket -->
            <div class="card">
                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <h2><i class="fas fa-comments" style="color: var(--primary); margin-right: 8px;"></i> Diskusi Tiket</h2>
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
                                Belum ada percakapan pada tiket ini.
                            </div>
                        @endforelse
                    </div>

                    @if(!$ticket->status->is_terminal)
                    <form action="{{ route('admin.tickets.messages.store', $ticket) }}" method="POST" style="display: flex; gap: 10px;">
                        @csrf
                        <input type="text" name="message" class="form-control" required maxlength="50" placeholder="Tulis pesan (maksimal 50 karakter)..." autocomplete="off">
                        <button type="submit" class="btn btn-primary" style="padding: 10px 20px;">
                            <i class="fas fa-paper-plane"></i> Kirim
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Info Pemohon, Staf Ditugaskan, & Riwayat Timeline -->
        <div>
            <!-- Info Pemohon -->
            <div class="card" style="margin-bottom: 24px;">
                <div class="card-header">
                    <h2><i class="fas fa-user" style="color: var(--primary); margin-right: 8px;"></i> Data Pemohon</h2>
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
                            <span style="color: var(--gray-500);">NIM / NIP:</span>
                            <span style="font-weight: 600; font-variant-numeric: tabular-nums; font-feature-settings: 'tnum';">{{ $ticket->user->nomor_identitas }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: var(--gray-500);">Email:</span>
                            <span style="font-weight: 600;">{{ $ticket->user->email }}</span>
                        </div>
                        @if($ticket->user->programStudy)
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: var(--gray-500);">Program Studi:</span>
                            <span style="font-weight: 600;">{{ $ticket->user->programStudy->name }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Staf Pelaksana yang Ditugaskan -->
            @php
                $uniqueAssignments = $ticket->assignments->unique('staff_id')->values();
            @endphp
            <div class="card" style="margin-bottom: 24px;">
                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <h2><i class="fas fa-users-cog" style="color: var(--primary); margin-right: 6px;"></i> Staf Pelaksana</h2>
                        @if($uniqueAssignments->count() > 0)
                            <span style="background: rgba(10, 94, 61, 0.1); color: var(--primary); font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 999px;">
                                {{ $uniqueAssignments->count() }} Staf
                            </span>
                        @endif
                    </div>
                    @if(!$ticket->status->is_terminal)
                    <button type="button" class="btn btn-sm btn-outline" onclick="openAssignModal()">
                        <i class="fas fa-edit"></i> Ubah
                    </button>
                    @endif
                </div>
                <div class="card-body">
                    @if($uniqueAssignments->count() > 0)
                        <div style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 12px;">
                            @foreach($uniqueAssignments as $assignment)
                                @if($assignment->staff)
                                    @php
                                        $isSpec = $staffMembers->firstWhere('id', $assignment->staff_id)?->is_specialist ?? false;
                                    @endphp
                                    <div style="display: flex; align-items: center; gap: 10px; padding: 10px 12px; background: var(--surface-alt); border: 1px solid var(--gray-200); border-radius: 8px;">
                                        <div style="width: 36px; height: 36px; border-radius: 50%; background: var(--primary-100); color: var(--primary); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px; flex-shrink: 0;">
                                            {{ strtoupper(substr($assignment->staff->nama, 0, 2)) }}
                                        </div>
                                        <div style="min-width: 0; flex: 1;">
                                            <div style="font-weight: 700; color: var(--gray-900); font-size: 13.5px; display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                                <span>{{ $assignment->staff->nama }}</span>
                                                @if($isSpec)
                                                    <span style="background: rgba(10, 94, 61, 0.12); color: var(--primary); font-size: 10.5px; font-weight: 700; padding: 1px 6px; border-radius: 4px; display: inline-flex; align-items: center; gap: 3px;">
                                                        <i class="fas fa-certificate" style="font-size: 10px;"></i> Spesialis
                                                    </span>
                                                @endif
                                            </div>
                                            <div style="font-size: 12px; color: var(--gray-500); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $assignment->staff->email }}</div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                        <div style="font-size: 12px; background: var(--surface-alt); padding: 10px 12px; border-radius: 6px; border: 1px solid var(--gray-200);">
                            <div style="font-weight: 600; color: var(--gray-700); margin-bottom: 2px;">Catatan Penugasan Admin:</div>
                            <div style="color: var(--gray-600); font-style: italic;">
                                {{ $ticket->latestAssignment?->notes ?: 'Tidak ada instruksi khusus.' }}
                            </div>
                            @if($ticket->latestAssignment)
                            <div style="font-size: 12px; color: var(--gray-400); margin-top: 6px;">
                                Ditugaskan oleh: {{ $ticket->latestAssignment->assignedByUser->nama ?? 'Admin' }} ({{ $ticket->latestAssignment->assigned_at->translatedFormat('d M, H:i') }})
                            </div>
                            @endif
                        </div>
                    @else
                        <div style="text-align: center; padding: 16px; background: rgba(245,158,11,0.08); border-radius: 8px; border: 1px dashed #f59e0b;">
                            <i class="fas fa-user-clock" style="font-size: 22px; color: #f59e0b; margin-bottom: 6px;"></i>
                            <div style="font-size: 14px; font-weight: 600; color: var(--gray-800);">Belum Ada Staf Ditugaskan</div>
                            <p style="font-size: 12px; color: var(--gray-500); margin: 4px 0 10px 0;">
                                Silakan centang satu atau beberapa staf pelaksana untuk menangani tiket ini.
                            </p>
                            @if(!$ticket->status->is_terminal)
                            <button type="button" class="btn btn-sm btn-primary" onclick="openAssignModal()">
                                <i class="fas fa-user-plus"></i> Tugaskan Sekarang
                            </button>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Riwayat Status (Timeline) -->
            <div class="card" style="margin-bottom: 24px;">
                <div class="card-header">
                    <h2><i class="fas fa-history" style="color: var(--primary); margin-right: 8px;"></i> Riwayat Perjalanan Tiket</h2>
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
                                Oleh: {{ $hist->changedByUser->nama ?? 'Sistem' }} &bull; {{ $hist->created_at->translatedFormat('d M Y, H:i') }}
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Riwayat Notifikasi Email -->
            @if($ticket->emailNotifications->count() > 0)
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-envelope-open-text" style="color: var(--primary); margin-right: 8px;"></i> Log Notifikasi Email</h2>
                </div>
                <div class="card-body">
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        @foreach($ticket->emailNotifications as $notif)
                        <div style="padding: 10px 12px; background: var(--surface-alt); border-radius: 6px; border: 1px solid var(--gray-200); font-size: 12px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                <span style="font-weight: 700; color: {{ $notif->status === 'SENT' ? 'var(--status-selesai)' : ($notif->status === 'FAILED' ? 'var(--status-ditolak)' : 'var(--status-diproses)') }};">
                                    <i class="fas {{ $notif->status === 'SENT' ? 'fa-check-circle' : 'fa-exclamation-circle' }}"></i>
                                    {{ $notif->type_label }} ({{ $notif->status }})
                                </span>
                                <span style="color: var(--gray-400); font-size: 12px;">
                                    {{ $notif->sent_at ? $notif->sent_at->translatedFormat('d M, H:i') : $notif->created_at->translatedFormat('d M, H:i') }}
                                </span>
                            </div>
                            <div style="color: var(--gray-600); font-size: 12px;">
                                Dikirim ke: <strong>{{ $ticket->user->email }}</strong>
                            </div>
                            @if($notif->error_message)
                            <div style="color: var(--status-ditolak); font-size: 12px; margin-top: 4px; font-variant-numeric: tabular-nums;">
                                Error: {{ Str::limit($notif->error_message, 80) }}
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>

    <!-- Modal Penugasan Staf (Admin Manager Multi-Staff Checkbox Assignment) -->
    @php
        $assignedStaffIds = old('staff_ids', $ticket->assignments->pluck('staff_id')->unique()->values()->toArray());
    @endphp
    <div class="modal-backdrop" id="assignModalBackdrop" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
        <div class="card" style="width: 100%; max-width: 620px; margin: 20px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h3 style="margin: 0; font-size: 18px; font-weight: 700;">Tugaskan Tiket ke Staf</h3>
                    <p style="margin: 2px 0 0 0; font-size: 12px; color: var(--gray-500);">
                        Centang satu atau beberapa staf pelaksana (bisa lebih dari 1 orang). Staf dengan keahlian layanan ini ditandai lencana <strong>Spesialis</strong>.
                    </p>
                </div>
                <button type="button" onclick="closeAssignModal()" style="background: none; border: none; font-size: 18px; cursor: pointer; color: var(--gray-400);">&times;</button>
            </div>
            <form action="{{ route('admin.tickets.assign', $ticket) }}" method="POST" id="assignStaffForm" onsubmit="return validateStaffAssignment()">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; flex-wrap: wrap; gap: 8px;">
                            <label class="form-label" style="margin: 0;">
                                Pilih Staf Pelaksana <span class="required">*</span>
                                <span id="selectedStaffBadge" style="margin-left: 6px; background: rgba(10, 94, 61, 0.1); color: var(--primary); font-size: 11.5px; font-weight: 700; padding: 2px 8px; border-radius: 999px;">
                                    0 dipilih
                                </span>
                            </label>
                            <div style="display: flex; gap: 6px;">
                                <button type="button" class="btn btn-sm btn-outline" style="padding: 3px 8px; font-size: 11.5px;" onclick="selectSpecialistStaff()">
                                    <i class="fas fa-certificate"></i> Pilih Spesialis
                                </button>
                                <button type="button" class="btn btn-sm btn-outline" style="padding: 3px 8px; font-size: 11.5px;" onclick="clearStaffSelection()">
                                    <i class="fas fa-eraser"></i> Reset
                                </button>
                            </div>
                        </div>

                        <div id="assignErrorAlert" style="display: none; margin-bottom: 10px; padding: 8px 12px; border-radius: 8px; background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.25); color: var(--status-ditolak); font-size: 12.5px; font-weight: 600;">
                            <i class="fas fa-exclamation-circle"></i> Silakan centang minimal 1 staf pelaksana terlebih dahulu.
                        </div>

                        <div style="display: flex; flex-direction: column; gap: 8px; max-height: 260px; overflow-y: auto; padding-right: 2px;">
                            @foreach($staffMembers as $staff)
                            @php
                                $isChecked = in_array($staff->id, $assignedStaffIds) || ($loop->first && empty($assignedStaffIds));
                            @endphp
                            <label class="staff-checkbox-card" data-specialist="{{ $staff->is_specialist ? '1' : '0' }}" style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; border: 1.5px solid {{ $isChecked ? 'var(--primary)' : 'var(--gray-200)' }}; border-radius: 10px; cursor: pointer; transition: all 0.15s ease; background: {{ $isChecked ? 'rgba(10, 94, 61, 0.05)' : 'var(--surface-alt)' }};">
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <input type="checkbox" name="staff_ids[]" value="{{ $staff->id }}"
                                        class="staff-assign-checkbox"
                                        onchange="updateStaffCheckboxUI()"
                                        {{ $isChecked ? 'checked' : '' }}
                                        style="width: 17px; height: 17px; accent-color: var(--primary); cursor: pointer; flex-shrink: 0;">
                                    <div>
                                        <div style="font-weight: 600; font-size: 14px; color: var(--gray-900); display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                            <span>{{ $staff->nama }}</span>
                                            @if($staff->is_specialist)
                                                 <span style="background: var(--primary); color: white; font-size: 11px; font-weight: 700; padding: 2px 7px; border-radius: 4px; display: inline-flex; align-items: center; gap: 4px;">
                                                    <i class="fas fa-certificate" style="font-size: 10px;"></i> SPESIALIS
                                                </span>
                                            @endif
                                        </div>
                                        <div style="font-size: 12px; color: var(--gray-500);">{{ $staff->email }}</div>
                                    </div>
                                </div>
                                <div>
                                    <span style="font-size: 12px; font-weight: 600; background: var(--gray-100); padding: 3px 9px; border-radius: 20px; white-space: nowrap;">
                                        {{ $staff->assigned_tickets_count }} tiket aktif
                                    </span>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="form-group" style="margin-top: 14px;">
                        <label class="form-label">Instruksi / Catatan Admin untuk Staf</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Contoh: Tolak jika berkas KTP tidak jelas, atau koordinasikan bersama di lapangan...">{{ old('notes', $ticket->latestAssignment?->notes) }}</textarea>
                    </div>
                </div>
                <div style="padding: 16px 24px; background: var(--surface-alt); border-top: 1px solid var(--gray-200); display: flex; justify-content: flex-end; gap: 10px; border-radius: 0 0 12px 12px;">
                    <button type="button" class="btn btn-outline" onclick="closeAssignModal()">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i> Simpan &amp; Tugaskan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Tolak Tiket -->
    <div class="modal-backdrop" id="rejectModalBackdrop" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
        <div class="card" style="width: 100%; max-width: 500px; margin: 20px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <h3 style="margin: 0; font-size: 18px; font-weight: 700; color: var(--status-ditolak);">Tolak Pengajuan Tiket</h3>
                <button type="button" onclick="closeRejectModal()" style="background: none; border: none; font-size: 18px; cursor: pointer; color: var(--gray-400);">&times;</button>
            </div>
            <form action="{{ route('admin.tickets.reject', $ticket) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="card-body">
                    <p style="font-size: 14px; color: var(--gray-600); margin-top: 0;">
                        Mohon berikan alasan penolakan yang jelas agar pemohon dapat memperbaiki dokumen atau memahami kendala.
                    </p>
                    <div class="form-group">
                        <label class="form-label">Alasan Penolakan <span class="required">*</span></label>
                        <textarea name="rejection_reason" class="form-control" rows="4" required placeholder="Contoh: Berkas formulir belum ditandatangani oleh Dosen PA..."></textarea>
                    </div>
                </div>
                <div style="padding: 16px 24px; background: var(--surface-alt); border-top: 1px solid var(--gray-200); display: flex; justify-content: flex-end; gap: 10px; border-radius: 0 0 12px 12px;">
                    <button type="button" class="btn btn-outline" onclick="closeRejectModal()">Batal</button>
                    <button type="submit" class="btn btn-primary" style="background: var(--status-ditolak); border-color: var(--status-ditolak);">Tolak Tiket</button>
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
        function openAssignModal() {
            document.getElementById('assignModalBackdrop').style.display = 'flex';
            updateStaffCheckboxUI();
        }
        function closeAssignModal() {
            document.getElementById('assignModalBackdrop').style.display = 'none';
            const err = document.getElementById('assignErrorAlert');
            if (err) err.style.display = 'none';
        }
        function updateStaffCheckboxUI() {
            const cards = document.querySelectorAll('.staff-checkbox-card');
            let count = 0;
            cards.forEach(function(card) {
                const cb = card.querySelector('.staff-assign-checkbox');
                if (cb && cb.checked) {
                    count++;
                    card.style.borderColor = 'var(--primary)';
                    card.style.background = 'rgba(10, 94, 61, 0.05)';
                } else {
                    card.style.borderColor = 'var(--gray-200)';
                    card.style.background = 'var(--surface-alt)';
                }
            });
            const badge = document.getElementById('selectedStaffBadge');
            if (badge) {
                badge.textContent = count + ' dipilih';
            }
            const err = document.getElementById('assignErrorAlert');
            if (err && count > 0) {
                err.style.display = 'none';
            }
        }
        function selectSpecialistStaff() {
            const cards = document.querySelectorAll('.staff-checkbox-card');
            let anySpecialist = false;
            cards.forEach(function(card) {
                if (card.getAttribute('data-specialist') === '1') {
                    anySpecialist = true;
                }
            });
            cards.forEach(function(card) {
                const cb = card.querySelector('.staff-assign-checkbox');
                if (cb) {
                    cb.checked = anySpecialist ? (card.getAttribute('data-specialist') === '1') : true;
                }
            });
            updateStaffCheckboxUI();
        }
        function clearStaffSelection() {
            document.querySelectorAll('.staff-assign-checkbox').forEach(function(cb) {
                cb.checked = false;
            });
            updateStaffCheckboxUI();
        }
        function validateStaffAssignment() {
            const checkedCount = document.querySelectorAll('.staff-assign-checkbox:checked').length;
            if (checkedCount === 0) {
                const err = document.getElementById('assignErrorAlert');
                if (err) err.style.display = 'block';
                return false;
            }
            return true;
        }
        document.addEventListener('DOMContentLoaded', updateStaffCheckboxUI);

        function openRejectModal() {
            document.getElementById('rejectModalBackdrop').style.display = 'flex';
        }
        function closeRejectModal() {
            document.getElementById('rejectModalBackdrop').style.display = 'none';
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
