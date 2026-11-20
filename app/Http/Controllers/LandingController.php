<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Service;
use App\Models\Status;
use App\Models\Ticket;
use App\Models\TicketRating;

class LandingController extends Controller
{
    /**
     * Halaman publik SIPALA.
     * Seluruh angka diambil langsung dari database (tanpa data rekaan).
     */
    public function index()
    {
        // =============================================
        // 1. Distribusi status tiket (untuk diagram donat)
        // =============================================
        $statusMeta = [
            Status::DIAJUKAN     => ['label' => 'Diajukan',     'color' => '#D29A1E'],
            Status::DIVERIFIKASI => ['label' => 'Diverifikasi', 'color' => '#7C3AED'],
            Status::DIPROSES     => ['label' => 'Diproses',     'color' => '#3B7DD8'],
            Status::SELESAI      => ['label' => 'Selesai',      'color' => '#1E9E63'],
            Status::DITOLAK      => ['label' => 'Ditolak',      'color' => '#D8463B'],
        ];

        $counts = Ticket::query()
            ->join('statuses', 'statuses.id', '=', 'tickets.status_id')
            ->selectRaw('statuses.name as status_name, COUNT(*) as total')
            ->groupBy('statuses.name')
            ->pluck('total', 'status_name');

        $totalTickets = (int) $counts->sum();

        $distribution = [];
        $offset = 0;
        foreach ($statusMeta as $key => $meta) {
            $n   = (int) ($counts[$key] ?? 0);
            $pct = $totalTickets > 0 ? ($n / $totalTickets) * 100 : 0;

            $distribution[] = [
                'label'  => $meta['label'],
                'color'  => $meta['color'],
                'count'  => $n,
                'pct'    => round($pct, 1),
                'offset' => round($offset, 3),
            ];
            $offset += $pct;
        }

        $completed  = (int) ($counts[Status::SELESAI] ?? 0);
        $inProgress = (int) ($counts[Status::DIVERIFIKASI] ?? 0) + (int) ($counts[Status::DIPROSES] ?? 0);

        // =============================================
        // 2. Kinerja penyelesaian (SLA & rata-rata waktu)
        // =============================================
        $resolved = Ticket::byStatus(Status::SELESAI)
            ->whereNotNull('resolved_at')
            ->get(['created_at', 'resolved_at', 'deadline']);

        $slaRate       = null;
        $avgResolution = null;

        if ($resolved->count() > 0) {
            $onTime  = $resolved->filter(fn ($t) => $t->deadline && $t->resolved_at->lte($t->deadline))->count();
            $slaRate = (int) round(($onTime / $resolved->count()) * 100);

            $avgHours = $resolved->avg(fn ($t) => abs($t->created_at->diffInMinutes($t->resolved_at)) / 60);
            $avgResolution = $avgHours < 24
                ? max(1, (int) round($avgHours)) . ' jam'
                : number_format($avgHours / 24, 1, ',', '.') . ' hari';
        }

        // =============================================
        // 3. Layanan & kategori
        // =============================================
        $activeServices = Service::where('is_active', true)->count();

        $categories = Category::where('is_active', true)
            ->withCount(['services' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('sort_order')
            ->get();

        $topServices = Service::withCount('tickets')
            ->having('tickets_count', '>', 0)
            ->orderByDesc('tickets_count')
            ->take(5)
            ->get(['id', 'name']);

        // =============================================
        // 4. Penilaian (hanya ulasan bintang 4–5 yang ditampilkan)
        // =============================================
        $ratingCount = TicketRating::count();
        $ratingAvg   = $ratingCount > 0 ? round((float) TicketRating::avg('rating'), 1) : null;

        $reviews = TicketRating::with(['user.userType', 'ticket.service'])
            ->where('rating', '>=', 4)
            ->whereNotNull('comment')
            ->where('comment', '!=', '')
            ->latest()
            ->take(6)
            ->get()
            ->map(fn ($r) => [
                'name'    => $this->maskName($r->user->nama ?? 'Pengguna'),
                'role'    => $r->user->userType->label ?? 'Pengguna',
                'service' => $r->ticket->service->name ?? 'Layanan Akademik',
                'rating'  => (int) $r->rating,
                'comment' => $r->comment,
                'date'    => $r->created_at?->translatedFormat('d M Y'),
                'initial' => $this->initials($r->user->nama ?? 'P'),
            ]);

        return view('landing', [
            'totalTickets'   => $totalTickets,
            'completed'      => $completed,
            'inProgress'     => $inProgress,
            'distribution'   => $distribution,
            'slaRate'        => $slaRate,
            'avgResolution'  => $avgResolution,
            'activeServices' => $activeServices,
            'categories'     => $categories,
            'topServices'    => $topServices,
            'ratingCount'    => $ratingCount,
            'ratingAvg'      => $ratingAvg,
            'reviews'        => $reviews,
            'updatedAt'      => now(),
        ]);
    }

    private function maskName(string $name): string
    {
        $parts = preg_split('/\s+/', trim(mb_convert_case($name, MB_CASE_TITLE)));
        $first = $parts[0] ?? 'Pengguna';
        $last  = count($parts) > 1 ? ' ' . mb_substr(end($parts), 0, 1) . '.' : '';

        return $first . $last;
    }

    private function initials(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name));
        $a = mb_substr($parts[0] ?? 'P', 0, 1);
        $b = count($parts) > 1 ? mb_substr(end($parts), 0, 1) : '';

        return mb_strtoupper($a . $b);
    }
}
