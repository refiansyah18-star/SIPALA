<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AlumniRegistration;
use App\Models\Ticket;
use App\Models\Status;
use App\Models\UserType;

class DashboardController extends Controller
{
    /**
     * Route users to their appropriate dashboard based on role.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        return match ($user->userType->name) {
            UserType::SUPER_ADMIN => $this->adminDashboard($user),
            UserType::ADMIN => $this->adminDashboard($user),
            UserType::STAFF => $this->staffDashboard($user),
            default => $this->userDashboard($user),
        };
    }

    /**
     * User Dashboard (Mahasiswa, Alumni, Dosen/Pegawai).
     * PRD Section 34.
     */
    private function userDashboard($user)
    {
        $tickets = Ticket::byUser($user->id)
            ->with(['service', 'status'])
            ->latest()
            ->get();

        $stats = [
            'total' => $tickets->count(),
            'diajukan' => $tickets->filter(fn($t) => $t->status->name === Status::DIAJUKAN)->count(),
            'diproses' => $tickets->filter(fn($t) => in_array($t->status->name, [Status::DIVERIFIKASI, Status::DIPROSES]))->count(),
            'selesai' => $tickets->filter(fn($t) => $t->status->name === Status::SELESAI)->count(),
            'ditolak' => $tickets->filter(fn($t) => $t->status->name === Status::DITOLAK)->count(),
        ];

        return view('dashboard.user', compact('user', 'tickets', 'stats'));
    }

    /**
     * Admin Dashboard.
     * PRD Section 35.
     */
    private function adminDashboard($user)
    {
        $allTickets = Ticket::with(['service', 'status', 'user.userType', 'latestAssignment.staff', 'assignments.staff'])
            ->latest()
            ->get();

        $stats = [
            'total' => $allTickets->count(),
            'baru' => $allTickets->filter(fn($t) => $t->status->name === Status::DIAJUKAN)->count(),
            'diverifikasi' => $allTickets->filter(fn($t) => $t->status->name === Status::DIVERIFIKASI)->count(),
            'diproses' => $allTickets->filter(fn($t) => $t->status->name === Status::DIPROSES)->count(),
            'selesai' => $allTickets->filter(fn($t) => $t->status->name === Status::SELESAI)->count(),
            'ditolak' => $allTickets->filter(fn($t) => $t->status->name === Status::DITOLAK)->count(),
            'overdue' => $allTickets->filter(fn($t) => $t->sla_status === 'OVERDUE')->count(),
            'near_deadline' => $allTickets->filter(fn($t) => $t->sla_status === 'NEAR_DEADLINE')->count(),
            'unassigned' => $allTickets->filter(fn($t) => !$t->latestAssignment && !$t->status->is_terminal && $t->status->name !== Status::DIAJUKAN)->count(),
            'pending_alumni' => AlumniRegistration::pending()->count(),
        ];

        $recentTickets = $allTickets->take(10);

        return view('dashboard.admin', compact('user', 'stats', 'recentTickets'));
    }

    /**
     * Staff Dashboard.
     * PRD Section 36.
     */
    private function staffDashboard($user)
    {
        $assignedTickets = Ticket::assignedTo($user->id)
            ->with(['service', 'status', 'user'])
            ->latest()
            ->get();

        $stats = [
            'total' => $assignedTickets->count(),
            'belum_diproses' => $assignedTickets->filter(fn($t) => in_array($t->status->name, [Status::DIVERIFIKASI, Status::DIAJUKAN]))->count(),
            'diproses' => $assignedTickets->filter(fn($t) => $t->status->name === Status::DIPROSES)->count(),
            'near_deadline' => $assignedTickets->filter(fn($t) => $t->sla_status === 'NEAR_DEADLINE')->count(),
            'selesai' => $assignedTickets->filter(fn($t) => $t->status->name === Status::SELESAI)->count(),
        ];

        return view('dashboard.staff', compact('user', 'assignedTickets', 'stats'));
    }
}
