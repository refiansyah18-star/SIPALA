<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Status;
use App\Models\Ticket;
use App\Models\TicketRating;
use Illuminate\Http\Request;

class TicketRatingController extends Controller
{
    public function store(Request $request, Ticket $ticket)
    {
        // Only ticket owner can rate
        if ($ticket->user_id !== auth()->id()) {
            abort(403, 'Akses ditolak.');
        }

        // Only SELESAI tickets can be rated
        if ($ticket->status->name !== Status::SELESAI) {
            return back()->with('error', 'Penilaian hanya dapat diberikan untuk tiket yang sudah selesai.');
        }

        $request->validate([
            'rating'  => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
        ]);

        TicketRating::updateOrCreate(
            ['ticket_id' => $ticket->id],
            [
                'user_id' => auth()->id(),
                'rating'  => $request->rating,
                'comment' => $request->comment,
            ]
        );

        return redirect()->route('user.tickets.show', $ticket)
            ->with('success', 'Terima kasih! Penilaian Anda berhasil disimpan.');
    }
}

