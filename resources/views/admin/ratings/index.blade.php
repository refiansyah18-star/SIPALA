@extends('layouts.app')

@section('title', 'Penilaian Layanan & Kepuasan Pengguna')
@section('page-title', 'Penilaian & Kepuasan Layanan')

@section('content')
<div style="max-width: 1100px; margin: 0 auto; padding: 10px 16px 40px;">

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
        <div>
            <h2 style="font-size: 24px; font-weight: 700; color: #14472A; margin: 0 0 6px 0; letter-spacing: -0.02em;">
                Penilaian Kepuasan Layanan (CSAT)
            </h2>
            <p style="color: var(--muted); font-size: 14px; margin: 0;">
                Laporan feedback dan tingkat kepuasan sivitas akademika terhadap layanan fakultas.
            </p>
        </div>
    </div>

    {{-- CSAT Metrics Grid --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
        {{-- Card 1: Rata-Rata Skor --}}
        <div class="card" style="background: var(--surface); border: 1px solid var(--line); border-radius: 16px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
            <div style="display: flex; align-items: center; gap: 14px;">
                <div style="width: 48px; height: 48px; border-radius: 14px; background: rgba(187, 143, 50, 0.12); color: #BB8F32; display: flex; align-items: center; justify-content: center; font-size: 22px;">
                    <i class="fas fa-star"></i>
                </div>
                <div>
                    <div style="font-size: 12px; font-weight: 600; color: var(--muted); text-transform: uppercase; letter-spacing: 0.5px;">Rata-Rata Rating</div>
                    <div style="font-size: 26px; font-weight: 800; color: var(--ink); line-height: 1.2;">
                        {{ $averageRating }} <span style="font-size: 14px; font-weight: 500; color: var(--muted);">/ 5.0</span>
                    </div>
                </div>
            </div>
            <div style="display: flex; gap: 3px; margin-top: 12px;">
                @for($i = 1; $i <= 5; $i++)
                    <i class="fas fa-star" style="font-size: 14px; color: {{ $i <= round($averageRating) ? '#BB8F32' : '#E5E7EB' }};"></i>
                @endfor
            </div>
        </div>

        {{-- Card 2: Total Ulasan --}}
        <div class="card" style="background: var(--surface); border: 1px solid var(--line); border-radius: 16px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
            <div style="display: flex; align-items: center; gap: 14px;">
                <div style="width: 48px; height: 48px; border-radius: 14px; background: rgba(10, 94, 61, 0.1); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 22px;">
                    <i class="fas fa-comments"></i>
                </div>
                <div>
                    <div style="font-size: 12px; font-weight: 600; color: var(--muted); text-transform: uppercase; letter-spacing: 0.5px;">Total Ulasan</div>
                    <div style="font-size: 26px; font-weight: 800; color: var(--ink); line-height: 1.2;">
                        {{ $totalRatings }}
                    </div>
                </div>
            </div>
            <div style="font-size: 12px; color: var(--muted); margin-top: 12px;">
                Ulasan masuk dari pemohon tiket
            </div>
        </div>

        {{-- Card 3: Tingkat Kepuasan --}}
        <div class="card" style="background: var(--surface); border: 1px solid var(--line); border-radius: 16px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
            <div style="display: flex; align-items: center; gap: 14px;">
                <div style="width: 48px; height: 48px; border-radius: 14px; background: rgba(34, 197, 94, 0.12); color: #16A34A; display: flex; align-items: center; justify-content: center; font-size: 22px;">
                    <i class="fas fa-smile"></i>
                </div>
                <div>
                    <div style="font-size: 12px; font-weight: 600; color: var(--muted); text-transform: uppercase; letter-spacing: 0.5px;">Kepuasan Layanan</div>
                    <div style="font-size: 26px; font-weight: 800; color: var(--ink); line-height: 1.2;">
                        {{ $satisfactionPercentage }}%
                    </div>
                </div>
            </div>
            <div style="font-size: 12px; color: var(--muted); margin-top: 12px;">
                Persentase ulasan bintang 4 & 5
            </div>
        </div>

        {{-- Card 4: Tiket Selesai Belum Dinilai --}}
        <div class="card" style="background: var(--surface); border: 1px solid var(--line); border-radius: 16px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
            <div style="display: flex; align-items: center; gap: 14px;">
                <div style="width: 48px; height: 48px; border-radius: 14px; background: rgba(245, 158, 11, 0.12); color: #D97706; display: flex; align-items: center; justify-content: center; font-size: 22px;">
                    <i class="fas fa-bell"></i>
                </div>
                <div>
                    <div style="font-size: 12px; font-weight: 600; color: var(--muted); text-transform: uppercase; letter-spacing: 0.5px;">Belum Dinilai</div>
                    <div style="font-size: 26px; font-weight: 800; color: var(--ink); line-height: 1.2;">
                        {{ isset($unratedTickets) ? $unratedTickets->count() : 0 }}
                    </div>
                </div>
            </div>
            <div style="font-size: 12px; color: var(--muted); margin-top: 12px;">
                Tiket selesai menunggu rating pemohon
            </div>
        </div>
    </div>

    {{-- Section: Tiket Selesai yang Belum Diberi Rating (Reminder Gmail) --}}
    @if(isset($unratedTickets) && $unratedTickets->count() > 0)
    <div class="card" style="background: var(--surface); border: 1px solid var(--line); border-radius: 16px; padding: 20px; margin-bottom: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
        <div style="display: flex; justify-content: space-between; align-items: center; gap: 14px; flex-wrap: wrap; margin-bottom: 14px;">
            <div>
                <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: var(--ink);">
                    <i class="fas fa-envelope-open-text" style="color: #BB8F32; margin-right: 6px;"></i>
                    Tiket Selesai Belum Diberi Rating ({{ $unratedTickets->count() }})
                </h3>
                <p style="margin: 3px 0 0 0; font-size: 12.5px; color: var(--muted);">
                    Pengingat otomatis dikirim setiap minggu ke Gmail pemohon, atau kirim pengingat sekarang secara langsung.
                </p>
            </div>
            <form action="{{ route('admin.ratings.remind-all') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-sm btn-primary" style="padding: 8px 16px; font-size: 13px;">
                    <i class="fas fa-paper-plane"></i> Kirim Pengingat ke Semua ({{ $unratedTickets->count() }})
                </button>
            </form>
        </div>

        <div style="display: flex; flex-direction: column; gap: 8px;">
            @foreach($unratedTickets as $ut)
            @php
                $lastReminder = $ut->emailNotifications
                    ->where('type', \App\Models\EmailNotification::TYPE_RATING_REMINDER)
                    ->where('status', \App\Models\EmailNotification::STATUS_SENT)
                    ->sortByDesc('sent_at')
                    ->first();
            @endphp
            <div style="display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; padding: 10px 14px; background: var(--canvas); border: 1px solid var(--line); border-radius: 10px;">
                <div style="min-width: 0;">
                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                        <a href="{{ route('admin.tickets.show', $ut) }}" style="font-weight: 700; font-size: 13.5px; color: var(--primary); text-decoration: none;">
                            {{ $ut->ticket_number }}
                        </a>
                        <span style="font-size: 13.5px; font-weight: 600; color: var(--ink);">{{ $ut->subject }}</span>
                    </div>
                    <div style="font-size: 12px; color: var(--muted); margin-top: 2px;">
                        Pemohon: <strong>{{ $ut->user->nama }}</strong> ({{ $ut->user->email }})
                        &bull; Selesai: {{ ($ut->resolved_at ?? $ut->updated_at)->translatedFormat('d M Y, H:i') }}
                        @if($lastReminder && $lastReminder->sent_at)
                            &bull; <span style="color: var(--primary); font-weight: 600;">Terakhir diingatkan: {{ $lastReminder->sent_at->translatedFormat('d M Y, H:i') }}</span>
                        @endif
                    </div>
                </div>
                <form action="{{ route('admin.tickets.remind-rating', $ut) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline" style="font-size: 12px; padding: 6px 12px; white-space: nowrap;">
                        <i class="fas fa-bell"></i> Ingatkan via Email
                    </button>
                </form>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Filter Card --}}
    <div class="card" style="background: var(--surface); border: 1px solid var(--line); border-radius: 16px; padding: 18px 20px; margin-bottom: 24px;">
        <form action="{{ route('admin.ratings.index') }}" method="GET" style="display: flex; gap: 14px; align-items: center; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 180px;">
                <select name="score" class="form-control" style="width: 100%; border: 1px solid var(--line); border-radius: 8px; padding: 10px 14px; font-size: 14px; background: #fff;">
                    <option value="">Semua Skor Bintang</option>
                    @for($s = 5; $s >= 1; $s--)
                        <option value="{{ $s }}" {{ request('score') == $s ? 'selected' : '' }}>
                            ⭐ {{ $s }} Bintang ({{ $starCounts[$s] ?? 0 }})
                        </option>
                    @endfor
                </select>
            </div>

            <div style="flex: 1; min-width: 200px;">
                <select name="category_id" class="form-control" style="width: 100%; border: 1px solid var(--line); border-radius: 8px; padding: 10px 14px; font-size: 14px; background: #fff;">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" style="background: #1A6B3C; color: #fff; border: none; border-radius: 8px; padding: 10px 20px; font-size: 14px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                <i class="fas fa-filter"></i> Filter
            </button>

            @if(request()->hasAny(['score', 'category_id']))
                <a href="{{ route('admin.ratings.index') }}" style="color: var(--muted); font-size: 13px; text-decoration: none; padding: 8px 12px;">
                    Reset Filter
                </a>
            @endif
        </form>
    </div>

    {{-- Ratings Table --}}
    <div class="card" style="background: var(--surface); border: 1px solid var(--line); border-radius: 16px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 14px;">
                <thead>
                    <tr style="background: var(--canvas); border-bottom: 1px solid var(--line); color: var(--muted); font-size: 12px; font-weight: 700; text-transform: uppercase;">
                        <th style="padding: 14px 18px;">Tiket</th>
                        <th style="padding: 14px 18px;">Pemohon</th>
                        <th style="padding: 14px 18px;">Layanan</th>
                        <th style="padding: 14px 18px;">Penilaian</th>
                        <th style="padding: 14px 18px;">Komentar / Masukan</th>
                        <th style="padding: 14px 18px;">Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ratings as $rating)
                        <tr style="border-bottom: 1px solid var(--line); transition: background 0.15s ease;">
                            <td style="padding: 14px 18px; font-weight: 700;">
                                <a href="{{ route('admin.tickets.show', $rating->ticket) }}" style="color: var(--primary); text-decoration: none;">
                                    {{ $rating->ticket->ticket_number }}
                                </a>
                            </td>
                            <td style="padding: 14px 18px;">
                                <div style="font-weight: 600; color: var(--ink);">{{ $rating->ticket->user->nama ?? '-' }}</div>
                                <div style="font-size: 11px; color: var(--muted);">{{ $rating->ticket->user->userType->label ?? '' }}</div>
                            </td>
                            <td style="padding: 14px 18px;">
                                <div style="color: var(--ink); font-weight: 500;">{{ $rating->ticket->service->name ?? '-' }}</div>
                                <div style="font-size: 11px; color: var(--muted);">{{ $rating->ticket->category->name ?? '' }}</div>
                            </td>
                            <td style="padding: 14px 18px; white-space: nowrap;">
                                <div style="display: flex; align-items: center; gap: 4px;">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fas fa-star" style="font-size: 13px; color: {{ $i <= $rating->rating ? '#BB8F32' : '#E5E7EB' }};"></i>
                                    @endfor
                                    <span style="font-weight: 700; color: #1A1305; font-size: 13px; margin-left: 4px;">{{ $rating->rating }}</span>
                                </div>
                            </td>
                            <td style="padding: 14px 18px; color: var(--ink); max-width: 280px;">
                                @if($rating->comment)
                                    <span style="font-style: italic;">"{{ $rating->comment }}"</span>
                                @else
                                    <span style="color: var(--muted); font-size: 12px;">(Tanpa komentar)</span>
                                @endif
                            </td>
                            <td style="padding: 14px 18px; color: var(--muted); font-size: 12px; white-space: nowrap;">
                                {{ $rating->created_at->translatedFormat('d M Y, H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding: 40px 20px; text-align: center; color: var(--muted);">
                                <i class="fas fa-inbox" style="font-size: 32px; display: block; margin-bottom: 8px; opacity: 0.4;"></i>
                                Belum ada data penilaian layanan yang cocok dengan filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($ratings->hasPages())
            <div style="padding: 16px 20px; border-top: 1px solid var(--line);">
                {{ $ratings->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

