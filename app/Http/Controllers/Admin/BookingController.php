<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\Booking\BookingDataTable;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\NurseReview;
use Carbon\Carbon;

class BookingController extends Controller
{
    /**
     * Display a listing of all bookings.
     */
    public function index(BookingDataTable $dataTable)
    {
        return $dataTable->render('admin.booking.index');
    }

    /**
     * Get data for datatable (AJAX)
     */
    public function data(BookingDataTable $dataTable)
    {
        return $dataTable->ajax();
    }

    public function active(BookingDataTable $dataTable)
    {
        return $dataTable->render('admin.booking.index', [
            'title' => 'Active Bookings',
            'dataUrl' => route('admin.bookings.active-data'),
            'hideStatusFilter' => true
        ]);
    }

    public function activeData(BookingDataTable $dataTable)
    {
        request()->merge(['status' => Booking::STATUS_ACTIVE]);
        return $dataTable->ajax();
    }

    public function cancelled(BookingDataTable $dataTable)
    {
        return $dataTable->render('admin.booking.index', [
            'title' => 'Cancelled Bookings',
            'dataUrl' => route('admin.bookings.cancelled-data'),
            'hideStatusFilter' => true
        ]);
    }

    public function cancelledData(BookingDataTable $dataTable)
    {
        request()->merge(['status' => Booking::STATUS_CANCELLED]);
        return $dataTable->ajax();
    }

    /**
     * Display the specified booking with all details.
     */
    public function show($id)
    {
        $booking = Booking::with([
            'user',
            'nurse.user',
            'careRequest.careType',
            'bid',
            'parentBooking',
            'extensions',
        ])->findOrFail($id);

        return view('admin.booking.show', compact('booking'));
    }

    /**
     * Get sessions data for a booking (AJAX).
     */
    public function sessionsData($id)
    {
        $booking = Booking::findOrFail($id);
        $sessions = $booking->sessions()->orderBy('session_number');

        return datatables()->of($sessions)
            ->addColumn('session_date', function ($session) {
                return $session->session_date ? $session->session_date->format('d M Y') : '—';
            })
            ->addColumn('start_time', function ($session) {
                return $session->start_time ? Carbon::parse($session->start_time)->format('h:i A') : '—';
            })
            ->addColumn('end_time', function ($session) {
                return $session->end_time ? Carbon::parse($session->end_time)->format('h:i A') : '—';
            })
            ->addColumn('started_at', function ($session) {
                return $session->started_at ? $session->started_at->format('d M Y h:i A') : '—';
            })
            ->addColumn('ended_at', function ($session) {
                return $session->ended_at ? $session->ended_at->format('d M Y h:i A') : '—';
            })
            ->editColumn('status', function ($session) {
                $color = $session->status_color;
                return '<span class="badge badge-light-' . $color . ' border border-' . $color . ' fw-bold px-3 py-2">' . e($session->status_text) . '</span>';
            })
            ->addColumn('otp_verified', function ($session) {
                return $session->otp_verified_at
                    ? '<span class="badge bg-green-lt border border-success fw-bold px-2 py-1"><i class="ti ti-eye fs-6 me-1"></i>Verified</span>'
                    : '<span class="badge bg-secondary-lt border border-secondary fw-bold px-2 py-1">Pending</span>';
            })
            ->addColumn('nurse_notes', function ($session) {
                return e($session->nurse_notes ?? '—');
            })
            ->rawColumns(['status', 'otp_verified'])
            ->make(true);
    }

    /**
     * Get payment logs for a booking (AJAX).
     */
    public function paymentLogsData($id)
    {
        $booking = Booking::findOrFail($id);
        $logs = $booking->paymentLogs()->orderByDesc('created_at');

        $eventColors = [
            1 => 'primary',   // order created
            2 => 'success',   // payment success
            3 => 'danger',    // payment failed
            4 => 'warning',   // refund initiated
            5 => 'info',      // refund completed
            6 => 'primary',   // payout initiated
            7 => 'success',   // payout completed
            8 => 'danger',    // payout failed
            9 => 'secondary', // webhook
        ];

        $eventIcons = [
            1 => 'ti ti-eye',
            2 => 'ti ti-eye',
            3 => 'ti ti-eye',
            4 => 'ti ti-eye',
            5 => 'ti ti-eye',
            6 => 'ti ti-eye',
            7 => 'ti ti-eye',
            8 => 'ti ti-eye',
            9 => 'ti ti-eye',
        ];

        return datatables()->of($logs)
            ->addColumn('event', function ($log) use ($eventColors, $eventIcons) {
                $color = $eventColors[$log->event_type] ?? 'dark';
                $icon = $eventIcons[$log->event_type] ?? 'ti ti-eye';
                return '<div class="d-flex align-items-center gap-2">
                    <i class="' . $icon . ' fs-4 text-' . $color . '"></i>
                    <span class="badge badge-light-' . $color . ' border border-' . $color . ' fw-bold px-3 py-2">' . e($log->event_type_text) . '</span>
                </div>';
            })
            ->editColumn('amount', function ($log) {
                return '<span class="fw-bold text-gray-900">₹' . number_format($log->amount, 2) . '</span>';
            })
            ->addColumn('gateway', function ($log) {
                return '<span class="text-gray-700 fw-semibold">' . e($log->gateway_name ?? '—') . '</span>';
            })
            ->addColumn('gateway_order_id', function ($log) {
                return '<span class="text-gray-600 fs-8 font-monospace">' . e($log->gateway_order_id ?? '—') . '</span>';
            })
            ->addColumn('gateway_payment_id', function ($log) {
                return '<span class="text-gray-600 fs-8 font-monospace">' . e($log->gateway_payment_id ?? '—') . '</span>';
            })
            ->editColumn('status', function ($log) {
                return '<span class="text-gray-700 fw-semibold">' . e($log->gateway_status ?? '—') . '</span>';
            })
            ->editColumn('created_at', function ($log) {
                return $log->created_at ? $log->created_at->format('d M Y h:i A') : '—';
            })
            ->rawColumns(['event', 'amount', 'gateway', 'gateway_order_id', 'gateway_payment_id', 'status'])
            ->make(true);
    }

    // ── Reviews Data (AJAX) ──────────────────────────────────────────
    public function reviewsData($id)
    {
        $booking = Booking::findOrFail($id);

        $reviews = NurseReview::with(['user'])
            ->where('booking_id', $booking->id)
            ->latest()
            ->select(['id', 'user_id', 'rating', 'review', 'created_at']);

        return datatables()->of($reviews)
            ->editColumn('user', function ($review) {
                $img = $review->user->avatar_html;
                return '
                <div class="d-flex align-items-center gap-3">
                    <div class="symbol symbol-30px symbol-circle">' . $img . '</div>
                    <div class="d-flex flex-column">
                        <a href="' . route('admin.patients.show', $review->user->id) . '" class="text-gray-900 text-hover-primary fw-bold fs-7">' . $review->user->name . '</a>
                        <span class="text-gray-500 fs-8">' . $review->user->email . '</span>
                    </div>
                </div>';
            })
            ->editColumn('rating', function ($review) {
                $stars = '';
                for ($i = 1; $i <= 5; $i++) {
                    $stars .= '<i class="ki-solid ki-star fs-6 ' . ($i <= $review->rating ? 'text-warning' : 'text-gray-300') . '"></i>';
                }
                return '<div class="d-flex align-items-center gap-1">' . $stars . '<span class="ms-1 fw-bold text-gray-800 fs-7">' . $review->rating . '.0</span></div>';
            })
            ->editColumn('created_at', function ($review) {
                return '<span class="text-gray-600 fs-7">' . $review->created_at->format('d M Y') . '</span><br><span class="fs-8 text-gray-500">' . $review->created_at->format('h:i A') . '</span>';
            })
            ->rawColumns(['user', 'rating', 'created_at'])
            ->make(true);
    }

    /**
     * Get bids data for a booking's care request (AJAX).
     */
    public function bidsData($id)
    {
        $booking = Booking::with('careRequest')->findOrFail($id);

        if (!$booking->careRequest) {
            return datatables()->of(collect([]))->make(true);
        }

        $bids = $booking->careRequest->bids()->with('nurse.user');

        return datatables()->of($bids)
            ->addColumn('nurse', function ($bid) {
                $nurseInfo = '<span class="text-gray-500 fs-7">Unknown</span>';
                if ($bid->nurse && $bid->nurse->user) {
                    $initial = mb_strtoupper(mb_substr($bid->nurse->user->name ?? 'Un', 0, 2));
                    $url = route('admin.nurses.show', $bid->nurse->user->id);
                    $distanceHtml = '';
                    if ($bid->distance_km) {
                        $distanceHtml = '<div class="mt-1 d-flex align-items-center text-gray-700 fs-8"><i class="ti ti-eye fs-8 me-1 text-gray-500"></i>' . $bid->distance_km . ' km away</div>';
                    }

                    $nurseInfo = '
                        <div class="d-flex align-items-start gap-3">
                            <div class="symbol symbol-30px symbol-circle">
                                <span class="symbol-label bg-light-info text-info fw-bold">' . e($initial) . '</span>
                            </div>
                            <div class="d-flex flex-column">
                                <a href="' . $url . '" class="text-gray-900 text-hover-primary fw-bold fs-7">' . e($bid->nurse->user->name) . '</a>
                                <span class="text-gray-600 fs-8">ID: ' . $bid->nurse_id . '</span>
                                ' . $distanceHtml . '
                            </div>
                        </div>';
                }
                return $nurseInfo;
            })
            ->addColumn('nurse_amount', function ($bid) {
                return '<span class="text-gray-900 fw-bold fs-7">₹' . number_format($bid->nurse_amount, 2) . '</span>';
            })
            ->addColumn('commission', function ($bid) {
                return '<span class="text-success fw-bold fs-7">₹' . number_format($bid->commission_amount, 2) . '</span>';
            })
            ->addColumn('total', function ($bid) {
                return '<span class="text-primary fw-bold fs-7">₹' . number_format($bid->total_amount, 2) . '</span>';
            })
            ->editColumn('status', function ($bid) use ($booking) {
                $color = $bid->status_color;
                $isSelected = ($booking->bid_id == $bid->id);
                $statusBadge = '<span class="badge badge-light-' . $color . ' fs-8 px-2 py-1">' . e($bid->status_text) . '</span>';
                if ($isSelected) {
                    $statusBadge .= ' <span class="badge badge-success fs-9 px-2 py-1 ms-1"><i class="ti ti-eye text-white fs-8"></i> Selected</span>';
                }
                return $statusBadge;
            })
            ->addColumn('notes', function ($bid) {
                return '<span class="text-gray-600 fs-8">' . e($bid->notes ?? '—') . '</span>';
            })
            ->setRowClass(function ($bid) use ($booking) {
                return ($booking->bid_id == $bid->id) ? 'bg-light-success' : '';
            })
            ->rawColumns(['nurse', 'nurse_amount', 'commission', 'total', 'status', 'notes'])
            ->make(true);
    }
}

