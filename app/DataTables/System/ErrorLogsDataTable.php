<?php

namespace App\DataTables\System;

use App\Models\ApplicationError;
use Illuminate\Support\Str;
use Yajra\DataTables\Services\DataTable;

class ErrorLogsDataTable extends DataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)

            // -- Error ID ----------------------------------------------
            ->addColumn('error_id', function (ApplicationError $error) {
                $viewUrl = route('admin.system.errors.show', $error->id);
                return '
                    <div>
                        <a href="' . $viewUrl . '" class="text-primary fw-bold text-decoration-none d-block">
                            #' . e($error->error_id) . '
                        </a>
                        <span class="text-secondary text-truncate d-inline-block" style="max-width: 280px;" title="' . e($error->message) . '">
                            ' . e(Str::limit($error->message, 80)) . '
                        </span>
                    </div>
                ';
            })

            // -- Severity --------------------------------------------
            ->addColumn('severity', function (ApplicationError $error) {
                $map = [
                    ApplicationError::SEVERITY_LOW => ['Low', 'info'],
                    ApplicationError::SEVERITY_MEDIUM => ['Medium', 'warning'],
                    ApplicationError::SEVERITY_HIGH => ['High', 'danger'],
                    ApplicationError::SEVERITY_CRITICAL => ['Critical', 'dark'],
                ];
                $item = $map[$error->severity] ?? ['Unknown', 'secondary'];
                return '<span class="badge badge-outline text-' . $item[1] . ' border-' . $item[1] . ' fs-9 px-2 py-1">' . e($item[0]) . '</span>';
            })

            // -- Status ----------------------------------------------
            ->addColumn('status', function (ApplicationError $error) {
                $map = [
                    ApplicationError::STATUS_PENDING => ['Pending', 'danger'],
                    ApplicationError::STATUS_OPENED => ['Opened', 'warning'],
                    ApplicationError::STATUS_RESOLVED => ['Resolved', 'success'],
                ];
                $item = $map[$error->status] ?? ['Unknown', 'secondary'];
                return '<span class="badge badge-outline text-' . $item[1] . ' border-' . $item[1] . ' fs-9 px-2 py-1">' . e($item[0]) . '</span>';
            })

            // -- Method ---------------------------------------------
            ->addColumn('method', function (ApplicationError $error) {
                $method = strtoupper($error->method ?? 'GET');
                $color = match($method) {
                    'GET' => 'primary',
                    'POST' => 'success',
                    'PUT', 'PATCH' => 'warning',
                    'DELETE' => 'danger',
                    default => 'secondary'
                };
                return '<span class="badge badge-outline text-' . $color . ' border-' . $color . ' fs-9 px-2 py-1">' . e($method) . '</span>';
            })

            // -- URL ---------------------------------------------
            ->addColumn('url', function (ApplicationError $error) {
                $url = $error->url ?? '—';
                return '<span class="text-secondary text-truncate d-inline-block" style="max-width: 250px;" title="' . e($url) . '">' . e($url) . '</span>';
            })

            // -- Created ---------------------------------------------
            ->editColumn('created_at', function (ApplicationError $error) {
                if (!$error->created_at) return '<span class="text-secondary">—</span>';
                return '
                    <div>
                        <span class="text-secondary d-block">' . $error->created_at->format('d M Y') . '</span>
                        <span class="text-muted">' . $error->created_at->format('h:i A') . '</span>
                    </div>
                ';
            })

            ->filter(function ($query) {
                if (request()->has('search') && $keyword = request('search')['value']) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('error_id', 'like', "%{$keyword}%")
                          ->orWhere('message', 'like', "%{$keyword}%")
                          ->orWhere('url', 'like', "%{$keyword}%")
                          ->orWhere('method', 'like', "%{$keyword}%");
                    });
                }
            }, true)

            ->rawColumns([
                'error_id',
                'severity',
                'status',
                'method',
                'url',
                'created_at',
            ]);
    }

    public function query(ApplicationError $model)
    {
        $query = $model->newQuery()
            ->select('application_errors.*')
            ->orderBy('created_at', 'desc');

        if (request()->filled('status')) {
            $query->where('status', request('status'));
        }

        if (request()->filled('severity')) {
            $query->where('severity', request('severity'));
        }

        if (request()->filled('date')) {
            $date = request('date');
            $query->whereBetween('application_errors.created_at', [
                $date . ' 00:00:00',
                $date . ' 23:59:59',
            ]);
        }

        return $query;
    }

    public function filename(): string
    {
        return 'Application_Errors_' . date('Y_m_d_His');
    }
}
