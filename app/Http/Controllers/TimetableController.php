<?php

namespace App\Http\Controllers;

use App\Models\TimetableSlot;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Subject;
use App\Models\Staff;
use App\Models\AcademicYear;
use App\Http\Requests\StoreTimetableSlotRequest;
use Illuminate\Http\Request;

class TimetableController extends Controller
{
    public function index(Request $request)
    {
        $classes = SchoolClass::active()->with('sections')->orderBy('level')->get();
        $currentYear = AcademicYear::current();
        $selectedClassId = $request->get('class_id');
        $selectedSectionId = $request->get('section_id');

        $slots = collect();
        if ($selectedClassId && $currentYear) {
            $query = TimetableSlot::with(['subject', 'teacher'])
                ->where('school_class_id', $selectedClassId)
                ->where('academic_year_id', $currentYear->id);

            if ($selectedSectionId) {
                $query->where('section_id', $selectedSectionId);
            }

            $slots = $query->orderBy('day_of_week')->orderBy('start_time')->get();
        }

        $subjects = Subject::active()->orderBy('name')->get();
        $teachers = Staff::active()->orderBy('first_name')->get();

        return view('timetable.index', compact('classes', 'slots', 'subjects', 'teachers', 'selectedClassId', 'selectedSectionId'));
    }

    public function create()
    {
        $classes = SchoolClass::active()->with('sections')->orderBy('level')->get();
        $subjects = Subject::active()->orderBy('name')->get();
        $teachers = Staff::active()->orderBy('first_name')->get();

        return view('timetable.create', compact('classes', 'subjects', 'teachers'));
    }

    public function store(StoreTimetableSlotRequest $request)
    {
        $validated = $request->validated();

        $currentYear = AcademicYear::current();
        $validated['academic_year_id'] = $currentYear->id;

        TimetableSlot::create($validated);
        return redirect()->route('timetable.index', ['class_id' => $request->school_class_id, 'section_id' => $request->section_id])
            ->with('success', 'Timetable slot added.');
    }

    public function edit(TimetableSlot $slot)
    {
        $classes = SchoolClass::active()->with('sections')->orderBy('level')->get();
        $subjects = Subject::active()->orderBy('name')->get();
        $teachers = Staff::active()->orderBy('first_name')->get();

        return view('timetable.edit', compact('slot', 'classes', 'subjects', 'teachers'));
    }

    public function update(Request $request, TimetableSlot $slot)
    {
        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'staff_id' => 'nullable|exists:staff,id',
            'day_of_week' => 'required|integer|min:1|max:7',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'room' => 'nullable|string|max:50',
        ]);

        $slot->update($validated);
        return redirect()->back()->with('success', 'Timetable slot updated.');
    }

    public function destroy(TimetableSlot $slot)
    {
        $slot->delete();
        return redirect()->back()->with('success', 'Timetable slot removed.');
    }
}
