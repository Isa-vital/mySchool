<?php

namespace App\Http\Controllers;

use App\Models\Guardian;
use Illuminate\Http\Request;

class GuardianController extends Controller
{
    public function index(Request $request)
    {
        $query = Guardian::withCount('students');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $guardians = $query->orderBy('first_name')->paginate(20)->withQueryString();
        return view('guardians.index', compact('guardians'));
    }

    public function create()
    {
        return view('guardians.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'relationship' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:20',
            'alt_phone' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
            'occupation' => 'nullable|string|max:255',
            'national_id' => 'nullable|string|max:50',
        ]);

        Guardian::create($validated);
        return redirect()->route('guardians.index')->with('success', 'Guardian added successfully.');
    }

    public function show(Guardian $guardian)
    {
        $guardian->load(['students.enrollments' => function($q) {
            $currentYear = \App\Models\AcademicYear::current();
            if ($currentYear) {
                $q->where('academic_year_id', $currentYear->id)->with(['schoolClass', 'section']);
            }
        }, 'students.invoices']);
        return view('guardians.show', compact('guardian'));
    }

    public function edit(Guardian $guardian)
    {
        return view('guardians.edit', compact('guardian'));
    }

    public function update(Request $request, Guardian $guardian)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'relationship' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:20',
            'alt_phone' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
            'occupation' => 'nullable|string|max:255',
            'national_id' => 'nullable|string|max:50',
        ]);

        $guardian->update($validated);
        return redirect()->route('guardians.show', $guardian)->with('success', 'Guardian updated successfully.');
    }

    public function destroy(Guardian $guardian)
    {
        $guardian->delete();
        return redirect()->route('guardians.index')->with('success', 'Guardian deleted successfully.');
    }
}
