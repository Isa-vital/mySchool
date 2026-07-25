<?php

namespace App\Services;

// CHANGED (UX): one-submit term exam setup — creates the term's component exams
// (e.g. BOT/MID/END), their class/subject schedules, and the composite report-card
// exam with weights, replacing four separate exam-creation forms.

use App\Models\Exam;
use App\Models\ExamSchedule;
use App\Models\SchoolClass;
use App\Models\Term;
use Illuminate\Support\Facades\DB;

class ExamSetupService
{
    /**
     * Create a term's exam sets + composite report exam in one transaction.
     *
     * @param array $sets      [['name' => 'Beginning of Term', 'weight' => 20], ...]
     * @param array $classIds  classes to schedule; empty = all active classes
     * @return array{components: array, report: ?Exam, schedules: int}
     */
    public static function setupTerm(Term $term, array $sets, bool $createReport, ?string $reportName, array $classIds = []): array
    {
        $classes = SchoolClass::active()
            ->with('subjects')
            ->when($classIds !== [], fn($q) => $q->whereIn('id', $classIds))
            ->orderBy('level')
            ->get();

        return DB::transaction(function () use ($term, $sets, $createReport, $reportName, $classes) {
            $components = [];
            $scheduleCount = 0;

            foreach ($sets as $set) {
                $name = trim((string) ($set['name'] ?? ''));
                if ($name === '') {
                    continue;
                }

                // Idempotent: reuse an existing exam with the same name in this term.
                $exam = Exam::firstOrCreate(
                    [
                        'name' => $name,
                        'term_id' => $term->id,
                        'academic_year_id' => $term->academic_year_id,
                    ],
                    [
                        // CHANGED (A1): NULL = auto-detect format from each student's class
                        // ('auto' string is not storable in the enum column).
                        'assessment_format' => null,
                        'max_points' => 100,
                        'is_report_card' => false,
                        'is_published' => false,
                    ]
                );

                // Schedules for every subject of every selected class (skip existing).
                // CHANGED (QA fix): locked/published exams are immutable — don't quietly
                // grow their schedules from the wizard.
                if ($exam->acceptsMarks()) {
                    foreach ($classes as $class) {
                        foreach ($class->subjects as $subject) {
                            $schedule = ExamSchedule::firstOrCreate(
                                [
                                    'exam_id' => $exam->id,
                                    'school_class_id' => $class->id,
                                    'subject_id' => $subject->id,
                                ],
                                [
                                    'full_marks' => 100,
                                    'pass_marks' => 40,
                                ]
                            );
                            if ($schedule->wasRecentlyCreated) {
                                $scheduleCount++;
                            }
                        }
                    }
                }

                $components[] = ['exam' => $exam, 'weight' => (float) ($set['weight'] ?? 0)];
            }

            $reportExam = null;
            if ($createReport && $components !== []) {
                $reportExam = Exam::firstOrCreate(
                    [
                        'name' => trim((string) $reportName) !== '' ? trim((string) $reportName) : ($term->name . ' Report Card'),
                        'term_id' => $term->id,
                        'academic_year_id' => $term->academic_year_id,
                    ],
                    [
                        // CHANGED (A1): NULL = auto-detect (see component exams above).
                        'assessment_format' => null,
                        'max_points' => 100,
                        'is_report_card' => true,
                        'is_published' => false,
                    ]
                );
                $reportExam->update(['is_report_card' => true]);

                $syncPayload = [];
                foreach ($components as $index => $component) {
                    $syncPayload[$component['exam']->id] = [
                        'weight' => $component['weight'],
                        'display_order' => $index,
                    ];
                }
                $reportExam->reportComponents()->sync($syncPayload);
            }

            return [
                'components' => $components,
                'report' => $reportExam,
                'schedules' => $scheduleCount,
            ];
        });
    }
}
