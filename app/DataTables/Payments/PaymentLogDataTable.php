<?php

namespace App\DataTables\Payments;

use App\Models\PaymentLog;
use Yajra\DataTables\Services\DataTable;

class PaymentLogDataTable extends DataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            
            // ── ID ────────────────────────────────────────────────────────
            ->editColumn('id', function (PaymentLog $log) {
                return '<span class="fw-bold text-gray-800">#' . $log->id . '</span>';
            })
            
            // ── Booking / Target ──────────────────────────────────────────
            ->addColumn('target', function (PaymentLog $log) {
                if ($log->loggable_type === \App\Models\Booking::class) {
                    $url = route('admin.bookings.show', $log->loggable_id);
                    return '
                        <div class="d-flex flex-column">
                            <span class="text-gray-800 fw-semibold fs-7">Booking</span>
                            <a href="' . $url . '" class="text-primary fw-semibold fs-8 text-hover-primary">#' . $log->loggable_id . '</a>
                        </div>
                    ';
                }
                return '
                    <div class="d-flex flex-column">
                        <span class="text-gray-800 fw-semibold fs-7">' . class_basename($log->loggable_type) . '</span>
                        <span class="text-muted fw-semibold fs-8">#' . $log->loggable_id . '</span>
                    </div>
                ';
            })
            
            // ── Gateway Info ──────────────────────────────────────────────
            ->addColumn('gateway_info', function (PaymentLog $log) {
                return '
                    <div class="d-flex flex-column">
                        <span class="text-gray-800 fw-semibold fs-7">' . e(ucfirst($log->gateway_name)) . '</span>
                        <span class="text-muted fs-8 font-monospace" title="Payment ID">' . e($log->gateway_payment_id ?? $log->gateway_order_id ?? '—') . '</span>
                    </div>
                ';
            })
            
            // ── Event / Status ────────────────────────────────────────────
            ->editColumn('event_type', function (PaymentLog $log) {
                $colors = [
                    PaymentLog::EVENT_ORDER_CREATED => 'primary',
                    PaymentLog::EVENT_PAYMENT_SUCCESS => 'success',
                    PaymentLog::EVENT_PAYMENT_FAILED => 'danger',
                    PaymentLog::EVENT_REFUND_INITIATED => 'warning',
                    PaymentLog::EVENT_REFUND_COMPLETED => 'info',
                    PaymentLog::EVENT_PAYOUT_INITIATED => 'primary',
                    PaymentLog::EVENT_PAYOUT_COMPLETED => 'success',
                    PaymentLog::EVENT_PAYOUT_FAILED => 'danger',
                    PaymentLog::EVENT_WEBHOOK_RECEIVED => 'secondary',
                ];
                
                $color = $colors[$log->event_type] ?? 'dark';
                
                return '<span class="badge badge-light-' . $color . ' border border-' . $color . ' fw-bold px-3 py-2">' . e($log->event_type_text) . '</span>';
            })
            
            // ── Amount ────────────────────────────────────────────────────
            ->addColumn('amount', function (PaymentLog $log) {
                return '<div class="fw-bold text-gray-900 fs-6">' . e($log->currency) . ' ' . number_format($log->amount, 2) . '</div>';
            })
            
            // ── Gateway Status ────────────────────────────────────────────
            ->editColumn('gateway_status', function (PaymentLog $log) {
                return '<span class="text-gray-800 fw-semibold fs-7">' . e($log->gateway_status ?? '—') . '</span>';
            })
            
            // ── Created At ────────────────────────────────────────────────
            ->editColumn('created_at', function (PaymentLog $log) {
                return '
                    <div class="fw-semibold text-gray-800">' . $log->created_at->format('d M Y') . '</div>
                    <div class="text-muted fs-7">' . $log->created_at->format('h:i A') . '</div>
                ';
            })
            
            ->rawColumns([
                'id',
                'target',
                'gateway_info',
                'event_type',
                'amount',
                'gateway_status',
                'created_at',
            ]);
    }

    public function query(PaymentLog $model)
    {
        $query = $model->newQuery()
            ->select('payment_logs.*');

        // Optional filter to show only refunds if used for refund table
        if (request('only_refunds') == '1') {
            $query->whereIn('event_type', [
                PaymentLog::EVENT_REFUND_INITIATED,
                PaymentLog::EVENT_REFUND_COMPLETED
            ]);
        } else {
            if (request()->filled('event_type')) {
                $query->where('event_type', request('event_type'));
            }
        }

        if (request()->filled('date')) {
            $query->whereDate('created_at', request('date'));
        }

        return $query;
    }
}
