<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Http\Requests\StoreSubjectRequest;
use App\Http\Requests\UpdateSubjectRequest;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index(Request $request)
    {
        $query = Subject::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        $subjects = $query->orderBy('name')->paginate(20)->withQueryString();
        return view('subjects.index', compact('subjects'));
    }

    public function create()
    {
        return view('subjects.create');
    }

    public function store(StoreSubjectRequest $request)
    {
        $validated = $request->validated();

        $subject = Subject::create($validated);
        // CHANGED (A6): weighted assessment components (Paper 1/2, theory + practical).
        $this->syncComponents($subject, $request);

        return redirect()->route('subjects.index')->with('success', 'Subject created successfully.');
    }

    public function edit(Subject $subject)
    {
        // CHANGED (A6): components editable on the subject form.
        $subject->load('components');
        return view('subjects.edit', compact('subject'));
    }

    public function update(UpdateSubjectRequest $request, Subject $subject)
    {
        $validated = $request->validated();

        $subject->update($validated);
        // CHANGED (A6)
        $this->syncComponents($subject, $request);

        return redirect()->route('subjects.index')->with('success', 'Subject updated successfully.');
    }

    /**
     * CHANGED (A6): sync weighted components. Removing a component keeps its grade
     * rows (FK nulls out), which then read as whole-subject scores — avoid removing
     * components mid-term.
     */
    protected function syncComponents(Subject $subject, Request $request): void
    {
        $rows = $request->validate([
            'components' => 'nullable|array',
            'components.*.id' => 'nullable|integer',
            'components.*.name' => 'nullable|string|max:255',
            'components.*.weight' => 'nullable|numeric|min:0.01',
            'components.*.max_score' => 'nullable|numeric|min:1',
        ])['components'] ?? [];

        $keepIds = [];
        foreach ($rows as $row) {
            $name = trim((string) ($row['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $existing = isset($row['id']) ? $subject->components()->whereKey((int) $row['id'])->first() : null;
            $payload = [
                'name' => $name,
                'weight' => (float) ($row['weight'] ?? 1) ?: 1,
                'max_score' => (float) ($row['max_score'] ?? 100) ?: 100,
            ];

            $component = $existing ? tap($existing)->update($payload) : $subject->components()->create($payload);
            $keepIds[] = $component->id;
        }

        $subject->components()->whereNotIn('id', $keepIds)->delete();
    }

    public function destroy(Subject $subject)
    {
        $subject->delete();
        return redirect()->route('subjects.index')->with('success', 'Subject deleted successfully.');
    }
}
