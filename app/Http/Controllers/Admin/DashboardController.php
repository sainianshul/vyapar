<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\CareRequest;
use App\Models\LoginHistory;
use App\Models\NurseProfile;
use App\Models\NurseReview;
use App\Models\RequestBid;
use App\Models\SupportTicket;
use App\Models\User;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard');
    }

    /**
     * Return all dashboard statistics as JSON.
     * Called via AJAX so the page shell loads instantly.
     */
    public function stats()
    {
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();

        // ── Overview Cards ──────────────────────────────────
        $totalPatients = User::where('role', User::ROLE_USER)->count();
        $totalNurses = User::where('role', User::ROLE_NURSE)->count();
        $totalRequests = CareRequest::count();
        $totalBookings = Booking::count();

        // ── Today Stats ─────────────────────────────────────
        $todayRequests = CareRequest::whereDate('created_at', $today)->count();
        $todayBookings = Booking::whereDate('created_at', $today)->count();
        $todayBids = RequestBid::whereDate('created_at', $today)->count();
        $todayReviews = NurseReview::whereDate('created_at', $today)->count();
        $todayLogins = LoginHistory::whereDate('created_at', $today)->count();

        // ── This Month ──────────────────────────────────────
        $monthRequests = CareRequest::where('created_at', '>=', $startOfMonth)->count();
        $monthBookings = Booking::where('created_at', '>=', $startOfMonth)->count();

        // ── Revenue ─────────────────────────────────────────
        $totalRevenue = Booking::where('payment_status', Booking::PAYMENT_PAID)->sum('total_amount');
        $monthRevenue = Booking::where('payment_status', Booking::PAYMENT_PAID)
            ->where('created_at', '>=', $startOfMonth)->sum('total_amount');
        $todayRevenue = Booking::where('payment_status', Booking::PAYMENT_PAID)
            ->whereDate('created_at', $today)->sum('total_amount');

        // ── Booking Status Breakdown ────────────────────────
        $bookingsByStatus = [
            'pending' => Booking::where('status', Booking::STATUS_PENDING_PAYMENT)->count(),
            'confirmed' => Booking::where('status', Booking::STATUS_CONFIRMED)->count(),
            'active' => Booking::where('status', Booking::STATUS_ACTIVE)->count(),
            'completed' => Booking::where('status', Booking::STATUS_COMPLETED)->count(),
            'cancelled' => Booking::where('status', Booking::STATUS_CANCELLED)->count(),
        ];

        // ── Request Status Breakdown ────────────────────────
        $requestsByStatus = [
            'pending' => CareRequest::where('status', CareRequest::STATUS_PENDING)->count(),
            'matching' => CareRequest::where('status', CareRequest::STATUS_MATCHING)->count(),
            'accepted' => CareRequest::where('status', CareRequest::STATUS_ACCEPTED)->count(),
            'completed' => CareRequest::where('status', CareRequest::STATUS_COMPLETED)->count(),
            'cancelled' => CareRequest::where('status', CareRequest::STATUS_CANCELLED)->count(),
            'failed' => CareRequest::whereIn('status', [
                CareRequest::STATUS_FAILED_NO_NURSES,
                CareRequest::STATUS_FAILED_NO_BIDS,
                CareRequest::STATUS_FAILED_UNACCEPTED,
            ])->count(),
        ];

        // ── Nurse Status Breakdown ──────────────────────────
        $nursesByStatus = [
            'pending' => NurseProfile::where('status', NurseProfile::STATUS_PENDING)->count(),
            'under_review' => NurseProfile::where('status', NurseProfile::STATUS_UNDER_REVIEW)->count(),
            'approved' => NurseProfile::where('status', NurseProfile::STATUS_APPROVED)->count(),
            'rejected' => NurseProfile::where('status', NurseProfile::STATUS_REJECTED)->count(),
        ];

        // ── Monthly Bookings Chart (last 6 months) ──────────
        $monthlyBookings = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthlyBookings[] = [
                'month' => $month->format('M'),
                'count' => Booking::whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)->count(),
                'revenue' => (float) Booking::where('payment_status', Booking::PAYMENT_PAID)
                    ->whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)->sum('total_amount'),
            ];
        }

        // ── Monthly Requests Chart (last 6 months) ──────────
        $monthlyRequests = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthlyRequests[] = [
                'month' => $month->format('M'),
                'count' => CareRequest::whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)->count(),
            ];
        }

        // ── Recent Bookings (latest 5) ──────────────────────
        $recentBookings = Booking::with(['user', 'nurse.user'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'reference_id' => $booking->reference_id,
                    'user_name' => $booking->user->name ?? 'Unknown',
                    'nurse_name' => ($booking->nurse && $booking->nurse->user) ? $booking->nurse->user->name : 'Unassigned',
                    'total_amount' => number_format($booking->total_amount, 2),
                    'status' => $booking->status,
                    'status_text' => $booking->status_text,
                    'created_at' => $booking->created_at->diffForHumans(),
                ];
            });

        // ── Recent Requests (latest 5) ──────────────────────
        $recentRequests = CareRequest::with('user')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($request) {
                return [
                    'id' => $request->id,
                    'reference_id' => $request->reference_id,
                    'user_name' => $request->user->name ?? 'Unknown',
                    'status' => $request->status,
                    'status_text' => $request->status_text,
                    'city' => $request->city ?? '—',
                    'created_at' => $request->created_at->diffForHumans(),
                ];
            });

        // ── Support Tickets ─────────────────────────────────
        $openTickets = SupportTicket::where('status', SupportTicket::STATUS_OPEN)->count();

        return response()->json([
            // Overview
            'total_patients' => $totalPatients,
            'total_nurses' => $totalNurses,
            'total_requests' => $totalRequests,
            'total_bookings' => $totalBookings,

            // Today
            'today_requests' => $todayRequests,
            'today_bookings' => $todayBookings,
            'today_bids' => $todayBids,
            'today_reviews' => $todayReviews,
            'today_logins' => $todayLogins,

            // This Month
            'month_requests' => $monthRequests,
            'month_bookings' => $monthBookings,

            // Revenue
            'total_revenue' => number_format($totalRevenue, 2),
            'month_revenue' => number_format($monthRevenue, 2),
            'today_revenue' => number_format($todayRevenue, 2),

            // Breakdowns
            'bookings_by_status' => $bookingsByStatus,
            'requests_by_status' => $requestsByStatus,
            'nurses_by_status' => $nursesByStatus,

            // Charts
            'monthly_bookings' => $monthlyBookings,
            'monthly_requests' => $monthlyRequests,

            // Recent
            'recent_bookings' => $recentBookings,
            'recent_requests' => $recentRequests,

            // Support
            'open_tickets' => $openTickets,
        ]);
    }
}
