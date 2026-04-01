<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Term;
use App\Http\Requests\StoreAcademicYearRequest;
use App\Http\Requests\UpdateAcademicYearRequest;
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

    public function store(StoreAcademicYearRequest $request)
    {
        $validated = $request->validated();

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

    public function update(UpdateAcademicYearRequest $request, AcademicYear $academicYear)
    {
        $validated = $request->validated();

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
