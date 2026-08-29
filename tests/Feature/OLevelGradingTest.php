<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\ActivityScore;
use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Services\AssessmentGradingService;
use App\Services\ReportCardFormatter;
use App\Services\UgandaGrading;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Acceptance tests for the corrected O-Level (UCE) grading model
 * (reverse-engineered from a verified real report card).
 */
class OLevelGradingTest extends TestCase
{
    use RefreshDatabase;

    protected AcademicYear $year;
    protected SchoolClass $s2;
    protected Student $student;
    protected Enrollment $enrollment;
    protected Exam $exam;
    protected \App\Models\User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = \App\Models\User::factory()->create();

        $this->year = AcademicYear::create([
            'name' => '2026',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'is_current' => true,
        ]);

        $this->s2 = SchoolClass::create(['name' => 'S.2', 'code' => 'S2', 'level' => 9, 'category' => 'o_level']);

        $term = \App\Models\Term::create([
            'academic_year_id' => $this->year->id,
            'name' => 'Term 1',
            'start_date' => '2026-02-01',
            'end_date' => '2026-05-01',
        ]);

        $this->student = Student::create([
            'admission_number' => 'STU-100',
            'first_name' => 'Murungi',
            'last_name' => 'Angel',
            'gender' => 'female',
            'date_of_birth' => '2011-01-01',
            'admission_date' => '2026-01-10',
            'status' => 'active',
        ]);

        $this->enrollment = Enrollment::create([
            'student_id' => $this->student->id,
            'school_class_id' => $this->s2->id,
            'academic_year_id' => $this->year->id,
            'status' => 'active',
        ]);

        $this->exam = Exam::create([
            'name' => 'Term 1 Assessment',
            'academic_year_id' => $this->year->id,
            'term_id' => $term->id,
            'start_date' => '2026-03-01',
            'end_date' => '2026-03-10',
            'status' => 'marks_entry_open',
        ]);
    }

    protected function subject(string $name): \App\Models\Subject
    {
        return \App\Models\Subject::create(['name' => $name, 'code' => strtoupper(substr($name, 0, 3)) . rand(100, 999)]);
    }

    protected function oLevelRanges(): array
    {
        return AssessmentGradingService::rangesForFormat('o-level');
    }

    /** §0: an E-graded subject must score FEWER points than an A-graded subject. */
    public function test_points_scale_is_not_inverted(): void
    {
        $ranges = $this->oLevelRanges();
        $a = AssessmentGradingService::resolve(90, $ranges);
        $e = AssessmentGradingService::resolve(10, $ranges);

        $this->assertSame('A', $a['grade']);
        $this->assertSame('E', $e['grade']);
        $this->assertGreaterThan($e['points'], $a['points'], 'E must score fewer points than A — the scale was inverted.');
        $this->assertSame(4.0, $a['points']);
        $this->assertSame(0.0, $e['points']);

        // Points map used by UgandaGrading agrees with the scale.
        $this->assertSame(4, UgandaGrading::oLevelCompetencyPoints('A'));
        $this->assertSame(0, UgandaGrading::oLevelCompetencyPoints('E'));
    }

    /** §1: band boundaries match the verified reference card. */
    public function test_band_boundaries_match_reference_card(): void
    {
        $ranges = $this->oLevelRanges();
        $expected = [
            [80, 'A', 4, 'Exceptional'],
            [79.99, 'B', 3, 'Outstanding'],
            [60, 'B', 3, 'Outstanding'],
            [59.99, 'C', 2, 'Satisfactory'],
            [50, 'C', 2, 'Satisfactory'],
            [49.99, 'D', 1, 'Basic'],
            [40, 'D', 1, 'Basic'],
            [39.99, 'E', 0, 'Elementary'],
            [0, 'E', 0, 'Elementary'],
        ];

        foreach ($expected as [$marks, $grade, $points, $descriptor]) {
            $resolved = AssessmentGradingService::resolve($marks, $ranges);
            $this->assertSame($grade, $resolved['grade'], "{$marks}% must band to {$grade}");
            $this->assertSame((float) $points, $resolved['points'], "{$marks}% must score {$points} points");
            $this->assertSame($descriptor, $resolved['description']);
        }
    }

    /** §2: a null mark must resolve to NOT YET ASSESSED — never grade E via the lowest-band fallback. */
    public function test_null_marks_resolve_to_not_yet_assessed_not_e(): void
    {
        $resolved = AssessmentGradingService::resolveMark(null, $this->oLevelRanges());

        $this->assertSame(AssessmentGradingService::STATUS_NOT_YET_ASSESSED, $resolved['status']);
        $this->assertNull($resolved['grade']);
        $this->assertNull($resolved['points']);
        $this->assertSame('NOT YET ASSESSED', $resolved['description']);

        // Same through the full O-Level path: a grade row with no mark entered.
        $subject = $this->subject('Biology');
        Grade::create([
            'exam_id' => $this->exam->id,
            'student_id' => $this->student->id,
            'subject_id' => $subject->id,
            'school_class_id' => $this->s2->id,
            'marks_obtained' => null,
        ]);

        $result = AssessmentGradingService::computeOLevelFinalMark($this->enrollment, $subject, $this->exam);
        $this->assertSame(AssessmentGradingService::STATUS_NOT_YET_ASSESSED, $result['status']);
        $this->assertNull($result['grade']);
    }

    /** §8: reference-card regression — activities 2.55, 1.80, 2.40 + EOT 52.00 => final 67, grade B, points 3. */
    public function test_reference_card_history_case(): void
    {
        $subject = $this->subject('History and Political Education');

        foreach ([1 => 2.55, 2 => 1.80, 3 => 2.40] as $number => $score) {
            ActivityScore::create([
                'enrollment_id' => $this->enrollment->id,
                'subject_id' => $subject->id,
                'exam_id' => $this->exam->id,
                'activity_number' => $number,
                'raw_score' => $score,
                'status' => 'scored',
            ]);
        }

        Grade::create([
            'exam_id' => $this->exam->id,
            'student_id' => $this->student->id,
            'subject_id' => $subject->id,
            'school_class_id' => $this->s2->id,
            'eot_raw_score' => 52.00,
            'eot_max_score' => 80,
            'eot_status' => 'scored',
        ]);

        $result = AssessmentGradingService::computeOLevelFinalMark($this->enrollment, $subject, $this->exam);

        // Intermediate values must stay auditable against the real card.
        $this->assertSame(2.25, $result['activity_avg'], 'activity_avg = mean(2.55, 1.80, 2.40)');
        $this->assertSame(15.0, $result['ca_mark'], 'ca_mark = (2.25 / 3.0) * 20');
        $this->assertSame(67.0, $result['final_mark'], 'final_mark = 15.00 + 52.00');
        $this->assertSame(AssessmentGradingService::STATUS_GRADED, $result['status']);
        $this->assertSame('B', $result['grade']);
        $this->assertSame(3.0, $result['points']);
    }

    /** §6: identifier is teacher-entered — identical activity averages may carry different identifiers. */
    public function test_identifier_is_independent_of_activity_average(): void
    {
        $ict = $this->subject('ICT');
        $math = $this->subject('Mathematics');

        foreach ([$ict->id => 2, $math->id => 1] as $subjectId => $identifier) {
            ActivityScore::create([
                'enrollment_id' => $this->enrollment->id,
                'subject_id' => $subjectId,
                'exam_id' => $this->exam->id,
                'activity_number' => 1,
                'raw_score' => 1.5, // identical average for both subjects
                'status' => 'scored',
            ]);

            Grade::create([
                'exam_id' => $this->exam->id,
                'student_id' => $this->student->id,
                'subject_id' => $subjectId,
                'school_class_id' => $this->s2->id,
                'eot_raw_score' => 40,
                'eot_max_score' => 80,
                'eot_status' => 'scored',
                'identifier' => $identifier,
            ]);
        }

        $ictResult = AssessmentGradingService::computeOLevelFinalMark($this->enrollment, $ict, $this->exam);
        $mathResult = AssessmentGradingService::computeOLevelFinalMark($this->enrollment, $math, $this->exam);

        $this->assertSame(1.5, $ictResult['activity_avg']);
        $this->assertSame(1.5, $mathResult['activity_avg']);
        $this->assertSame(2, $ictResult['identifier']);
        $this->assertSame(1, $mathResult['identifier'], 'Identifier must never be derived/overwritten from AVG.');
    }

    /** §8: an EOT score above its max must be rejected at save — never persisted (a real card printed 1,066.67 / 80). */
    public function test_eot_score_exceeding_max_is_rejected_at_save(): void
    {
        $subject = $this->subject('Chemistry');

        $this->expectException(\InvalidArgumentException::class);

        Grade::create([
            'exam_id' => $this->exam->id,
            'student_id' => $this->student->id,
            'subject_id' => $subject->id,
            'school_class_id' => $this->s2->id,
            'eot_raw_score' => 1066.67,
            'eot_max_score' => 80,
            'eot_status' => 'scored',
        ]);
    }

    /** §5a: an activity score above the configured activity max must be rejected at save. */
    public function test_activity_score_exceeding_max_is_rejected_at_save(): void
    {
        $subject = $this->subject('Physics');

        $this->expectException(\InvalidArgumentException::class);

        ActivityScore::create([
            'enrollment_id' => $this->enrollment->id,
            'subject_id' => $subject->id,
            'exam_id' => $this->exam->id,
            'activity_number' => 1,
            'raw_score' => 3.5, // max is 3.0
            'status' => 'scored',
        ]);
    }

    /** §3: oLevelOverallLevel() and the report summary's overall grade agree on the same student. */
    public function test_overall_grade_formulas_agree(): void
    {
        $marks = ['Kiswahili' => 80.0, 'English Language' => 84.0, 'Agriculture' => 69.0, 'CRE' => 59.0];
        $grades = collect();
        foreach ($marks as $name => $mark) {
            $subject = $this->subject($name);
            $grades->push(Grade::create([
                'exam_id' => $this->exam->id,
                'student_id' => $this->student->id,
                'subject_id' => $subject->id,
                'school_class_id' => $this->s2->id,
                'marks_obtained' => $mark, // legacy single-mark rows
            ])->load('subject'));
        }

        $average = array_sum($marks) / count($marks);
        $formatted = ReportCardFormatter::format(
            $this->exam,
            $grades,
            array_sum($marks),
            $average,
            null,
            null,
            $this->s2,
            $this->enrollment
        );

        $this->assertSame('o-level', $formatted['format']);
        $this->assertSame(UgandaGrading::oLevelOverallLevel((float) $formatted['average']), $formatted['overall_grade']);
        $this->assertSame(UgandaGrading::oLevelOverallLevel($average), $formatted['overall_grade']);
    }

    /** §5e: INCOMPLETE / NOT_YET_ASSESSED subjects are excluded from Total Points numerator AND denominator. */
    public function test_total_points_counts_only_resolved_subjects(): void
    {
        $graded = $this->subject('Kiswahili');
        Grade::create([
            'exam_id' => $this->exam->id,
            'student_id' => $this->student->id,
            'subject_id' => $graded->id,
            'school_class_id' => $this->s2->id,
            'marks_obtained' => 80, // A = 4 points
        ]);

        $pending = $this->subject('Geography');
        Grade::create([
            'exam_id' => $this->exam->id,
            'student_id' => $this->student->id,
            'subject_id' => $pending->id,
            'school_class_id' => $this->s2->id,
            'marks_obtained' => null, // NOT YET ASSESSED
        ]);

        $grades = Grade::with('subject')->where('exam_id', $this->exam->id)->get();
        $formatted = ReportCardFormatter::format($this->exam, $grades, 80, 80, null, null, $this->s2, $this->enrollment);

        $this->assertSame(4, $formatted['total_points']);
        $this->assertSame(1, $formatted['resolved_subject_count']);
        $this->assertSame(4, $formatted['max_total_points'], 'Denominator = resolved subjects × 4 — never a fixed constant.');
        $this->assertSame(2, $formatted['subject_count']);
    }

    /** §4: an S.4 report must no longer invoke the stanine/aggregate/division path. */
    public function test_s4_national_result_uses_competency_not_divisions(): void
    {
        $result = UgandaGrading::nationalResult('UCE', [53, 80, 84, 67, 44, 53, 33, 42, 22, 69, 59]);

        $this->assertNull($result['aggregate'], 'No aggregate — divisions were retired for UCE.');
        $this->assertStringNotContainsString('Division', (string) $result['label']);
        $this->assertStringContainsString('Achievement Level', (string) $result['label']);

        // PLE still uses divisions (unchanged).
        $ple = UgandaGrading::nationalResult('PLE', [90, 85, 70, 65]);
        $this->assertStringContainsString('Division', (string) $ple['label']);
        $this->assertNotNull($ple['aggregate']);
    }

    /** Legacy pre-CBC helpers survive for historical records but no longer feed UCE labels. */
    public function test_legacy_uce_helpers_still_work_for_historical_records(): void
    {
        $this->assertSame(8, UgandaGrading::uceAggregate([1, 1, 1, 1, 1, 1, 1, 1, 9]));
        $this->assertSame('Division 1', UgandaGrading::uceDivision(8));
    }

    /** Shared save path (admin + teacher portal): reference-card case through OLevelMarksService. */
    public function test_shared_marks_service_saves_and_computes_final_mark(): void
    {
        $subject = $this->subject('History');

        \App\Services\OLevelMarksService::save($this->exam, $this->s2, $subject, [
            [
                'student_id' => $this->student->id,
                'activities' => [1 => '2.55', 2 => '1.80', 3 => '2.40'],
                'identifier' => '2',
                'eot_raw_score' => '52',
                'eot_status' => 'scored',
            ],
        ], $this->user->id);

        $grade = Grade::where('exam_id', $this->exam->id)->where('subject_id', $subject->id)->firstOrFail();
        $this->assertSame(67.0, (float) $grade->marks_obtained, 'final = (2.25/3)*20 + 52 = 67');
        $this->assertSame('B', $grade->grade_letter);
        $this->assertSame(2, $grade->identifier);
        $this->assertSame(3, ActivityScore::where('subject_id', $subject->id)->count());
    }

    /** Project work gets its own grade and is NEVER merged into the subject's final mark. */
    public function test_project_score_has_own_grade_and_never_merges_into_final(): void
    {
        $subject = $this->subject('Art and Design');

        \App\Services\OLevelMarksService::save($this->exam, $this->s2, $subject, [
            [
                'student_id' => $this->student->id,
                'activities' => [1 => '2.25'],
                'eot_raw_score' => '52',
                'eot_status' => 'scored',
                'project_score_raw' => '9', // 9/10 = 90% => own grade A
            ],
        ], $this->user->id);

        $grade = Grade::where('exam_id', $this->exam->id)->where('subject_id', $subject->id)->firstOrFail();
        $this->assertSame(9.0, (float) $grade->project_score_raw);
        $this->assertSame(10.0, (float) $grade->project_score_max);
        // final = (2.25/3)*20 + 52 = 67 — the 9-point project must NOT appear in it.
        $this->assertSame(67.0, (float) $grade->marks_obtained);

        $result = AssessmentGradingService::computeOLevelFinalMark($this->enrollment, $subject, $this->exam);
        $this->assertSame('A', $result['project_grade'], 'Project graded on its own (90% => A)');
        $this->assertSame('B', $result['grade'], 'Subject grade unaffected by project');
    }

    /** A project score above its max must be rejected at save. */
    public function test_project_score_exceeding_max_is_rejected_at_save(): void
    {
        $subject = $this->subject('Agriculture');

        $this->expectException(\InvalidArgumentException::class);

        Grade::create([
            'exam_id' => $this->exam->id,
            'student_id' => $this->student->id,
            'subject_id' => $subject->id,
            'school_class_id' => $this->s2->id,
            'project_score_raw' => 12,
            'project_score_max' => 10,
        ]);
    }
}
