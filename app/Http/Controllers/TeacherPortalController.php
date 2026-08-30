<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\AcademicYear;
use App\Models\Enrollment;
use App\Models\Attendance;
use App\Models\TimetableSlot;
use App\Models\Grade;
use App\Models\Exam;
use Illuminate\Http\Request;

class TeacherPortalController extends Controller
{
    protected function getStaff()
    {
        return Staff::where('user_id', auth()->id())->firstOrFail();
    }

    public function dashboard()
    {
        $staff = $this->getStaff();
        $currentYear = AcademicYear::current();

        $todaySlots = collect();
        if ($currentYear) {
            $todaySlots = TimetableSlot::with(['subject', 'schoolClass', 'section'])
                ->where('staff_id', $staff->id)
                ->where('academic_year_id', $currentYear->id)
                ->where('day_of_week', now()->dayOfWeekIso)
                ->orderBy('start_time')
                ->get();
        }

        $myClasses = collect();
        if ($currentYear) {
            $myClasses = TimetableSlot::where('staff_id', $staff->id)
                ->where('academic_year_id', $currentYear->id)
                ->with('schoolClass')
                ->get()
                ->pluck('schoolClass')
                ->unique('id')
                ->values();
        }

        return view('teacher-portal.dashboard', compact('staff', 'todaySlots', 'myClasses'));
    }

    public function timetable()
    {
        $staff = $this->getStaff();
        $currentYear = AcademicYear::current();

        $slots = collect();
        if ($currentYear) {
            $slots = TimetableSlot::with(['subject', 'schoolClass', 'section'])
                ->where('staff_id', $staff->id)
                ->where('academic_year_id', $currentYear->id)
                ->orderBy('day_of_week')
                ->orderBy('start_time')
                ->get()
                ->groupBy('day_of_week');
        }

        return view('teacher-portal.timetable', compact('slots'));
    }

    public function attendance(Request $request)
    {
        $staff = $this->getStaff();
        $currentYear = AcademicYear::current();

        $myClassIds = TimetableSlot::where('staff_id', $staff->id)
            ->when($currentYear, fn($q) => $q->where('academic_year_id', $currentYear->id))
            ->distinct()
            ->pluck('school_class_id');

        $classes = SchoolClass::whereIn('id', $myClassIds)->with('sections')->orderBy('level')->get();
        $selectedClassId = $request->get('class_id');
        $selectedSectionId = $request->get('section_id');
        $date = $request->get('date', today()->format('Y-m-d'));

        $students = collect();
        $attendances = collect();

        if ($selectedClassId && $currentYear) {
            $enrollmentQuery = Enrollment::with('student')
                ->where('school_class_id', $selectedClassId)
                ->where('academic_year_id', $currentYear->id)
                ->where('status', 'active');

            if ($selectedSectionId) {
                $enrollmentQuery->where('section_id', $selectedSectionId);
            }

            $students = $enrollmentQuery->get()->pluck('student');

            $attendances = Attendance::where('school_class_id', $selectedClassId)
                ->where('academic_year_id', $currentYear->id)
                ->where('date', $date)
                ->get()
                ->keyBy('student_id');
        }

        return view('teacher-portal.attendance', compact('classes', 'students', 'attendances', 'selectedClassId', 'selectedSectionId', 'date'));
    }

    public function storeAttendance(Request $request)
    {
        $validated = $request->validate([
            'class_id' => 'required|exists:school_classes,id',
            'section_id' => 'nullable|exists:sections,id',
            'date' => 'required|date|before_or_equal:today',
            'attendance' => 'required|array|min:1',
            'attendance.*.student_id' => 'required|exists:students,id',
            'attendance.*.status' => 'required|in:present,absent,late,excused',
            'attendance.*.remarks' => 'nullable|string|max:500',
        ]);

        $currentYear = AcademicYear::current();

        foreach ($request->attendance as $record) {
            Attendance::updateOrCreate(
                [
                    'student_id' => $record['student_id'],
                    'date' => $request->date,
                ],
                [
                    'school_class_id' => $request->class_id,
                    'section_id' => $request->section_id,
                    'academic_year_id' => $currentYear->id,
                    'status' => $record['status'],
                    'remarks' => $record['remarks'] ?? null,
                    'marked_by' => auth()->id(),
                ]
            );
        }

        return redirect()->route('teacher.attendance', [
            'class_id' => $request->class_id,
            'section_id' => $request->section_id,
            'date' => $request->date,
        ])->with('success', 'Attendance saved successfully.');
    }

    public function grades(Request $request)
    {
        $staff = $this->getStaff();
        $currentYear = AcademicYear::current();
        $exams = Exam::with(['academicYear', 'term'])
            ->where('is_report_card', false)
            ->orderBy('created_at', 'desc')
            ->get();

        $myClassIds = TimetableSlot::where('staff_id', $staff->id)
            ->when($currentYear, fn($q) => $q->where('academic_year_id', $currentYear->id))
            ->distinct()
            ->pluck('school_class_id');

        $classes = SchoolClass::whereIn('id', $myClassIds)->with('subjects')->orderBy('level')->get();
        $subjects = Subject::active()->orderBy('name')->get();

        return view('teacher-portal.grades', compact('exams', 'classes', 'subjects'));
    }

    public function enterGrades(Request $request, Exam $exam)
    {
        abort_if($exam->is_report_card, 422, 'Report-card exams are computed from component exams and do not accept direct grade entry.');

        $staff = $this->getStaff();
        $currentYear = AcademicYear::current();

        $myClassIds = TimetableSlot::where('staff_id', $staff->id)
            ->when($currentYear, fn($q) => $q->where('academic_year_id', $currentYear->id))
            ->distinct()
            ->pluck('school_class_id');

        $classes = SchoolClass::whereIn('id', $myClassIds)->with('subjects')->orderBy('level')->get();
        $selectedClassId = $request->get('class_id');
        $selectedSubjectId = $request->get('subject_id');

        $students = collect();
        $existingGrades = collect();
        $subjects = collect();
        $subject = null;

        // CHANGED: use the exam's grading profile for live preview and validation.
        // CHANGED: ranges computed after the class is known so 'auto' format
        // previews the class-appropriate scale.
        $gradingRanges = collect();
        $fullMarks = 100;
        $passMarks = 40;
        $class = null;

        if ($selectedClassId) {
            $class = SchoolClass::find($selectedClassId);
            $subjects = $class ? $class->subjects : collect();

            $enrollments = Enrollment::with('student')
                ->where('school_class_id', $selectedClassId)
                ->where('academic_year_id', $exam->academic_year_id)
                ->where('status', 'active')
                ->takingSubject($class, $selectedSubjectId)
                ->get();
            $students = $enrollments->pluck('student');

            if ($selectedSubjectId) {
                $subject = Subject::find($selectedSubjectId);
                $existingGrades = Grade::where('exam_id', $exam->id)
                    ->where('school_class_id', $selectedClassId)
                    ->where('subject_id', $selectedSubjectId)
                    // CHANGED (A6): whole-subject rows only; component rows load separately below.
                    ->whereNull('subject_component_id')
                    ->get()
                    ->keyBy('student_id');

                $schedule = \App\Models\ExamSchedule::where('exam_id', $exam->id)
                    ->where('school_class_id', $selectedClassId)
                    ->where('subject_id', $selectedSubjectId)
                    ->first();
                if ($schedule) {
                    $fullMarks = (float) $schedule->full_marks ?: 100;
                    $passMarks = (float) $schedule->pass_marks ?: 40;
                }
            }
        }

        // CHANGED: class-aware ranges so 'auto' format previews correctly.
        $gradingRanges = collect(\App\Services\AssessmentGradingService::previewRangesForExam($exam, $class));

        // O-Level activity/CA layer: same entry grid as the admin Grades screen.
        $isOLevel = $class && \App\Services\AssessmentGradingService::resolveFormat($exam->assessment_format, $class) === 'o-level';
        $activityMax = \App\Services\AssessmentGradingService::activityMaxScore();
        $caWeights = \App\Services\AssessmentGradingService::caWeights();
        $activityColumns = 3;
        $activityScores = [];
        if ($isOLevel && $selectedClassId && $selectedSubjectId) {
            $enrollmentIds = Enrollment::where('school_class_id', $selectedClassId)
                ->where('academic_year_id', $exam->academic_year_id)
                ->where('status', 'active')
                ->pluck('id', 'student_id')
                ->all();

            $existingActivities = \App\Models\ActivityScore::where('exam_id', $exam->id)
                ->where('subject_id', $selectedSubjectId)
                ->whereIn('enrollment_id', array_values($enrollmentIds))
                ->get();

            $studentByEnrollment = array_flip($enrollmentIds);
            foreach ($existingActivities as $score) {
                $studentId = $studentByEnrollment[$score->enrollment_id] ?? null;
                if ($studentId !== null && $score->status === 'scored') {
                    $activityScores[$studentId][(int) $score->activity_number] = (float) $score->raw_score;
                }
            }

            // Every logged slot plus one spare column.
            $activityColumns = max(3, (int) $existingActivities->max('activity_number') + 1);
        }

        // CHANGED (A6): subjects with weighted components use the component-columns table.
        $subjectComponents = $subject ? $subject->components()->get() : collect();
        $existingComponentMarks = ($subject && $subjectComponents->isNotEmpty() && $selectedClassId)
            ? \App\Services\ComponentMarksService::existingMarks($exam, (int) $selectedClassId, $subject)
            : collect();

        return view('teacher-portal.enter-grades', compact('exam', 'classes', 'students', 'existingGrades', 'subjects', 'subject', 'selectedClassId', 'selectedSubjectId', 'gradingRanges', 'fullMarks', 'passMarks', 'subjectComponents', 'existingComponentMarks', 'isOLevel', 'activityMax', 'caWeights', 'activityColumns', 'activityScores'));
    }

    public function saveGrades(Request $request, Exam $exam)
    {
        abort_if($exam->is_report_card, 422, 'Report-card exams are computed from component exams and do not accept direct grade entry.');

        // CHANGED (A2): marks are frozen once the exam is locked/published.
        abort_unless($exam->acceptsMarks(), 423, 'Marks for this exam are locked. Ask a moderator to unlock it before editing.');

        // CHANGED (A6): component-subject path — raw scores per weighted component.
        $componentSubject = Subject::find($request->subject_id);
        if ($componentSubject && $componentSubject->hasComponents() && $request->filled('component_marks')) {
            $request->validate(['class_id' => 'required|exists:school_classes,id', 'subject_id' => 'required|exists:subjects,id']);

            $saved = \App\Services\ComponentMarksService::saveMarks(
                $exam,
                (int) $request->class_id,
                $componentSubject,
                (array) $request->input('component_marks', []),
                (int) auth()->id()
            );

            return redirect()->route('teacher.enter-grades', [
                'exam' => $exam->id,
                'class_id' => $request->class_id,
                'subject_id' => $request->subject_id,
            ])->with('success', "{$saved} component score(s) saved.");
        }

        $validated = $request->validate([
            'class_id' => 'required|exists:school_classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'grades' => 'required|array|min:1',
            'grades.*.student_id' => 'required|exists:students,id',
            'grades.*.marks_obtained' => 'nullable|numeric|min:0|max:500',
            'grades.*.remarks' => 'nullable|string|max:500',
            'olevel_entry' => 'nullable|boolean',
            'grades.*.activities' => 'nullable|array',
            'grades.*.activities.*' => 'nullable|numeric|min:0|max:' . \App\Services\AssessmentGradingService::activityMaxScore(),
            'grades.*.identifier' => 'nullable|integer|in:1,2,3',
            'grades.*.eot_raw_score' => 'nullable|numeric|min:0|max:' . \App\Services\AssessmentGradingService::caWeights()['eot'],
            'grades.*.eot_status' => 'nullable|in:scored,absent,withheld',
            'grades.*.project_score_raw' => 'nullable|numeric|min:0|max:' . \App\Services\AssessmentGradingService::projectMaxScore(),
        ]);

        // CHANGED: resolve against the selected class so 'auto' format uses the
        // class-appropriate scale (primary/o-level/a-level).
        $gradeClass = SchoolClass::find((int) $request->class_id);

        // O-Level path: same shared save as the admin Grades screen — the final
        // mark is computed (CA + EOT), never typed.
        if (
            $gradeClass && \App\Services\AssessmentGradingService::resolveFormat($exam->assessment_format, $gradeClass) === 'o-level'
            && $request->boolean('olevel_entry')
        ) {
            $subject = Subject::findOrFail((int) $request->subject_id);
            \App\Services\OLevelMarksService::save($exam, $gradeClass, $subject, (array) $request->grades, (int) auth()->id());

            return redirect()->route('teacher.enter-grades', [
                'exam' => $exam->id,
                'class_id' => $request->class_id,
                'subject_id' => $request->subject_id,
            ])->with('success', 'O-Level assessment saved: activities, identifiers, project work and EOT scores recorded.');
        }

        foreach ($request->grades as $gradeData) {
            if (isset($gradeData['marks_obtained']) && $gradeData['marks_obtained'] !== null && $gradeData['marks_obtained'] !== '') {
                $marks = (float) $gradeData['marks_obtained'];
                // CHANGED: was resolveForExam($exam, $marks)
                $resolved = \App\Services\AssessmentGradingService::resolveForExam($exam, $marks, $gradeClass);
                $gradeLetter = $resolved['grade'];

                Grade::updateOrCreate(
                    [
                        'exam_id' => $exam->id,
                        'student_id' => $gradeData['student_id'],
                        'subject_id' => $request->subject_id,
                    ],
                    [
                        'school_class_id' => $request->class_id,
                        'marks_obtained' => $marks,
                        'grade_letter' => $gradeLetter,
                        'achievement_level' => $resolved['description'],
                        'remarks' => $gradeData['remarks'] ?? null,
                        'graded_by' => auth()->id(),
                    ]
                );
            }
        }

        return redirect()->route('teacher.enter-grades', [
            'exam' => $exam->id,
            'class_id' => $request->class_id,
            'subject_id' => $request->subject_id,
        ])->with('success', 'Grades saved successfully.');
    }
}
