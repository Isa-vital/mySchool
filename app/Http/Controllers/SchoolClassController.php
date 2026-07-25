<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Subject;
use App\Http\Requests\StoreSchoolClassRequest;
use App\Http\Requests\UpdateSchoolClassRequest;
use Illuminate\Http\Request;

class SchoolClassController extends Controller
{
    public function index()
    {
        // CHANGED: hide classes outside the configured school level (settings > School Level)
        // $classes = SchoolClass::with('sections')->orderBy('level')->paginate(20);
        $classes = SchoolClass::with('sections')->forSchoolLevel()->orderBy('level')->paginate(20);
        return view('classes.index', compact('classes'));
    }

    public function create()
    {
        $subjects = Subject::active()->orderBy('name')->get();
        return view('classes.create', compact('subjects'));
    }

    public function store(StoreSchoolClassRequest $request)
    {
        $validated = $request->validated();

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
        // CHANGED: the view references $schoolClass, so expose it under that name
        return view('classes.show', ['schoolClass' => $class]);
    }

    public function edit(SchoolClass $class)
    {
        $class->load(['sections', 'subjects']);
        $subjects = Subject::active()->orderBy('name')->get();
        // CHANGED: the view references $schoolClass, so expose it under that name
        return view('classes.edit', ['schoolClass' => $class, 'subjects' => $subjects]);
    }

    public function update(UpdateSchoolClassRequest $request, SchoolClass $class)
    {
        $validated = $request->validated();

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
