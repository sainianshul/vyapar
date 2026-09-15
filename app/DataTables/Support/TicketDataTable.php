<?php

namespace App\DataTables\Support;

use App\Models\SupportTicket;
use Yajra\DataTables\Services\DataTable;

class TicketDataTable extends DataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)

            // ── Reference ID ─────────────────────────────────────────
            ->editColumn('reference_id', function (SupportTicket $ticket) {
                return '<span class="text-nowrap">' . e($ticket->reference_id) . '</span>';
            })

            // ── User Info ────────────────────────────────────────────
            ->filterColumn('user', function($query, $keyword) {
                $query->whereHas('user', function($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%")
                      ->orWhere('email', 'like', "%{$keyword}%")
                      ->orWhere('phone', 'like', "%{$keyword}%");
                });
            })
            ->addColumn('user', function (SupportTicket $ticket) {
                $user = $ticket->user;
                if (!$user) return '<span class="text-muted">Unknown</span>';

                return '<span class="text-body">' . e($user->name) . '</span>';
            })

            // ── Category ─────────────────────────────────────────────
            ->addColumn('category', function (SupportTicket $ticket) {
                return '<span class="text-body">' . e($ticket->category ?? 'General') . '</span>';
            })

            // ── Priority ─────────────────────────────────────────────
            ->addColumn('priority', function (SupportTicket $ticket) {
                $priorityMap = [
                    SupportTicket::PRIORITY_LOW => ['class' => 'text-success', 'icon' => 'ti-arrow-down'],
                    SupportTicket::PRIORITY_MEDIUM => ['class' => 'text-warning', 'icon' => 'ti-minus'],
                    SupportTicket::PRIORITY_HIGH => ['class' => 'text-danger', 'icon' => 'ti-arrow-up'],
                ];
                
                $prio = $priorityMap[$ticket->priority] ?? $priorityMap[SupportTicket::PRIORITY_LOW];
                
                return '<div class="d-flex align-items-center"><i class="ti ' . $prio['icon'] . ' ' . $prio['class'] . ' me-1"></i> <span class="' . $prio['class'] . '">' . $ticket->priority_text . '</span></div>';
            })

            // ── Status ──────────────────────────────────────────────
            ->addColumn('status', function (SupportTicket $ticket) {
                $color = $ticket->status_color;
                if ($color == 'primary') $color = 'blue';
                return '
                    <span class="badge badge-outline text-' . $color . ' border-' . $color . ' fs-9 px-2 py-1">
                        ' . e($ticket->status_text) . '
                    </span>
                ';
            })

            // ── Created At ──────────────────────────────────────────
            ->editColumn('created_at', function (SupportTicket $ticket) {
                return '<div class="text-secondary">' . $ticket->created_at->format('d M Y, h:i A') . '</div>';
            })

            // ── Actions ─────────────────────────────────────────────
            ->addColumn('actions', function (SupportTicket $ticket) {
                $viewUrl = route('admin.support.show', $ticket->id);

                return '
                    <div class="d-flex gap-1 justify-content-end">
                        <a href="' . $viewUrl . '"
                            class="btn btn-icon btn-outline-primary btn-sm"
                            title="View">
                            <i class="ti ti-eye"></i>
                        </a>
                    </div>
                ';
            })

            ->rawColumns([
                'reference_id',
                'user',
                'category',
                'priority',
                'status',
                'created_at',
                'actions',
            ]);
    }

    public function query(SupportTicket $model)
    {
        $query = $model->newQuery()->with(['user'])->select('support_tickets.*');

        // Filter by user_id
        if (request()->filled('user_id')) {
            $query->where('user_id', request('user_id'));
        }

        // Filter by status
        if (request()->filled('status')) {
            $query->where('status', request('status'));
        }

        // Filter by category
        if (request()->filled('category')) {
            $query->where('category', request('category'));
        }

        // Filter by date
        if (request()->filled('date')) {
            $query->whereDate('support_tickets.created_at', request('date'));
        }

        return $query;
    }

    public function filename(): string
    {
        return 'SupportTickets_' . date('Y_m_d_His');
    }
}

