<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\Request\RequestBidsDataTable;
use App\DataTables\Request\RequestDataTable;
use App\DataTables\Request\RequestNotifiedNursesDataTable;
use App\Http\Controllers\Controller;
use App\Models\CareRequest;
use Illuminate\Http\Request;

class RequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(RequestDataTable $dataTable)
    {
        return $dataTable->render('admin.request.index');
    }

    /**
     * Display a listing of today's requests.
     */
    public function todayIndex(RequestDataTable $dataTable)
    {
        $isToday = true;
        return $dataTable->render('admin.request.index', compact('isToday'));
    }

    /**
     * Get data for datatable (AJAX)
     */
    public function data(RequestDataTable $dataTable)
    {
        return $dataTable->ajax();
    }

    /**
     * Display the specified care request.
     */
    public function show($id)
    {
        $careRequest = CareRequest::with([
            'user',
            'careType',
            'bids.nurse.user'
        ])->findOrFail($id);

        return view('admin.request.show', compact('careRequest'));
    }

    /**
     * Get bids data for the specific request (AJAX).
     */
    public function bidsData($id, RequestBidsDataTable $dataTable)
    {
        return $dataTable->withRequestId($id)->ajax();
    }

    /**
     * Get notified nurses data for the specific request (AJAX).
     */
    public function notifiedNursesData($id, RequestNotifiedNursesDataTable $dataTable)
    {
        return $dataTable->withRequestId($id)->ajax();
    }

    /**
     * Remove the specified care request from storage.
     */
    public function destroy($id)
    {
        $careRequest = CareRequest::findOrFail($id);
        $careRequest->delete();
        return response()->json(['success' => true]);
    }
}
