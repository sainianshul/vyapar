<?php

namespace App\DataTables\Nurses;

use App\Models\User;
use App\Models\NurseProfile;
use Yajra\DataTables\Services\DataTable;

abstract class BaseNursesDataTable extends DataTable
{
    // Override in subclass to auto-filter by nurse_profile.status
    protected array $profileStatuses = [];

    // ── Common Columns ─────────────────────────────────────

    protected function nurseColumn($dt)
    {
        return $dt->addColumn('nurse', function (User $user) {
            $avatar = $user->avatar_html;

            return '
                <div class="d-flex py-1 align-items-center">
                    ' . $avatar . '
                    <div class="flex-fill ms-3">
                        <span class="text-body fw-medium">' . e($user->name) . '</span>
                    </div>
                </div>
            ';
        });
    }

    protected function locationColumn($dt)
    {
        return $dt->addColumn('location', function (User $user) {
            $profile = $user->nurseProfile;
            return '<span class="text-body">' . e($profile?->city ?: '—') . '</span>';
        });
    }

    protected function phoneColumn($dt)
    {
        return $dt->addColumn('phone', function (User $user) {
            return '<span class="text-body">' . e($user->phone ?: '—') . '</span>';
        });
    }

    protected function joinedColumn($dt)
    {
        return $dt->editColumn('created_at', function (User $user) {
            return '<span class="text-body">' . $user->created_at->format('d M Y') . '</span>';
        });
    }

    protected function actionsColumn($dt)
    {
        return $dt->addColumn('actions', function (User $user) {
            $viewUrl = route('admin.nurses.show', $user->id);
            $editUrl = route('admin.nurses.edit', $user->id);

            return '
                <div class="btn-list flex-nowrap justify-content-end">
                    <a href="' . $viewUrl . '" class="btn btn-icon btn-outline-primary btn-sm" title="View">
                        <i class="ti ti-eye"></i>
                    </a>
                    <a href="' . $editUrl . '" class="btn btn-icon btn-outline-warning btn-sm" title="Edit">
                        <i class="ti ti-pencil"></i>
                    </a>
                    <button type="button" class="btn btn-icon btn-outline-danger btn-sm btn-delete" data-id="' . $user->id . '" title="Delete">
                        <i class="ti ti-trash"></i>
                    </button>
                </div>
            ';
        });
    }

    protected function profileStatusColumn($dt)
    {
        return $dt->addColumn('profile_status', function (User $user) {
            $status = $user->nurseProfile?->status;
            $map = [
                NurseProfile::STATUS_PENDING => ['Pending', 'warning'],
                NurseProfile::STATUS_UNDER_REVIEW => ['Under Review', 'info'],
                NurseProfile::STATUS_APPROVED => ['Approved', 'success'],
                NurseProfile::STATUS_REJECTED => ['Rejected', 'danger'],
                NurseProfile::STATUS_SUSPENDED => ['Suspended', 'secondary'],
            ];

            if (!array_key_exists($status, $map)) {
                $status = NurseProfile::STATUS_PENDING;
            }

            return '<span class="badge badge-outline text-' . $map[$status][1] . ' border-' . $map[$status][1] . '">' . e($map[$status][0]) . '</span>';
        });
    }

    protected function filterByName($dt)
    {
        return $dt->filterColumn('nurse', function ($query, $keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('users.name', 'like', "%{$keyword}%")
                  ->orWhere('users.email', 'like', "%{$keyword}%")
                  ->orWhere('users.phone', 'like', "%{$keyword}%");
            });
        });
    }

    // ── Shared Query ───────────────────────────────────────

    public function query(User $model)
    {
        $query = $model->newQuery()
            ->with('nurseProfile')
            ->where('users.role', 2)
            ->select('users.*');

        if (!empty($this->profileStatuses)) {
            $query->whereHas('nurseProfile', function ($q) {
                $q->whereIn('status', $this->profileStatuses);
            });
        }

        return $query;
    }

    // ── Base HTML Builder Setup ────────────────────────────

    protected function configureBuilder($builder, string $tableId, array $columns)
    {
        return $builder
            ->setTableId($tableId)
            ->columns($columns)
            ->minifiedAjax()
            ->orderBy(0) // Default order by first col (usually ID/SNo)
            ->parameters([
                'dom'          => 'rt<"d-flex align-items-center justify-content-between flex-wrap gap-2 mt-4"ip>',
                'drawCallback' => 'function() {
                    $("#' . $tableId . '-skeleton").addClass("d-none");
                    $("#' . $tableId . '-wrapper").removeClass("d-none");
                    
                    if(this.api().data().length === 0) {
                        $("#' . $tableId . '-empty").removeClass("d-none");
                        $("#' . $tableId . '-wrapper").addClass("d-none");
                    } else {
                        $("#' . $tableId . '-empty").addClass("d-none");
                    }
                }',
                'language' => [
                    'paginate' => [
                        'previous' => '<i class="ti ti-chevron-left"></i>',
                        'next'     => '<i class="ti ti-chevron-right"></i>',
                    ],
                ],
            ]);
    }
}
