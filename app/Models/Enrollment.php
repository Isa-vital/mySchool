<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    use LogsActivity;
    protected $fillable = [
        'student_id',
        'school_class_id',
        'section_id',
        'subject_combination_id', // CHANGED: Uganda fit - A-level combination
        'academic_year_id',
        'roll_number',
        'status',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function subjectCombination()
    {
        return $this->belongsTo(SubjectCombination::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * A-Level classes: keep only students whose combination includes the subject.
     * Enrollments without a combination pass through so they surface as data errors
     * instead of silently disappearing. No-op for other levels / no subject.
     */
    public function scopeTakingSubject($query, ?SchoolClass $class, $subjectId)
    {
        if (! $class || ! $subjectId || $class->category() !== 'a_level') {
            return $query;
        }

        return $query->where(function ($q) use ($subjectId) {
            $q->whereNull('subject_combination_id')
                ->orWhereHas('subjectCombination.subjects', fn($s) => $s->where('subjects.id', $subjectId));
        });
    }
}
