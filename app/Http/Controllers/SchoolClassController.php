<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Subject;
use Illuminate\Http\Request;

class SchoolClassController extends Controller
{
    public function index()
    {
        $classes = SchoolClass::with('sections')->orderBy('level')->paginate(20);
        return view('classes.index', compact('classes'));
    }

    public function create()
    {
        $subjects = Subject::active()->orderBy('name')->get();
        return view('classes.create', compact('subjects'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:20',
            'level' => 'nullable|integer',
            'description' => 'nullable|string',
            'subjects' => 'nullable|array',
            'subjects.*' => 'exists:subjects,id',
            'sections' => 'nullable|array',
            'sections.*.name' => 'required_with:sections|string|max:255',
            'sections.*.capacity' => 'nullable|integer|min:1',
        ]);

        $class = SchoolClass::create($validated);

        if ($request->filled('subjects')) {
            $class->subjects()->sync($request->subjects);
        }

        if ($request->filled('sections')) {
            foreach ($request->sections as $section) {
                $class->sections()->create($section);
            }
        }

        return redirect()->route('classes.index')->with('success', 'Class created successfully.');
    }

    public function show(SchoolClass $class)
    {
        $class->load(['sections', 'subjects']);
        return view('classes.show', compact('class'));
    }

    public function edit(SchoolClass $class)
    {
        $class->load(['sections', 'subjects']);
        $subjects = Subject::active()->orderBy('name')->get();
        return view('classes.edit', compact('class', 'subjects'));
    }

    public function update(Request $request, SchoolClass $class)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:20',
            'level' => 'nullable|integer',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'subjects' => 'nullable|array',
            'subjects.*' => 'exists:subjects,id',
        ]);

        $class->update($validated);

        if ($request->has('subjects')) {
            $class->subjects()->sync($request->subjects ?? []);
        }

        return redirect()->route('classes.index')->with('success', 'Class updated successfully.');
    }

    public function destroy(SchoolClass $class)
    {
        $class->delete();
        return redirect()->route('classes.index')->with('success', 'Class deleted successfully.');
    }

    public function storeSection(Request $request, SchoolClass $class)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'capacity' => 'nullable|integer|min:1',
        ]);

        $class->sections()->create($validated);
        return redirect()->route('classes.show', $class)->with('success', 'Section added successfully.');
    }

    public function destroySection(Section $section)
    {
        $classId = $section->school_class_id;
        $section->delete();
        return redirect()->route('classes.show', $classId)->with('success', 'Section removed successfully.');
    }
}
