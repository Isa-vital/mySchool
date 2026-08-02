<?php

namespace App\Http\Controllers;

// CHANGED (UACE paper rebuild): versioned ruleset admin. Boundaries/rules/points
// live in data per exam_cycle. An active cycle is NEVER edited in place —
// clone-then-edit, then activate the new version. Old exams stay pinned to the
// cycle they were graded under.

use App\Models\ExamCycle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExamCycleController extends Controller
{
    public function index(Request $request)
    {
        $cycles = ExamCycle::with(['bandBoundaries', 'combinationRules', 'gradePoints', 'subsidiaryConfig'])
            ->orderByDesc('is_active')
            ->orderByDesc('id')
            ->get();

        $selected = $request->filled('cycle_id')
            ? $cycles->firstWhere('id', (int) $request->cycle_id)
            : $cycles->first();

        return view('uace.cycles', compact('cycles', 'selected'));
    }

    /** Clone a cycle (boundaries + rules + points + subsidiary config) into a new inactive version. */
    public function clone(Request $request, ExamCycle $examCycle)
    {
        $request->validate(['name' => 'required|string|max:255|unique:exam_cycles,name']);

        $copy = DB::transaction(function () use ($examCycle, $request) {
            $copy = ExamCycle::create(['name' => $request->name, 'is_active' => false]);

            foreach ($examCycle->bandBoundaries as $band) {
                $copy->bandBoundaries()->create($band->only(['band_code', 'min_pct', 'max_pct']));
            }
            foreach ($examCycle->combinationRules as $rule) {
                $copy->combinationRules()->create($rule->only([
                    'paper_count',
                    'rule_order',
                    'matcher',
                    'subject_category_override',
                    'resulting_grade',
                    'description',
                ]));
            }
            foreach ($examCycle->gradePoints as $point) {
                $copy->gradePoints()->create($point->only(['grade_code', 'points']));
            }
            if ($examCycle->subsidiaryConfig) {
                $copy->subsidiaryConfig()->create($examCycle->subsidiaryConfig->only([
                    'pass_threshold_pct',
                    'pass_points',
                    'fail_points',
                ]));
            }

            return $copy;
        });

        return redirect()->route('uace-cycles.index', ['cycle_id' => $copy->id])
            ->with('success', "Cloned \"{$examCycle->name}\" into \"{$copy->name}\" (inactive). Adjust it, then activate.");
    }

    /** Activate one cycle; new exams pin to it. Already-pinned exams are untouched. */
    public function activate(ExamCycle $examCycle)
    {
        DB::transaction(function () use ($examCycle) {
            ExamCycle::where('is_active', true)->update(['is_active' => false]);
            $examCycle->update(['is_active' => true]);
        });

        return redirect()->route('uace-cycles.index', ['cycle_id' => $examCycle->id])
            ->with('success', "\"{$examCycle->name}\" is now the active ruleset for NEW exams. Existing exams keep the cycle they were graded under.");
    }
}
