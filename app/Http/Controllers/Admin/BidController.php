<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\DataTables\Bid\BidDataTable;
use App\Models\RequestBid;
use Illuminate\Http\Request;

class BidController extends Controller
{
    public function index(BidDataTable $dataTable)
    {
        return $dataTable->render('admin.bids.index', [
            'title' => 'All Bids',
            'dataUrl' => route('admin.bids.data')
        ]);
    }

    public function data(BidDataTable $dataTable)
    {
        return $dataTable->ajax();
    }

    public function todayIndex(BidDataTable $dataTable)
    {
        return $dataTable->render('admin.bids.index', [
            'title' => 'Today\'s Bids',
            'dataUrl' => route('admin.bids.today.data')
        ]);
    }

    public function todayData(BidDataTable $dataTable)
    {
        request()->merge(['today' => true]);
        return $dataTable->ajax();
    }

    public function active(BidDataTable $dataTable)
    {
        return $dataTable->render('admin.bids.index', [
            'title' => 'Active Bids',
            'dataUrl' => route('admin.bids.active.data'),
            'hideStatusFilter' => true
        ]);
    }

    public function activeData(BidDataTable $dataTable)
    {
        request()->merge(['status' => RequestBid::STATUS_PENDING]);
        return $dataTable->ajax();
    }



    /**
     * Display the specified bid.
     */
    public function show($id)
    {
        $bid = RequestBid::with([
            'careRequest.user',
            'careRequest.careType',
            'nurse.user',
            'nurse.careTypes'
        ])->findOrFail($id);

        return view('admin.bids.show', compact('bid'));
    }
}
