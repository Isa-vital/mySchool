<?php

namespace App\Http\Controllers;

// CHANGED (UACE paper rebuild): A-Level marks are entered PER PAPER, not as one
// blended percentage. Every paper must be explicitly resolved on save — a score,
// Absent, or Withheld. No paper may be left blank (hard server-side validation).

use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\PaperResult;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Services\UaceGradingEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UacePaperGradeController extends Controller
{
    /** Entry grid: students x papers for one exam / A-Level class / subject. */
    public function enter(Request $request, Exam $exam)
    {
        abort_if($exam->is_report_card, 422, 'Papers are entered per sitting (BOT/Mid/EOT), not on the composite report exam.');

        $cycle = UaceGradingEngine::cycleForExam($exam);
        if (! $cycle) {
            return redirect()->route('grades.index')->with('error', 'No active UACE exam cycle (grading ruleset) is configured.');
        }

        $classes = SchoolClass::active()->orderBy('level')->get()
            ->filter(fn($c) => $c->category() === 'a_level')->values();

        $selectedClassId = $request->get('class_id');
        $selectedSubjectId = $request->get('subject_id');

        $class = $selectedClassId ? $classes->firstWhere('id', (int) $selectedClassId) : null;
        $subjects = $class ? $class->subjects()->orderBy('name')->get() : collect();
        $subject = $selectedSubjectId ? $subjects->firstWhere('id', (int) $selectedSubjectId) : null;

        $enrollments = collect();
        $papers = collect();
        $existing = [];
        $noCombinationCount = 0;

        if ($class && $subject) {
            if (! $subject->is_subsidiary && ! $subject->paper_count) {
                return redirect()->route('uace-papers.enter', ['exam' => $exam->id, 'class_id' => $class->id])
                    ->with('error', "\"{$subject->name}\" has no paper count configured. Set its papers (2-4) on the subject's edit page first.");
            }

            $papers = self::paperSlots($subject);

            $enrollments = Enrollment::with('student')
                ->where('school_class_id', $class->id)
                ->where('academic_year_id', $exam->academic_year_id)
                ->where('status', 'active')
                ->takingSubject($class, $subject->id)
                ->get()
                ->sortBy(fn($e) => $e->student?->full_name ?? '')
                ->values();

            $noCombinationCount = $enrollments->whereNull('subject_combination_id')->count();

            $existing = PaperResult::where('exam_id', $exam->id)
                ->where('subject_id', $subject->id)
                ->whereIn('enrollment_id', $enrollments->pluck('id'))
                ->get()
                ->groupBy('enrollment_id')
                ->map(fn($rows) => $rows->keyBy('paper_number'))
                ->all();
        }

        return view('grades.enter-papers', compact(
            'exam',
            'cycle',
            'classes',
            'class',
            'subjects',
            'subject',
            'enrollments',
            'papers',
            'existing',
            'noCombinationCount'
        ));
    }

    /** Save one class/subject grid of paper results. */
    public function save(Request $request, Exam $exam)
    {
        abort_if($exam->is_report_card, 422);

        $cycle = UaceGradingEngine::cycleForExam($exam);
        abort_unless($cycle, 422, 'No active UACE exam cycle configured.');

        $validated = $request->validate([
            'class_id' => 'required|exists:school_classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'rows' => 'array',
            'rows.*.*.status' => 'required|in:scored,absent,withheld',
            'rows.*.*.pct' => 'nullable|numeric|min:0|max:100',
        ]);

        $subject = Subject::with('papers')->findOrFail($validated['subject_id']);
        $paperNumbers = self::paperSlots($subject)->pluck('paper_number')->all();

        $enrollments = Enrollment::with('student')
            ->where('school_class_id', $validated['class_id'])
            ->where('academic_year_id', $exam->academic_year_id)
            ->where('status', 'active')
            ->get()
            ->keyBy('id');

        $rows = $validated['rows'] ?? [];
        $errors = [];
        $writes = [];

        foreach ($rows as $enrollmentId => $paperInputs) {
            $enrollment = $enrollments->get((int) $enrollmentId);
            if (! $enrollment) {
                continue; // not an active enrollment in this class/year — ignore
            }

            $hasExisting = PaperResult::where('exam_id', $exam->id)
                ->where('subject_id', $subject->id)
                ->where('enrollment_id', $enrollment->id)
                ->exists();

            // A row is "touched" when any paper has a mark or a non-default status.
            $touched = collect($paperInputs)->contains(
                fn($p) => ($p['pct'] ?? '') !== '' && $p['pct'] !== null || ($p['status'] ?? 'scored') !== 'scored'
            );

            // Untouched student with no prior results: legitimately skipped.
            if (! $touched && ! $hasExisting) {
                continue;
            }

            // HARD RULE: once a student's subject is being saved, EVERY paper must be
            // resolved — a score, Absent, or Withheld. Blanks are rejected outright.
            foreach ($paperNumbers as $n) {
                $input = $paperInputs[$n] ?? null;
                $status = $input['status'] ?? 'scored';
                $pct = isset($input['pct']) && $input['pct'] !== '' ? (float) $input['pct'] : null;

                if ($status === 'scored' && $pct === null) {
                    $name = $enrollment->student?->full_name ?? "enrollment {$enrollment->id}";
                    $errors[] = "{$name} — Paper {$n}: enter a score, or mark it Absent/Withheld. No paper may be left blank.";
                    continue;
                }

                $writes[] = [
                    'enrollment_id' => $enrollment->id,
                    'paper_number' => (int) $n,
                    'status' => $status,
                    'raw_percentage' => $status === 'scored' ? $pct : null,
                    'paper_grade' => $status === 'scored' ? UaceGradingEngine::bandFor($pct, $cycle) : null,
                ];
            }
        }

        if ($errors !== []) {
            return back()->withInput()->with('error', implode(' ', array_slice($errors, 0, 5))
                . (count($errors) > 5 ? ' (+' . (count($errors) - 5) . ' more)' : ''));
        }

        DB::transaction(function () use ($writes, $exam, $subject) {
            foreach ($writes as $write) {
                PaperResult::updateOrCreate(
                    [
                        'enrollment_id' => $write['enrollment_id'],
                        'subject_id' => $subject->id,
                        'exam_id' => $exam->id,
                        'paper_number' => $write['paper_number'],
                    ],
                    [
                        'raw_percentage' => $write['raw_percentage'],
                        'status' => $write['status'],
                        'paper_grade' => $write['paper_grade'],
                        'graded_by' => auth()->id(),
                    ]
                );
            }
        });

        return redirect()->route('uace-papers.enter', [
            'exam' => $exam->id,
            'class_id' => $validated['class_id'],
            'subject_id' => $subject->id,
        ])->with('success', count($writes) . ' paper result(s) saved.');
    }

    /** Paper slots for a subject: defined papers, else generic Paper 1..N (1 for subsidiaries). */
    private static function paperSlots(Subject $subject)
    {
        $count = $subject->is_subsidiary ? 1 : (int) $subject->paper_count;

        return collect(range(1, max($count, 1)))->map(function ($n) use ($subject) {
            $definition = $subject->papers->firstWhere('paper_number', $n);

            return [
                'paper_number' => $n,
                'label' => $definition?->label() ?? ('Paper ' . $n),
            ];
        });
    }
}
