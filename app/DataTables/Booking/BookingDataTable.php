<?php

namespace App\DataTables\Booking;

use App\Models\Booking;
use Yajra\DataTables\Services\DataTable;

class BookingDataTable extends DataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)

            // ── Reference ID ─────────────────────────────────────────
            ->editColumn('reference_id', function (Booking $booking) {
                return '<span class="text-nowrap">' . e($booking->reference_id) . '</span>';
            })

            // ── User Info ────────────────────────────────────────────
            ->filterColumn('user', function ($query, $keyword) {
                $query->whereHas('user', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%")
                        ->orWhere('email', 'like', "%{$keyword}%")
                        ->orWhere('phone', 'like', "%{$keyword}%");
                });
            })
            ->addColumn('user', function (Booking $booking) {
                $user = $booking->user;
                if (!$user)
                    return '<span class="text-muted">Unknown</span>';

                return '<span class="text-secondary">' . e($user->name) . '</span>';
            })

            // ── Nurse Info ───────────────────────────────────────────
            ->addColumn('nurse', function (Booking $booking) {
                $nurse = $booking->nurse;
                if (!$nurse || !$nurse->user)
                    return '<span class="text-muted">Unassigned</span>';

                return '<span class="text-secondary">' . e($nurse->user->name) . '</span>';
            })

            // ── Amount ──────────────────────────────────────────────
            ->addColumn('amount', function (Booking $booking) {
                return '<span class="text-secondary">₹' . number_format($booking->total_amount, 2) . '</span>';
            })

            // ── Status ──────────────────────────────────────────────
            ->addColumn('status', function (Booking $booking) {
                $color = $booking->status_color;
                return '<span class="badge badge-outline text-' . $color . ' border-' . $color . ' fs-9 px-2 py-1">' . e($booking->status_text) . '</span>';
            })

            // ── Payment Status ──────────────────────────────────────
            ->addColumn('payment_status', function (Booking $booking) {
                $color = $booking->payment_status_color;
                return '<span class="badge badge-outline text-' . $color . ' border-' . $color . ' fs-9 px-2 py-1">' . e($booking->payment_status_text) . '</span>';
            })

            // ── Sessions ────────────────────────────────────────────
            ->addColumn('sessions', function (Booking $booking) {
                return '<span class="text-secondary">' . $booking->completed_sessions . ' / ' . $booking->total_sessions . '</span>';
            })

            // ── Created At ──────────────────────────────────────────
            ->editColumn('created_at', function (Booking $booking) {
                return '<span class="text-secondary">' . ($booking->created_at ? $booking->created_at->format('d M Y') : '—') . '</span>';
            })

            ->orderColumn('created_at', function ($query, $order) {
                $query->orderBy('bookings.id', $order);
            })

            // ── Actions ─────────────────────────────────────────────
            ->addColumn('actions', function (Booking $booking) {
                $viewUrl = route('admin.bookings.show', $booking->id);

                return '
                    <div class="d-flex gap-1 justify-content-end">
                        <a href="' . $viewUrl . '"
                            class="btn btn-sm btn-icon btn-light-primary border border-primary w-30px h-30px"
                            title="View">
                            <i class="ti ti-eye fs-5"></i>
                        </a>
                    </div>
                ';
            })

            ->rawColumns([
                'reference_id',
                'user',
                'nurse',
                'amount',
                'status',
                'payment_status',
                'sessions',
                'created_at',
                'actions',
            ]);
    }

    public function query(Booking $model)
    {
        $query = $model->newQuery()->with(['user', 'nurse.user'])->select([
            'bookings.id',
            'bookings.reference_id',
            'bookings.care_request_id',
            'bookings.user_id',
            'bookings.nurse_id',
            'bookings.total_amount',
            'bookings.status',
            'bookings.payment_status',
            'bookings.completed_sessions',
            'bookings.total_sessions',
            'bookings.created_at',
        ]);

        // Filter by user_id
        if (request()->filled('user_id')) {
            $query->where('user_id', request('user_id'));
        }

        // Filter by status
        if (request()->filled('status')) {
            $query->where('status', request('status'));
        }

        // Filter by payment status
        if (request()->filled('payment_status')) {
            $query->where('payment_status', request('payment_status'));
        }

        // Filter by date
        if (request()->filled('date')) {
            $date = request('date');
            $query->whereBetween('bookings.created_at', [
                $date . ' 00:00:00',
                $date . ' 23:59:59'
            ]);
        }

        return $query;
    }
}

