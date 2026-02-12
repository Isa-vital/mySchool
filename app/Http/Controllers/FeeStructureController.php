<?php

namespace App\Http\Controllers;

use App\Models\FeeStructure;
use App\Models\FeeType;
use App\Models\SchoolClass;
use App\Models\AcademicYear;
use App\Models\Term;
use Illuminate\Http\Request;

class FeeStructureController extends Controller
{
    public function index(Request $request)
    {
        $query = FeeStructure::with(['feeType', 'schoolClass', 'academicYear', 'term']);

        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        if ($request->filled('class_id')) {
            $query->where('school_class_id', $request->class_id);
        }

        $feeStructures = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();
        $classes = SchoolClass::active()->orderBy('level')->get();

        return view('fee-structures.index', compact('feeStructures', 'academicYears', 'classes'));
    }

    public function create()
    {
        $feeTypes = FeeType::where('is_active', true)->orderBy('name')->get();
        $classes = SchoolClass::active()->orderBy('level')->get();
        $academicYears = AcademicYear::with('terms')->orderBy('start_date', 'desc')->get();

        return view('fee-structures.create', compact('feeTypes', 'classes', 'academicYears'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'fee_type_id' => 'required|exists:fee_types,id',
            'school_class_id' => 'required|exists:school_classes,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id' => 'nullable|exists:terms,id',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        FeeStructure::create($validated);
        return redirect()->route('fee-structures.index')->with('success', 'Fee structure created successfully.');
    }

    public function edit(FeeStructure $feeStructure)
    {
        $feeTypes = FeeType::where('is_active', true)->orderBy('name')->get();
        $classes = SchoolClass::active()->orderBy('level')->get();
        $academicYears = AcademicYear::with('terms')->orderBy('start_date', 'desc')->get();

        return view('fee-structures.edit', compact('feeStructure', 'feeTypes', 'classes', 'academicYears'));
    }

    public function update(Request $request, FeeStructure $feeStructure)
    {
        $validated = $request->validate([
            'fee_type_id' => 'required|exists:fee_types,id',
            'school_class_id' => 'required|exists:school_classes,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id' => 'nullable|exists:terms,id',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $feeStructure->update($validated);
        return redirect()->route('fee-structures.index')->with('success', 'Fee structure updated successfully.');
    }

    public function destroy(FeeStructure $feeStructure)
    {
        $feeStructure->delete();
        return redirect()->route('fee-structures.index')->with('success', 'Fee structure deleted successfully.');
    }
}
