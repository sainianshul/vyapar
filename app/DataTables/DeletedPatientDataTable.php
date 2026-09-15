<?php

namespace App\DataTables;

use App\Models\User;

class DeletedPatientDataTable extends PatientDataTable
{
    public function dataTable($query)
    {
        $dt = parent::dataTable($query);
        
        $dt->editColumn('deleted_at', function (User $user) {
            return '<div class="text-secondary">' . $user->deleted_at->format('d M Y') . '</div>';
        });

        // Overriding actions for Restore
        $dt->addColumn('actions', function (User $user) {
            return '
                <div class="d-flex gap-1 justify-content-end">
                    <button type="button"
                        class="btn btn-sm btn-light-success border border-success btn-restore fw-bold"
                        data-id="' . $user->id . '"
                        title="Restore Patient">
                        <i class="ki-outline ki-arrows-circle fs-5 me-1"></i> Restore
                    </button>
                </div>
            ';
        });

        return $dt->rawColumns(['name', 'email', 'phone', 'status', 'last_login_at', 'deleted_at', 'actions']);
    }

    public function query(\App\Models\User $model)
    {
        $query = $model->newQuery()
            ->onlyTrashed()
            ->where('role', User::ROLE_USER)
            ->select('users.*');

        // Filter by status
        if (request()->filled('status')) {
            $query->where('status', request('status'));
        }

        return $query;
    }
}
