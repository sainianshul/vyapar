<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function show(Lead $lead)
    {
        $lead->load(['buyer', 'seller', 'product', 'requirement']);
        return view('admin.leads.show', compact('lead'));
    }
}
