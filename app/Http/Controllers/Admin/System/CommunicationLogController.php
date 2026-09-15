<?php

namespace App\Http\Controllers\Admin\System;

use App\Http\Controllers\Controller;
use App\DataTables\CommunicationLogDataTable;
use App\Models\CommunicationLog;
use Illuminate\Http\Request;

class CommunicationLogController extends Controller
{
    public function index(CommunicationLogDataTable $dataTable)
    {
        return $dataTable->render('admin.system.communication-logs.index');
    }

    public function data(CommunicationLogDataTable $dataTable)
    {
        return $dataTable->ajax();
    }

    public function empty()
    {
        CommunicationLog::query()->truncate();
        return response()->json(['status' => 'success', 'message' => 'Logs cleared successfully.']);
    }
}
