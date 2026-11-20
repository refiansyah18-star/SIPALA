@extends('layouts.app')

@section('title', 'Penilaian Layanan Akademik')
@section('page-title', 'Penilaian Layanan')

@section('content')
<div style="max-width: 900px; margin: 0 auto; padding: 10px 16px 40px;">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
        <div>
            <h2 style="font-size: 24px; font-weight: 700; color: #14472A; margin: 0 0 6px 0; letter-spacing: -0.02em;">
                Penilaian Kepuasan Layanan
            </h2>
            <p style="color: var(--muted); font-size: 14px; margin: 0;">
                Beri ulasan dan rating untuk pengaduan atau layanan yang telah diselesaikan.
            </p>
        </div>
        <a href="{{ route('user.tickets.index') }}" class="back-link" style="color: var(--primary); font-size: 14px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; text-decoration: none;">
            <i class="fas fa-arrow-left"></i> Kembali ke Tiket Saya
        </a>
    </div>

    @if(session('success'))
        <div style="background: rgba(10, 94, 61, 0.1); border: 1px solid rgba(10, 94, 61, 0.25); color: #0A5E3D; padding: 12px 18px; border-radius: 12px; margin-bottom: 20px; font-size: 14px; font-weight: 600; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-check-circle"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- Unrated Alert Banner --}}
    @if($unratedCount > 0)
        <div style="background: linear-gradient(135deg, rgba(187, 143, 50, 0.15) 0%, rgba(212, 174, 90, 0.1) 100%); border: 1px solid rgba(187, 143, 50, 0.35); border-radius: 14px; padding: 16px 20px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
            <div style="display: flex; align-items: center; gap: 14px;">
                <div style="width: 42px; height: 42px; border-radius: 12px; background: #BB8F32; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0;">
                    <i class="fas fa-star"></i>
                </div>
                <div>
                    <div style="font-weight: 700; color: #1A1305; font-size: 15px;">
                        Ada {{ $unratedCount }} tiket selesai yang belum dinilai!
                    </div>
                    <div style="font-size: 13px; color: #785A18; margin-top: 2px;">
                        Ulasan Anda sangat berharga untuk peningkatan mutu layanan fakultas.
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Tickets List --}}
    @if($tickets->count() > 0)
        <div style="display: flex; flex-direction: column; gap: 16px;">
            @foreach($tickets as $ticket)
                <div class="card" style="background: var(--surface); border: 1px solid var(--line); border-radius: 16px; padding: 20px; transition: transform 0.15s ease, box-shadow 0.15s ease; box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px; margin-bottom: 12px;">
                        <div>
                            <div style="font-size: 12px; font-weight: 700; color: var(--primary); letter-spacing: 0.5px; margin-bottom: 4px;">
                                {{ $ticket->ticket_number }}
                            </div>
                            <h3 style="font-size: 16px; font-weight: 700; color: var(--ink); margin: 0 0 6px 0;">
                                <a href="{{ route('user.tickets.show', $ticket) }}" style="color: inherit; text-decoration: none;">
                                    {{ $ticket->subject }}
                                </a>
                            </h3>
                            <div style="display: flex; align-items: center; gap: 14px; font-size: 12px; color: var(--muted); flex-wrap: wrap;">
                                <span><i class="fas fa-tag" style="margin-right: 4px;"></i>{{ $ticket->service->category->name }} / {{ $ticket->service->name }}</span>
                                <span><i class="fas fa-check-circle" style="color: var(--primary); margin-right: 4px;"></i>Diselesaikan: {{ $ticket->resolved_at ? $ticket->resolved_at->translatedFormat('d M Y') : $ticket->updated_at->translatedFormat('d M Y') }}</span>
                            </div>
                        </div>

                        {{-- Rating Status Badge --}}
                        <div>
                            @if($ticket->rating)
                                <div style="display: inline-flex; align-items: center; gap: 6px; background: rgba(187, 143, 50, 0.12); color: #8A671C; border: 1px solid rgba(187, 143, 50, 0.25); padding: 6px 14px; border-radius: 9999px; font-size: 13px; font-weight: 700;">
                                    <div style="display: flex; gap: 2px;">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fas fa-star" style="font-size: 12px; color: {{ $i <= $ticket->rating->rating ? '#BB8F32' : '#D1D5DB' }};"></i>
                                        @endfor
                                    </div>
                                    <span>{{ $ticket->rating->rating }}/5</span>
                                </div>
                            @else
                                <span style="background: rgba(239, 68, 68, 0.1); color: #DC2626; border: 1px solid rgba(239, 68, 68, 0.2); padding: 5px 12px; border-radius: 9999px; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 5px;">
                                    <i class="fas fa-exclamation-circle"></i> Belum Dinilai
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Rating Content / Form --}}
                    @if($ticket->rating)
                        <div style="background: var(--canvas); border-radius: 12px; padding: 12px 16px; margin-top: 10px;">
                            @if($ticket->rating->comment)
                                <p style="font-size: 13px; color: var(--ink); font-style: italic; margin: 0 0 6px 0;">
                                    "{{ $ticket->rating->comment }}"
                                </p>
                            @else
                                <p style="font-size: 13px; color: var(--muted); font-style: italic; margin: 0 0 6px 0;">
                                    Tidak ada catatan ulasan tambahan.
                                </p>
                            @endif
                            <div style="display: flex; justify-content: space-between; align-items: center; font-size: 11px; color: var(--muted);">
                                <span>Dinilai pada {{ $ticket->rating->created_at->translatedFormat('d M Y, H:i') }}</span>
                                <button type="button" onclick="toggleEditRating('{{ $ticket->id }}')" style="background: none; border: none; color: var(--primary); font-weight: 600; cursor: pointer; padding: 0;">
                                    <i class="fas fa-edit"></i> Ubah Penilaian
                                </button>
                            </div>

                            {{-- Edit Form (Hidden by default) --}}
                            <div id="editBox_{{ $ticket->id }}" style="display: none; margin-top: 14px; padding-top: 14px; border-top: 1px dashed var(--line);">
                                <form action="{{ route('user.tickets.rating.store', $ticket) }}" method="POST">
                                    @csrf
                                    <div style="margin-bottom: 10px;">
                                        <div style="font-size: 12px; font-weight: 700; color: var(--ink); margin-bottom: 6px;">Ubah Skor Bintang:</div>
                                        <div class="star-rating-row" data-ticket="{{ $ticket->id }}" style="display: flex; gap: 8px;">
                                            @for($i = 1; $i <= 5; $i++)
                                                <i class="fas fa-star edit-star-{{ $ticket->id }}" data-score="{{ $i }}" style="font-size: 24px; cursor: pointer; color: {{ $i <= $ticket->rating->rating ? '#BB8F32' : '#D1D5DB' }};"></i>
                                            @endfor
                                        </div>
                                        <input type="hidden" name="rating" id="ratingInput_{{ $ticket->id }}" value="{{ $ticket->rating->rating }}" required>
                                    </div>
                                    <textarea name="comment" rows="2" placeholder="Tulis komentar ulasan (opsional)..." style="width: 100%; border: 1px solid var(--line); border-radius: 8px; padding: 8px 12px; font-size: 13px; font-family: inherit; resize: none; outline: none; box-sizing: border-box; background: var(--surface);">{{ $ticket->rating->comment }}</textarea>
                                    <div style="display: flex; gap: 8px; margin-top: 10px;">
                                        <button type="submit" style="background: #1A6B3C; color: #fff; border: none; border-radius: 9999px; padding: 8px 24px; font-size: 13px; font-weight: 700; cursor: pointer;">
                                            Simpan
                                        </button>
                                        <button type="button" onclick="toggleEditRating('{{ $ticket->id }}')" style="background: none; border: 1px solid var(--line); color: var(--muted); border-radius: 9999px; padding: 8px 18px; font-size: 13px; cursor: pointer;">
                                            Batal
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @else
                        {{-- Quick Submit Rating Form --}}
                        <div style="background: rgba(187, 143, 50, 0.05); border: 1px solid rgba(187, 143, 50, 0.2); border-radius: 12px; padding: 14px 18px; margin-top: 10px;">
                            <form action="{{ route('user.tickets.rating.store', $ticket) }}" method="POST">
                                @csrf
                                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; margin-bottom: 10px;">
                                    <span style="font-size: 13px; font-weight: 700; color: #14472A;">
                                        Beri Penilaian Layanan Ini:
                                    </span>
                                    <div class="star-rating-row" data-ticket="{{ $ticket->id }}" style="display: flex; gap: 8px;">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fas fa-star new-star-{{ $ticket->id }}" data-score="{{ $i }}" style="font-size: 26px; cursor: pointer; color: #D1D5DB;"></i>
                                        @endfor
                                    </div>
                                    <input type="hidden" name="rating" id="ratingInput_{{ $ticket->id }}" value="" required>
                                </div>
                                <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                                    <input type="text" name="comment" placeholder="Komentar / ulasan pengalaman Anda (opsional)..." style="flex: 1; min-width: 220px; border: 1px solid var(--line); border-radius: 8px; padding: 8px 12px; font-size: 13px; font-family: inherit; outline: none; background: #fff;">
                                    <button type="submit" style="background: #1A6B3C; color: #fff; border: none; border-radius: 9999px; padding: 9px 24px; font-size: 13px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 3px 10px rgba(26,107,60,0.2);">
                                        <i class="fas fa-paper-plane"></i> Kirim
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <div style="margin-top: 24px;">
            {{ $tickets->links() }}
        </div>
    @else
        <div style="text-align: center; padding: 60px 20px; background: var(--surface); border-radius: 16px; border: 1px solid var(--line);">
            <div style="width: 64px; height: 64px; border-radius: 20px; background: rgba(187, 143, 50, 0.1); color: #BB8F32; display: inline-flex; align-items: center; justify-content: center; font-size: 28px; margin-bottom: 16px;">
                <i class="fas fa-star"></i>
            </div>
            <h3 style="font-size: 18px; font-weight: 700; color: var(--ink); margin: 0 0 6px 0;">
                Belum Ada Tiket yang Selesai
            </h3>
            <p style="font-size: 14px; color: var(--muted); margin: 0 auto; max-width: 440px;">
                Penilaian dapat diberikan setelah permohonan atau pengaduan Anda telah selesai ditangani oleh staf.
            </p>
            <div style="margin-top: 20px;">
                <a href="{{ route('user.tickets.index') }}" style="background: var(--primary); color: #fff; padding: 10px 24px; border-radius: 9999px; text-decoration: none; font-size: 14px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px;">
                    <i class="fas fa-ticket-alt"></i> Lihat Tiket Saya
                </a>
            </div>
        </div>
    @endif
</div>

<script>
    function toggleEditRating(ticketId) {
        const box = document.getElementById('editBox_' + ticketId);
        if (box) {
            box.style.display = box.style.display === 'none' ? 'block' : 'none';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Handle star clicking for new and edit ratings
        document.querySelectorAll('.star-rating-row').forEach(function(row) {
            const ticketId = row.getAttribute('data-ticket');
            const hiddenInput = document.getElementById('ratingInput_' + ticketId);
            const stars = Array.from(row.querySelectorAll('.fa-star'));

            function setStars(score) {
                stars.forEach(function(s, idx) {
                    if (idx < score) {
                        s.style.color = '#BB8F32';
                    } else {
                        s.style.color = '#D1D5DB';
                    }
                });
            }

            stars.forEach(function(star) {
                star.addEventListener('click', function() {
                    const score = parseInt(this.getAttribute('data-score'));
                    if (hiddenInput) hiddenInput.value = score;
                    setStars(score);
                });

                star.addEventListener('mouseover', function() {
                    const score = parseInt(this.getAttribute('data-score'));
                    setStars(score);
                });

                star.addEventListener('mouseout', function() {
                    const currentScore = hiddenInput && hiddenInput.value ? parseInt(hiddenInput.value) : 0;
                    setStars(currentScore);
                });
            });
        });
    });
</script>
@endsection

