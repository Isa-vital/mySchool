<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Term;
use Illuminate\Http\Request;

class AcademicYearController extends Controller
{
    public function index()
    {
        $academicYears = AcademicYear::with('terms')->orderBy('start_date', 'desc')->paginate(20);
        return view('academic-years.index', compact('academicYears'));
    }

    public function create()
    {
        return view('academic-years.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_current' => 'nullable|boolean',
            'terms' => 'nullable|array',
            'terms.*.name' => 'required_with:terms|string|max:255',
            'terms.*.start_date' => 'required_with:terms|date',
            'terms.*.end_date' => 'required_with:terms|date',
        ]);

        if ($request->boolean('is_current')) {
            AcademicYear::where('is_current', true)->update(['is_current' => false]);
        }

        $academicYear = AcademicYear::create([
            'name' => $validated['name'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'is_current' => $request->boolean('is_current'),
        ]);

        if ($request->filled('terms')) {
            foreach ($request->terms as $term) {
                $academicYear->terms()->create($term);
            }
        }

        return redirect()->route('academic-years.index')->with('success', 'Academic year created successfully.');
    }

    public function show(AcademicYear $academicYear)
    {
        $academicYear->load('terms');
        return view('academic-years.show', compact('academicYear'));
    }

    public function edit(AcademicYear $academicYear)
    {
        $academicYear->load('terms');
        return view('academic-years.edit', compact('academicYear'));
    }

    public function update(Request $request, AcademicYear $academicYear)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        $academicYear->update($validated);
        return redirect()->route('academic-years.index')->with('success', 'Academic year updated successfully.');
    }

    public function destroy(AcademicYear $academicYear)
    {
        $academicYear->delete();
        return redirect()->route('academic-years.index')->with('success', 'Academic year deleted successfully.');
    }

    public function setCurrent(AcademicYear $academicYear)
    {
        AcademicYear::where('is_current', true)->update(['is_current' => false]);
        Term::where('is_current', true)->update(['is_current' => false]);
        $academicYear->update(['is_current' => true]);

        // Set first term as current
        $firstTerm = $academicYear->terms()->orderBy('start_date')->first();
        if ($firstTerm) {
            $firstTerm->update(['is_current' => true]);
        }

        return redirect()->route('academic-years.index')->with('success', 'Academic year set as current.');
    }
}
