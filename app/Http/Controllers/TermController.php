<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Term;
use App\Http\Requests\StoreTermRequest;
use App\Http\Requests\UpdateTermRequest;

class TermController extends Controller
{
    public function index(AcademicYear $academicYear)
    {
        $terms = $academicYear->terms()->orderBy('start_date')->get();
        return view('terms.index', compact('academicYear', 'terms'));
    }

    public function create(AcademicYear $academicYear)
    {
        return view('terms.create', compact('academicYear'));
    }

    public function store(StoreTermRequest $request, AcademicYear $academicYear)
    {
        $validated = $request->validated();

        if ($request->boolean('is_current')) {
            // Only one current term across the whole system
            Term::where('is_current', true)->update(['is_current' => false]);
        }

        $academicYear->terms()->create([
            'name' => $validated['name'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'is_current' => $request->boolean('is_current'),
        ]);

        return redirect()->route('academic-years.terms.index', $academicYear)
            ->with('success', 'Term added successfully.');
    }

    public function edit(AcademicYear $academicYear, Term $term)
    {
        abort_unless($term->academic_year_id === $academicYear->id, 404);
        return view('terms.edit', compact('academicYear', 'term'));
    }

    public function update(UpdateTermRequest $request, AcademicYear $academicYear, Term $term)
    {
        abort_unless($term->academic_year_id === $academicYear->id, 404);

        $validated = $request->validated();

        if ($request->boolean('is_current')) {
            Term::where('is_current', true)->where('id', '!=', $term->id)->update(['is_current' => false]);
        }

        $term->update([
            'name' => $validated['name'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'is_current' => $request->boolean('is_current'),
        ]);

        return redirect()->route('academic-years.terms.index', $academicYear)
            ->with('success', 'Term updated successfully.');
    }

    public function setCurrent(AcademicYear $academicYear, Term $term)
    {
        abort_unless($term->academic_year_id === $academicYear->id, 404);

        Term::where('is_current', true)->update(['is_current' => false]);
        $term->update(['is_current' => true]);

        return redirect()->route('academic-years.terms.index', $academicYear)
            ->with('success', $term->name . ' set as current term.');
    }

    public function destroy(AcademicYear $academicYear, Term $term)
    {
        abort_unless($term->academic_year_id === $academicYear->id, 404);

        $term->delete();

        return redirect()->route('academic-years.terms.index', $academicYear)
            ->with('success', 'Term deleted successfully.');
    }
}
