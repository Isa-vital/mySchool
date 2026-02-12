<?php

namespace App\Http\Controllers;

use App\Models\Notice;
use App\Models\SchoolClass;
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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'target_audience' => 'required|in:all,staff,students,parents,specific_class',
            'school_class_id' => 'nullable|exists:school_classes,id',
            'publish_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after_or_equal:publish_date',
            'is_published' => 'nullable|boolean',
        ]);

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

    public function update(Request $request, Notice $notice)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'target_audience' => 'required|in:all,staff,students,parents,specific_class',
            'school_class_id' => 'nullable|exists:school_classes,id',
            'publish_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after_or_equal:publish_date',
            'is_published' => 'nullable|boolean',
        ]);

        $notice->update($validated);
        return redirect()->route('notices.index')->with('success', 'Notice updated successfully.');
    }

    public function destroy(Notice $notice)
    {
        $notice->delete();
        return redirect()->route('notices.index')->with('success', 'Notice deleted successfully.');
    }
}
