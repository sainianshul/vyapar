<?php

namespace App\DataTables\Nurses;

use App\Models\User;
use App\Models\NurseProfile;

class ApprovedNursesDataTable extends BaseNursesDataTable
{
    protected array $profileStatuses = [
        NurseProfile::STATUS_APPROVED,
    ];

    protected $userStatus = User::STATUS_ACTIVE;

    public function dataTable($query)
    {
        $dt = datatables()->eloquent($query);

        $dt = $this->nurseColumn($dt);
        $dt = $this->locationColumn($dt);
        $dt = $this->joinedColumn($dt);
        $dt = $this->phoneColumn($dt);
        $dt = $this->profileStatusColumn($dt);
        $dt = $this->actionsColumn($dt);
        $dt = $this->filterByName($dt);

        return $dt->rawColumns(['nurse', 'location', 'phone', 'profile_status', 'created_at', 'actions']);
    }
}
