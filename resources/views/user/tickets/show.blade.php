@extends('layouts.app')

@section('title', 'Detail Tiket ' . $ticket->ticket_number)
@section('page-title', 'Detail Pengaduan')

@push('styles')
<style>
    /* =============================================
       TICKET DETAIL — Compact, mobile-friendly
       ============================================= */
    .ticket-layout {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        align-items: start;
    }

    /* --- Left: Ticket Info + Status Stepper --- */
    .ticket-info {
        background: var(--surface);
        border: 1px solid var(--gray-100);
        border-radius: var(--radius-lg);
        padding: 20px;
    }

    .ticket-number {
        font-size: 12px;
        font-weight: 700;
        font-variant-numeric: tabular-nums;
        font-feature-settings: "tnum";
        color: var(--primary);
        letter-spacing: 0.5px;
    }

    .ticket-subject {
        font-size: 18px;
        font-weight: 700;
        color: var(--gray-900);
        margin: 6px 0 8px;
        line-height: 1.35;
    }

    .ticket-meta {
        display: flex;
        flex-direction: column;
        gap: 3px;
        font-size: 12px;
        color: var(--gray-500);
        margin-bottom: 20px;
    }

    .ticket-meta span {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .ticket-meta i {
        width: 14px;
        text-align: center;
        color: var(--gray-400);
        font-size: 12px;
    }

    /* Stepper */
    .status-stepper {
        display: flex;
        flex-direction: column;
        gap: 0;
        padding-left: 0;
    }

    .step {
        display: flex;
        align-items: stretch;
        gap: 14px;
        position: relative;
    }

    .step-indicator {
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 20px;
        flex-shrink: 0;
        position: relative;
    }

    .step-dot {
        width: 18px;
        height: 18px;
        border-radius: 50%;
        border: 2px solid var(--gray-300);
        background: var(--surface);
        position: relative;
        z-index: 2;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        box-sizing: border-box;
    }

    .step-dot.completed {
        border-color: var(--primary);
        background: var(--primary);
    }

    .step-dot.completed::after {
        content: '';
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #FFFFFF;
    }

    .step-dot.active {
        border-color: var(--primary);
        background: var(--surface);
        box-shadow: 0 0 0 3.5px rgba(26, 107, 60, 0.15);
    }

    .step-dot.active::after {
        content: '';
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: var(--primary);
    }

    .step-line {
        width: 2px;
        flex: 1;
        background: var(--gray-200);
        margin-top: -1px;
        margin-bottom: -1px;
        position: relative;
        z-index: 1;
    }

    .step-line.completed {
        background: var(--primary);
    }

    .step-content {
        padding-bottom: 22px;
    }

    .step:last-child .step-content {
        padding-bottom: 0;
    }

    .step-label {
        font-size: 14px;
        font-weight: 600;
        color: var(--gray-400);
        line-height: 18px;
    }

    .step-label.completed,
    .step-label.active {
        color: var(--gray-900);
    }

    .step-time {
        font-size: 12px;
        color: var(--gray-400);
        margin-top: 2px;
        line-height: 16px;
    }

    /* --- Rincian Section --- */
    .detail-section {
        margin-top: 18px;
        padding-top: 16px;
        border-top: 1px solid var(--gray-100);
    }

    .detail-section h4 {
        font-size: 12px;
        font-weight: 700;
        color: var(--gray-500);
        text-transform: none;
        letter-spacing: normal;
        margin-bottom: 8px;
    }

    .detail-desc {
        font-size: 14px;
        line-height: 1.55;
        color: var(--gray-700);
        white-space: pre-line;
        background: var(--surface-alt);
        padding: 12px;
        border-radius: 8px;
        border: 1px solid var(--gray-100);
    }

    .attachment-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 10px;
        background: var(--surface-alt);
        border: 1px solid var(--gray-100);
        border-radius: 6px;
        font-size: 12px;
        margin-top: 6px;
    }

    .attachment-item a {
        color: var(--primary);
        font-weight: 600;
        font-size: 12px;
    }

    /* --- Right: Chat / Tanggapan --- */
    .ticket-chat {
        background: var(--surface);
        border: 1px solid var(--gray-100);
        border-radius: var(--radius-lg);
        display: flex;
        flex-direction: column;
        max-height: 600px;
    }

    .chat-header {
        padding: 14px 18px;
        border-bottom: 1px solid var(--gray-100);
        font-size: 14px;
        font-weight: 700;
        color: var(--gray-900);
    }

    .chat-messages {
        flex: 1;
        padding: 16px;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 10px;
        min-height: 200px;
    }

    .chat-empty {
        text-align: center;
        color: var(--gray-400);
        font-size: 12px;
        padding: 32px 16px;
    }

    .chat-bubble-wrap {
        display: flex;
        flex-direction: column;
        max-width: 85%;
    }

    .chat-bubble-wrap.mine {
        align-self: flex-end;
        align-items: flex-end;
    }

    .chat-bubble-wrap.theirs {
        align-self: flex-start;
        align-items: flex-start;
    }

    .chat-sender {
        font-size: 12px;
        font-weight: 600;
        color: var(--gray-500);
        margin-bottom: 3px;
        padding: 0 4px;
    }

    .chat-bubble {
        padding: 9px 14px;
        border-radius: 10px;
        font-size: 14px;
        line-height: 1.45;
    }

    .chat-bubble.mine {
        background: var(--primary);
        color: white;
        border-bottom-right-radius: 3px;
    }

    .chat-bubble.theirs {
        background: var(--primary-50);
        color: var(--gray-900);
        border-bottom-left-radius: 3px;
    }

    .chat-time {
        font-size: 12px;
        color: var(--gray-400);
        margin-top: 2px;
        padding: 0 4px;
    }

    .chat-form {
        padding: 10px 16px 12px;
        border-top: 1px solid var(--gray-100);
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .chat-input-wrapper {
        display: flex;
        gap: 8px;
    }

    .chat-input-wrapper input {
        flex: 1;
        border: 1px solid var(--gray-200);
        border-radius: 8px;
        padding: 9px 12px;
        font-size: 15px;
        font-family: inherit;
        color: var(--gray-800);
        background: var(--surface-alt);
        transition: all 0.2s;
    }

    .chat-input-wrapper input:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 2px rgba(26,107,60,0.08);
        background: var(--surface);
    }

    .chat-input-wrapper button {
        padding: 9px 16px;
        background: var(--primary);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        font-family: inherit;
        cursor: pointer;
        transition: all 0.15s;
        white-space: nowrap;
    }

    .chat-input-wrapper button:hover:not(:disabled) {
        background: var(--primary-dark);
    }

    .chat-input-wrapper button:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .chat-input-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 12px;
    }

    .word-counter {
        color: var(--gray-400);
        font-weight: 500;
        font-size: 12px;
    }

    .word-counter.limit-exceeded {
        color: #DC2626;
        font-weight: 700;
    }

    .chat-error-text {
        color: #DC2626;
        font-size: 12px;
    }

    /* --- Status Banners --- */
    .status-banner {
        padding: 12px 16px;
        border-radius: var(--radius-md);
        font-size: 14px;
        line-height: 1.45;
        margin-bottom: 14px;
        display: flex;
        gap: 10px;
        align-items: flex-start;
    }

    .status-banner i {
        margin-top: 1px;
        flex-shrink: 0;
    }

    .status-banner.success {
        background: #F0FDF4;
        border: 1px solid #BBF7D0;
        color: #166534;
    }

    .status-banner.danger {
        background: #FEF2F2;
        border: 1px solid #FECACA;
        color: #991B1B;
    }

    /* --- Footer --- */
    .ticket-footer {
        text-align: center;
        font-size: 12px;
        color: var(--gray-400);
        margin-top: 16px;
        padding-top: 12px;
        border-top: 1px solid var(--gray-100);
    }

    /* --- Responsive --- */
    @media (max-width: 768px) {
        .ticket-layout {
            grid-template-columns: 1fr;
        }
        .ticket-info { padding: 16px; }
        .ticket-subject { font-size: 18px; }
        .ticket-chat { max-height: 500px; }
        .chat-bubble-wrap { max-width: 90%; }
        .chat-input-wrapper input { font-size: 16px !important; }
    }

    @media (max-width: 480px) {
        .ticket-info { padding: 14px; }
        .ticket-subject { font-size: 15px; }
        .step-label { font-size: 12px; }
        .chat-messages { padding: 12px; }
        .chat-bubble { font-size: 14px; padding: 8px 12px; }
    }
</style>
@endpush

@section('content')
    <div style="margin-bottom: 16px;">
        <a href="{{ route('user.tickets.index') }}" class="back-link" style="color: var(--primary); font-size: 14px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; text-decoration: none;">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    {{-- Status Banners --}}
    @if($ticket->status->name === 'SELESAI')
    <div class="status-banner success">
        <i class="fas fa-check-circle"></i>
        <div>
            <strong>Pengaduan Selesai</strong><br>
            {{ $ticket->resolution_note ?: 'Permohonan Anda telah berhasil ditangani oleh staf kami.' }}
        </div>
    </div>
    @endif

    @if($ticket->status->name === 'DITOLAK')
    <div class="status-banner danger">
        <i class="fas fa-times-circle"></i>
        <div>
            <strong>Pengajuan Ditolak</strong><br>
            {{ $ticket->rejection_reason ?: 'Pengajuan belum memenuhi persyaratan.' }}
        </div>
    </div>
    @endif

    <div class="ticket-layout">
        {{-- Left Column: Info + Stepper --}}
        <div class="ticket-info">
            <div class="ticket-number">{{ $ticket->ticket_number }}</div>
            <h2 class="ticket-subject">{{ $ticket->subject }}</h2>
            <div class="ticket-meta">
                <span><i class="fas fa-th-large"></i> {{ $ticket->service->category->name }} / {{ $ticket->service->name }}</span>
                <span><i class="fas fa-calendar"></i> {{ $ticket->created_at->translatedFormat('d F Y') }}</span>
            </div>

            {{-- Status Stepper --}}
            @php
                $allSteps = ['DIAJUKAN', 'DIVERIFIKASI', 'DIPROSES', 'SELESAI'];
                $currentStatus = $ticket->status->name;

                // If rejected, use a different flow
                $isRejected = $currentStatus === 'DITOLAK';

                $stepLabels = [
                    'DIAJUKAN' => 'Diajukan',
                    'DIVERIFIKASI' => 'Diverifikasi',
                    'DIPROSES' => 'Diproses',
                    'SELESAI' => 'Selesai',
                ];

                // Find the index of current status in the normal flow
                $currentIdx = array_search($currentStatus, $allSteps);
                if ($currentIdx === false) $currentIdx = 0;

                // Build a map of status name -> timestamp from history
                $historyMap = [];
                foreach ($ticket->statusHistories as $hist) {
                    $historyMap[$hist->toStatus->name] = $hist->created_at;
                }
            @endphp

            <div class="status-stepper">
                @foreach($allSteps as $i => $stepName)
                    @php
                        $isCompleted = $i < $currentIdx || $currentStatus === 'SELESAI' && $i <= $currentIdx;
                        $isActive = $i === $currentIdx && $currentStatus !== 'SELESAI';
                        $isLast = $i === count($allSteps) - 1;

                        if ($currentStatus === 'SELESAI') {
                            $isCompleted = true;
                            $isActive = false;
                        }

                        $timestamp = $historyMap[$stepName] ?? null;
                    @endphp
                    <div class="step">
                        <div class="step-indicator">
                            <div class="step-dot {{ $isCompleted ? 'completed' : ($isActive ? 'active' : '') }}"></div>
                            @if(!$isLast)
                                <div class="step-line {{ $isCompleted ? 'completed' : '' }}"></div>
                            @endif
                        </div>
                        <div class="step-content">
                            <div class="step-label {{ $isCompleted ? 'completed' : ($isActive ? 'active' : '') }}">
                                {{ $stepLabels[$stepName] }}
                            </div>
                            @if($timestamp)
                                <div class="step-time">{{ $timestamp->translatedFormat('d M Y, H:i') }}</div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Rincian --}}
            @if($ticket->description)
            <div class="detail-section">
                <h4>Rincian Pengaduan</h4>
                <div class="detail-desc">{{ $ticket->description }}</div>
            </div>
            @endif

            {{-- Lampiran --}}
            @if($ticket->attachments->count() > 0)
            <div class="detail-section">
                <h4>Lampiran & Berkas Bukti</h4>
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    @foreach($ticket->attachments as $att)
                    @php
                        $isImg = Str::startsWith($att->mime_type, 'image/') || in_array(strtolower(pathinfo($att->original_name, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp', 'gif']);
                    @endphp
                    <div class="attachment-item" style="display: flex; justify-content: space-between; align-items: center; gap: 10px; flex-wrap: wrap;">
                        <div style="display: flex; align-items: center; gap: 10px; min-width: 0;">
                            @if($isImg)
                                <a href="javascript:void(0)" onclick="openPhotoViewer('{{ route('tickets.attachments.show', [$ticket, $att]) }}', '{{ addslashes($att->original_name) }}')" title="Klik untuk melihat foto">
                                    <img src="{{ route('tickets.attachments.show', [$ticket, $att]) }}" alt="{{ $att->original_name }}" style="width: 44px; height: 44px; object-fit: cover; border-radius: 6px; border: 1px solid var(--gray-200); display: block; cursor: pointer;">
                                </a>
                            @else
                                <i class="fas fa-paperclip" style="color: var(--gray-400); font-size: 16px;"></i>
                            @endif
                            <div style="min-width: 0;">
                                <div style="font-size: 13px; font-weight: 600; color: var(--gray-800); word-break: break-all;">{{ $att->original_name }}</div>
                                <div style="font-size: 11px; color: var(--gray-500);">
                                    {{ $att->requirement ? $att->requirement->name : 'Bukti Penyelesaian Staf' }} &bull; {{ number_format($att->file_size / 1024, 1) }} KB
                                </div>
                            </div>
                        </div>
                        <div style="display: flex; gap: 6px;">
                            @if($isImg)
                                <button type="button" onclick="openPhotoViewer('{{ route('tickets.attachments.show', [$ticket, $att]) }}', '{{ addslashes($att->original_name) }}')" style="background: none; border: none; color: var(--primary); font-size: 12px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 3px; padding: 4px 6px;">
                                    <i class="fas fa-eye"></i> Lihat
                                </button>
                            @endif
                            <a href="{{ route('tickets.attachments.show', [$ticket, $att]) }}" target="_blank" download="{{ $att->original_name }}"><i class="fas fa-download"></i> Unduh</a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- Right Column: Chat / Tanggapan --}}
        <div class="ticket-chat">
            <div class="chat-header" style="display: flex; justify-content: space-between; align-items: center;">
                <span><i class="fas fa-comments" style="color: var(--primary); margin-right: 6px;"></i> Tanggapan</span>
                @if($ticket->status->name === 'DIPROSES')
                    <span style="font-size: 12px; font-weight: 500; color: var(--gray-500);"><i class="fas fa-info-circle"></i> Maks. 50 karakter</span>
                @else
                    <span style="font-size: 12px; font-weight: 500; color: var(--gray-400);"><i class="fas fa-lock"></i> Chat Nonaktif</span>
                @endif
            </div>

            <div class="chat-messages" id="chatMessages">
                @forelse($ticket->messages as $msg)
                    @php $isMe = $msg->user_id === auth()->id(); @endphp
                    <div class="chat-bubble-wrap {{ $isMe ? 'mine' : 'theirs' }}">
                        <div class="chat-sender">
                            {{ $isMe ? 'Anda' : $msg->user->nama }}
                        </div>
                        <div class="chat-bubble {{ $isMe ? 'mine' : 'theirs' }}">
                            {{ $msg->message }}
                        </div>
                        <div class="chat-time">{{ $msg->created_at->translatedFormat('d M, H:i') }}</div>
                    </div>
                @empty
                    <div class="chat-empty">
                        <i class="fas fa-comment-slash" style="font-size: 22px; display: block; margin-bottom: 8px; opacity: 0.4;"></i>
                        Belum ada tanggapan
                    </div>
                @endforelse
            </div>

            @if($ticket->status->name === 'DIPROSES')
            <form action="{{ route('user.tickets.messages.store', $ticket) }}" method="POST" class="chat-form">
                @csrf
                <div class="chat-input-wrapper">
                    <input type="text" name="message" id="chatMessageInput" required maxlength="50" placeholder="Tulis pesan (maks. 50 karakter)..." autocomplete="off" value="{{ old('message') }}">
                    <button type="submit" id="chatSubmitBtn">Kirim</button>
                </div>
                <div class="chat-input-footer">
                    <span id="wordCountLabel" class="word-counter">0/50 karakter</span>
                    @error('message')
                        <span class="chat-error-text">{{ $message }}</span>
                    @enderror
                </div>
            </form>
            @else
            <div style="padding: 14px 16px; border-top: 1px solid var(--gray-100); background: var(--surface-alt); text-align: center; font-size: 12px; color: var(--gray-500); line-height: 1.4;">
                <i class="fas fa-lock" style="color: var(--gray-400); margin-right: 5px;"></i>
                @if(in_array($ticket->status->name, ['SELESAI', 'DITOLAK']))
                    Percakapan ditutup karena pengaduan sudah <strong>{{ strtolower($ticket->status->label) }}</strong>.
                @else
                    Fitur chat / tanggapan hanya dapat digunakan saat pengaduan berstatus <strong>Diproses</strong>.
                @endif
            </div>
            @endif
        </div>
    </div>

    {{-- Rating Section (hanya tampil jika tiket SELESAI) --}}
    @if($ticket->status->name === 'SELESAI')
    <div style="margin-top: 20px; background: var(--surface); border: 1px solid var(--gray-100); border-radius: var(--radius-lg); padding: 20px;">
        @if($ticket->rating)
            {{-- Sudah memberi rating --}}
            <div style="text-align: center; padding: 8px 0;">
                <div style="font-size: 13px; font-weight: 700; color: #14472A; margin-bottom: 10px;">
                    <i class="fas fa-star" style="color: #BB8F32; margin-right: 6px;"></i>Penilaian Anda
                </div>
                <div style="display: flex; justify-content: center; gap: 6px; margin-bottom: 10px;">
                    @for($i = 1; $i <= 5; $i++)
                        <i class="fas fa-star" style="font-size: 24px; color: {{ $i <= $ticket->rating->rating ? '#BB8F32' : '#E5E7EB' }};"></i>
                    @endfor
                </div>
                @if($ticket->rating->comment)
                    <p style="font-size: 13px; color: var(--gray-600); font-style: italic; margin: 0;">"{{ $ticket->rating->comment }}"</p>
                @endif
                <p style="font-size: 11px; color: var(--gray-400); margin-top: 8px;">
                    Dikirim {{ $ticket->rating->created_at->translatedFormat('d M Y') }}
                    &nbsp;·&nbsp;
                    <a href="#" onclick="document.getElementById('ratingFormBox').style.display='block'; this.parentElement.style.display='none'; return false;" style="color: var(--primary); text-decoration: none; font-weight: 600;">Ubah penilaian</a>
                </p>
            </div>
            {{-- Form ubah rating (tersembunyi) --}}
            <div id="ratingFormBox" style="display:none;">
                <form action="{{ route('user.tickets.rating.store', $ticket) }}" method="POST">
                    @csrf
                    <div style="text-align: center; margin-bottom: 12px;">
                        <div style="font-size: 13px; font-weight: 700; color: #14472A; margin-bottom: 10px;">Ubah Penilaian Layanan</div>
                        <div class="star-rating-wrap" style="display: flex; justify-content: center; gap: 8px; margin-bottom: 12px;">
                            @for($i = 5; $i >= 1; $i--)
                                <input type="radio" id="star_edit{{ $i }}" name="rating" value="{{ $i }}" {{ $ticket->rating->rating == $i ? 'checked' : '' }} style="display:none;">
                                <label for="star_edit{{ $i }}" style="font-size: 28px; color: #E5E7EB; cursor: pointer;" class="star-label">★</label>
                            @endfor
                        </div>
                        <textarea name="comment" rows="2" placeholder="Tambahkan komentar (opsional)..." style="width: 100%; border: 1px solid #E5E7EB; border-radius: 8px; padding: 10px 14px; font-size: 14px; font-family: inherit; resize: none; outline: none; box-sizing: border-box;">{{ $ticket->rating->comment }}</textarea>
                        <button type="submit" style="margin-top: 10px; background: #1A6B3C; color: #fff; border: none; border-radius: 9999px; padding: 10px 32px; font-size: 14px; font-weight: 700; cursor: pointer;">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        @else
            {{-- Belum memberi rating --}}
            <div style="text-align: center;">
                <div style="font-size: 13px; font-weight: 700; color: #14472A; margin-bottom: 4px;">
                    <i class="fas fa-star" style="color: #BB8F32; margin-right: 6px;"></i>Beri Penilaian Layanan
                </div>
                <p style="font-size: 12px; color: var(--gray-500); margin: 0 0 14px;">Bantu kami meningkatkan kualitas layanan dengan memberikan penilaian Anda.</p>
                <form action="{{ route('user.tickets.rating.store', $ticket) }}" method="POST">
                    @csrf
                    <div class="star-rating-wrap" style="display: flex; justify-content: center; gap: 8px; margin-bottom: 14px;">
                        @for($i = 5; $i >= 1; $i--)
                            <input type="radio" id="star{{ $i }}" name="rating" value="{{ $i }}" required style="display:none;">
                            <label for="star{{ $i }}" style="font-size: 32px; color: #E5E7EB; cursor: pointer;" class="star-label">★</label>
                        @endfor
                    </div>
                    <textarea name="comment" rows="2" placeholder="Komentar / catatan (opsional)..." style="width: 100%; border: 1px solid #E5E7EB; border-radius: 8px; padding: 10px 14px; font-size: 14px; font-family: inherit; resize: none; outline: none; box-sizing: border-box;"></textarea>
                    @error('rating')<div style="color: #DC2626; font-size: 12px; margin-top: 4px;">{{ $message }}</div>@enderror
                    <button type="submit" style="margin-top: 12px; background: #1A6B3C; color: #fff; border: none; border-radius: 9999px; padding: 10px 36px; font-size: 14px; font-weight: 700; cursor: pointer; box-shadow: 0 4px 12px rgba(26,107,60,0.2);">
                        <i class="fas fa-paper-plane" style="margin-right: 6px;"></i>Kirim Penilaian
                    </button>
                </form>
            </div>
        @endif
    </div>
    @endif

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

    <div class="ticket-footer">
        SIPALA — Sistem Pengaduan Layanan Akademik
    </div>
@endsection

@push('scripts')
<script>
    // Auto-scroll chat to bottom
    const chat = document.getElementById('chatMessages');
    if (chat) chat.scrollTop = chat.scrollHeight;

    // Real-time character counter (maks. 50 karakter: huruf, spasi, tanda baca)
    const chatInput = document.getElementById('chatMessageInput');
    const charCountLabel = document.getElementById('wordCountLabel');
    const submitBtn = document.getElementById('chatSubmitBtn');

    if (chatInput && charCountLabel) {
        const updateCharCount = () => {
            const len = chatInput.value.length;
            charCountLabel.textContent = `${len}/50 karakter`;
            if (len > 50) {
                charCountLabel.classList.add('limit-exceeded');
                if (submitBtn) submitBtn.disabled = true;
            } else {
                charCountLabel.classList.remove('limit-exceeded');
                if (submitBtn) submitBtn.disabled = len === 0;
            }
        };

        chatInput.addEventListener('input', updateCharCount);
        updateCharCount();
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

    // Star rating interaction
    document.querySelectorAll('.star-rating-wrap').forEach(function(wrap) {
        const labels = Array.from(wrap.querySelectorAll('.star-label')).reverse(); // reversed because RTL input order
        const inputs = Array.from(wrap.querySelectorAll('input[type=radio]')).reverse();

        function colorStars(upTo) {
            labels.forEach(function(lbl, idx) {
                lbl.style.color = idx <= upTo ? '#BB8F32' : '#E5E7EB';
            });
        }

        // Set initial color based on checked
        inputs.forEach(function(inp, idx) {
            if (inp.checked) colorStars(idx);
        });

        labels.forEach(function(lbl, idx) {
            lbl.addEventListener('mouseover', function() { colorStars(idx); });
            lbl.addEventListener('mouseout', function() {
                const checkedIdx = inputs.findIndex(function(i){ return i.checked; });
                colorStars(checkedIdx >= 0 ? checkedIdx : -1);
            });
            lbl.addEventListener('click', function() { colorStars(idx); });
        });
    });
</script>
@endpush
