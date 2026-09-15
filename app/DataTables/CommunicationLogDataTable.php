<?php

namespace App\DataTables;

use App\Models\CommunicationLog;
use App\Models\User;
use Illuminate\Support\Str;
use Yajra\DataTables\Services\DataTable;

class CommunicationLogDataTable extends DataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)

            // -- User -----------------------------------------------
            ->addColumn('user', function (CommunicationLog $log) {
                $notifiable = $log->notifiable;
                $user = ($notifiable instanceof User) ? $notifiable : null;

                if (!$user) {
                    return '
                        <div>
                            <span class="text-secondary fw-medium d-block">System / Deleted User</span>
                            <span class="text-muted">' . e($log->destination ?? '—') . '</span>
                        </div>
                    ';
                }

                $name = trim((string) ($user->name));
                if ($name === '') {
                    $name = 'Unknown User';
                }

                $contact = !empty($user->phone) ? $user->phone : (!empty($user->email) ? $user->email : $log->destination);
                $role = (int) ($user->role);

                $roleBadge = match ($role) {
                    User::ROLE_ADMIN => '<span class="badge badge-outline text-danger border-danger fs-9 px-2 py-1">Admin</span>',
                    User::ROLE_USER => '<span class="badge badge-outline text-warning border-warning fs-9 px-2 py-1">Patient</span>',
                    User::ROLE_NURSE => '<span class="badge badge-outline text-info border-info fs-9 px-2 py-1">Nurse</span>',
                    default => '<span class="badge badge-outline text-secondary border-secondary fs-9 px-2 py-1">Guest</span>'
                };

                return '
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="text-secondary fw-medium">' . e($name) . '</span>
                            ' . $roleBadge . '
                        </div>
                        <div class="text-muted">' . e($contact) . '</div>
                    </div>
                ';
            })

            // -- Notification (Channel) ------------------------------
            ->addColumn('notification', function (CommunicationLog $log) {
                $channel = strtolower($log->channel ?? 'unknown');
                $color = match ($channel) {
                    'sms', 'twilio' => 'info',
                    'mail', 'email' => 'warning',
                    'fcm', 'push' => 'success',
                    'whatsapp' => 'teal',
                    default => 'primary'
                };

                return '<span class="badge badge-outline text-' . $color . ' border-' . $color . ' fs-9 px-2 py-1">' . strtoupper($channel) . '</span>';
            })

            // -- Type -----------------------------------------------
            ->addColumn('type', function (CommunicationLog $log) {
                $type = $log->type ?? 'Notification';
                if (str_contains($type, '\\')) {
                    $parts = explode('\\', $type);
                    $type = end($parts);
                }
                return '<span class="text-secondary fw-medium">' . e($type) . '</span>';
            })

            // -- Message Content ------------------------------------
            ->addColumn('content', function (CommunicationLog $log) {
                $content = $log->content;
                if (!$content) return '<span class="text-secondary">—</span>';

                $truncated = Str::limit($content, 80);
                return '<span class="text-secondary text-truncate d-inline-block" style="max-width: 280px;" title="' . e($content) . '">' . e($truncated) . '</span>';
            })

            // -- Status ---------------------------------------------
            ->addColumn('status', function (CommunicationLog $log) {
                $st = strtolower($log->status ?? '');
                if ($st === 'sent' || $st === 'success' || $st === 'delivered') {
                    return '<span class="badge badge-outline text-success border-success fs-9 px-2 py-1">Sent</span>';
                } else {
                    $title = $log->error_message ? ' title="' . e($log->error_message) . '"' : '';
                    return '<span class="badge badge-outline text-danger border-danger fs-9 px-2 py-1"' . $title . '>Failed</span>';
                }
            })

            // -- Created At -----------------------------------------
            ->editColumn('created_at', function (CommunicationLog $log) {
                if (!$log->created_at) return '<span class="text-secondary">—</span>';
                return '
                    <div>
                        <span class="text-secondary d-block">' . $log->created_at->format('d M Y') . '</span>
                        <span class="text-muted">' . $log->created_at->format('h:i A') . '</span>
                    </div>
                ';
            })

            ->filter(function ($query) {
                if (request()->has('search') && $keyword = request('search')['value']) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('destination', 'like', "%{$keyword}%")
                          ->orWhere('content', 'like', "%{$keyword}%")
                          ->orWhere('type', 'like', "%{$keyword}%")
                          ->orWhere('channel', 'like', "%{$keyword}%")
                          ->orWhereHasMorph('notifiable', [User::class], function ($uq) use ($keyword) {
                              $uq->where('name', 'like', "%{$keyword}%")
                                 ->orWhere('email', 'like', "%{$keyword}%")
                                 ->orWhere('phone', 'like', "%{$keyword}%");
                          });
                    });
                }
            }, true)

            ->rawColumns([
                'user',
                'notification',
                'type',
                'content',
                'status',
                'created_at',
            ]);
    }

    public function query(CommunicationLog $model)
    {
        $query = $model->newQuery()
            ->with(['notifiable'])
            ->select('communication_logs.*')
            ->orderBy('created_at', 'desc');

        if (request()->filled('channel')) {
            $channel = request('channel');
            if ($channel === 'mail') {
                $query->whereIn('channel', ['mail', 'email']);
            } elseif ($channel === 'sms') {
                $query->whereIn('channel', ['sms', 'twilio']);
            } else {
                $query->where('channel', $channel);
            }
        }

        if (request()->filled('status')) {
            $status = request('status');
            if ($status === 'sent') {
                $query->whereIn('status', ['sent', 'success', 'delivered']);
            } elseif ($status === 'failed') {
                $query->whereNotIn('status', ['sent', 'success', 'delivered']);
            } else {
                $query->where('status', $status);
            }
        }

        if (request()->filled('date')) {
            $date = request('date');
            $query->whereBetween('communication_logs.created_at', [
                $date . ' 00:00:00',
                $date . ' 23:59:59',
            ]);
        }

        return $query;
    }

    public function filename(): string
    {
        return 'Communication_Logs_' . date('Y_m_d_His');
    }
}
