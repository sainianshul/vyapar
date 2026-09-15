<?php

namespace App\Http\Controllers\Admin\Support;

use App\DataTables\Support\FaqDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FaqRequest;
use App\Models\Faq;
use App\Models\SupportCategory;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = SupportCategory::orderBy('name')->get();
        $statuses = Faq::getStatusList();
        
        return view('admin.support.faqs.index', compact('categories', 'statuses'));
    }

    /**
     * Process datatables ajax request.
     */
    public function data(FaqDataTable $dataTable)
    {
        return $dataTable->render('admin.support.faqs.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = SupportCategory::orderBy('name')->get();
        $statuses = Faq::getStatusList();
        
        return view('admin.support.faqs.create', compact('categories', 'statuses'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(FaqRequest $request)
    {
        Faq::create($request->validated());

        return redirect()->route('admin.support.faqs.index')
            ->with('success', 'FAQ created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Faq $faq)
    {
        $categories = SupportCategory::orderBy('name')->get();
        $statuses = Faq::getStatusList();
        
        return view('admin.support.faqs.edit', compact('faq', 'categories', 'statuses'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(FaqRequest $request, Faq $faq)
    {
        $faq->update($request->validated());

        return redirect()->route('admin.support.faqs.index')
            ->with('success', 'FAQ updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Faq $faq)
    {
        $faq->delete();

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'FAQ deleted successfully.']);
        }

        return redirect()->route('admin.support.faqs.index')
            ->with('success', 'FAQ deleted successfully.');
    }
}
