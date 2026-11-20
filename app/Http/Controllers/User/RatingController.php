<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Status;
use App\Models\Ticket;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    /**
     * Display list of completed tickets for the authenticated user
     * with their rating status.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Tickets that are SELESAI
        $tickets = Ticket::byUser($user->id)
            ->byStatus(Status::SELESAI)
            ->with(['service.category', 'rating'])
            ->latest('resolved_at')
            ->latest('updated_at')
            ->paginate(10);

        // Count of completed tickets waiting for rating
        $unratedCount = Ticket::byUser($user->id)
            ->byStatus(Status::SELESAI)
            ->doesntHave('rating')
            ->count();

        return view('user.ratings.index', compact('tickets', 'unratedCount'));
    }
}

