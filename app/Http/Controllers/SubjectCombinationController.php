<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\SubjectCombination;
use Illuminate\Http\Request;

class SubjectCombinationController extends Controller
{
    public function index(Request $request)
    {
        $query = SubjectCombination::query()->with('subjects')->withCount('enrollments');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('code', 'like', "%{$request->search}%")
                    ->orWhere('name', 'like', "%{$request->search}%");
            });
        }

        $combinations = $query->orderBy('code')->paginate(20)->withQueryString();

        return view('subject-combinations.index', compact('combinations'));
    }

    public function create()
    {
        $subjects = Subject::where('is_active', true)->orderBy('name')->get();

        return view('subject-combinations.create', compact('subjects'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateCombination($request);

        $combination = SubjectCombination::create([
            'code' => strtoupper($validated['code']),
            'name' => $validated['name'],
            'level' => 'a_level',
            'is_active' => $request->boolean('is_active', true),
        ]);
        $combination->subjects()->sync($this->subjectSyncPayload($validated));

        return redirect()->route('subject-combinations.index')->with('success', 'Combination created successfully.');
    }

    public function edit(SubjectCombination $subjectCombination)
    {
        $subjects = Subject::where('is_active', true)->orderBy('name')->get();
        $subjectCombination->load('subjects');

        return view('subject-combinations.edit', compact('subjectCombination', 'subjects'));
    }

    public function update(Request $request, SubjectCombination $subjectCombination)
    {
        $validated = $this->validateCombination($request, $subjectCombination->id);

        $subjectCombination->update([
            'code' => strtoupper($validated['code']),
            'name' => $validated['name'],
            'is_active' => $request->boolean('is_active', true),
        ]);
        $subjectCombination->subjects()->sync($this->subjectSyncPayload($validated));

        return redirect()->route('subject-combinations.index')->with('success', 'Combination updated successfully.');
    }

    public function destroy(SubjectCombination $subjectCombination)
    {
        if ($subjectCombination->enrollments()->exists()) {
            return back()->with('error', 'Cannot delete: students are enrolled on this combination. Deactivate it instead.');
        }

        $subjectCombination->subjects()->detach();
        $subjectCombination->delete();

        return redirect()->route('subject-combinations.index')->with('success', 'Combination deleted.');
    }

    protected function validateCombination(Request $request, ?int $ignoreId = null): array
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:subject_combinations,code' . ($ignoreId ? ",{$ignoreId}" : ''),
            'name' => 'required|string|max:255',
            'principal_ids' => 'required|array|size:3',
            'principal_ids.*' => 'integer|exists:subjects,id',
            'subsidiary_ids' => 'nullable|array|max:3',
            'subsidiary_ids.*' => 'integer|exists:subjects,id',
        ], [
            'principal_ids.size' => 'A combination must have exactly 3 principal subjects.',
        ]);

        $overlap = array_intersect($validated['principal_ids'], $validated['subsidiary_ids'] ?? []);
        if ($overlap !== []) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'subsidiary_ids' => 'A subject cannot be both principal and subsidiary.',
            ]);
        }

        return $validated;
    }

    protected function subjectSyncPayload(array $validated): array
    {
        $payload = [];
        foreach ($validated['principal_ids'] as $id) {
            $payload[(int) $id] = ['is_principal' => true];
        }
        foreach ($validated['subsidiary_ids'] ?? [] as $id) {
            $payload[(int) $id] = ['is_principal' => false];
        }

        return $payload;
    }
}
