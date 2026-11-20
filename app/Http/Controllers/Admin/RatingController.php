<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Service;
use App\Models\Status;
use App\Models\Ticket;
use App\Models\TicketRating;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    /**
     * Display rating list and CSAT overview for admin.
     */
    public function index(Request $request)
    {
        $query = TicketRating::with(['ticket.service.category', 'ticket.user', 'user'])
            ->latest('created_at');

        // Filter by rating score (1-5)
        if ($request->filled('score')) {
            $query->where('rating', $request->score);
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->whereHas('ticket', function ($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }

        $ratings = $query->paginate(15)->withQueryString();

        // Metrics
        $totalRatings = TicketRating::count();
        $averageRating = $totalRatings > 0 ? round(TicketRating::avg('rating'), 2) : 0;
        
        $satisfiedCount = TicketRating::whereIn('rating', [4, 5])->count();
        $satisfactionPercentage = $totalRatings > 0 ? round(($satisfiedCount / $totalRatings) * 100) : 0;

        $starCounts = [
            5 => TicketRating::where('rating', 5)->count(),
            4 => TicketRating::where('rating', 4)->count(),
            3 => TicketRating::where('rating', 3)->count(),
            2 => TicketRating::where('rating', 2)->count(),
            1 => TicketRating::where('rating', 1)->count(),
        ];

        $categories = Category::active()->orderBy('name')->get();

        // Tiket SELESAI yang belum diberi rating
        $unratedTickets = Ticket::byStatus(Status::SELESAI)
            ->doesntHave('rating')
            ->with(['user.userType', 'service.category', 'emailNotifications'])
            ->latest('resolved_at')
            ->latest('updated_at')
            ->get();

        return view('admin.ratings.index', compact(
            'ratings',
            'totalRatings',
            'averageRating',
            'satisfactionPercentage',
            'starCounts',
            'categories',
            'unratedTickets'
        ));
    }

    /**
     * Send rating reminder emails to all completed tickets that have not been rated yet.
     */
    public function sendAllReminders(NotificationService $notificationService)
    {
        $sentCount = $notificationService->sendDueRatingReminders(7, true);

        if ($sentCount > 0) {
            return redirect()->route('admin.ratings.index')
                ->with('success', "Berhasil mengirimkan {$sentCount} email pengingat penilaian (rating) ke pemohon.");
        }

        return redirect()->route('admin.ratings.index')
            ->with('info', 'Tidak ada tiket selesai tanpa rating yang perlu dikirimkan pengingat saat ini.');
    }
}

