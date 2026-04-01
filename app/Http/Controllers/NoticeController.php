<?php

namespace App\Http\Controllers;

use App\Models\Notice;
use App\Models\SchoolClass;
use App\Http\Requests\StoreNoticeRequest;
use App\Http\Requests\UpdateNoticeRequest;
use Illuminate\Http\Request;

class NoticeController extends Controller
{
    public function index()
    {
        $notices = Notice::with('author')->orderBy('created_at', 'desc')->paginate(20);
        return view('notices.index', compact('notices'));
    }

    public function create()
    {
        $classes = SchoolClass::active()->orderBy('level')->get();
        return view('notices.create', compact('classes'));
    }

    public function store(StoreNoticeRequest $request)
    {
        $validated = $request->validated();

        $validated['created_by'] = auth()->id();
        Notice::create($validated);
        return redirect()->route('notices.index')->with('success', 'Notice created successfully.');
    }

    public function show(Notice $notice)
    {
        return view('notices.show', compact('notice'));
    }

    public function edit(Notice $notice)
    {
        $classes = SchoolClass::active()->orderBy('level')->get();
        return view('notices.edit', compact('notice', 'classes'));
    }

    public function update(UpdateNoticeRequest $request, Notice $notice)
    {
        $validated = $request->validated();

        $notice->update($validated);
        return redirect()->route('notices.index')->with('success', 'Notice updated successfully.');
    }

    public function destroy(Notice $notice)
    {
        $notice->delete();
        return redirect()->route('notices.index')->with('success', 'Notice deleted successfully.');
    }
}
