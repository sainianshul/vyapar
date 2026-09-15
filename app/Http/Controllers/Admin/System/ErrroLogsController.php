<?php

namespace App\Http\Controllers\Admin\System;

use App\DataTables\System\ErrorLogsDataTable;
use App\Http\Controllers\Controller;
use App\Models\ApplicationError;
use Illuminate\Http\Request;

class ErrroLogsController extends Controller
{
    public function index(ErrorLogsDataTable $dataTable)
    {
        return $dataTable->render('admin.systems.error-logs.index');
    }

    public function data(ErrorLogsDataTable $dataTable)
    {
        return $dataTable->ajax();
    }

    public function empty()
    {
        ApplicationError::query()->truncate();
        return response()->json(['status' => 'success']);
    }

    public function show($id)
    {
        $error = ApplicationError::with('user')->findOrFail($id);

        return view('admin.systems.error-logs.show', compact('error'));
    }

    public function status(Request $request, $id)
    {
        $error = ApplicationError::findOrFail($id);
        $error->status = $request->status;
        if ($request->status == ApplicationError::STATUS_RESOLVED) {
            $error->resolved_at = now();
        }
        $error->save();

        return response()->json(['success' => true, 'message' => 'Status updated successfully.']);
    }

    public function pendingCount()
    {
        $count = ApplicationError::where('status', ApplicationError::STATUS_PENDING)->count();
        return response()->json(['count' => $count]);
    }
}