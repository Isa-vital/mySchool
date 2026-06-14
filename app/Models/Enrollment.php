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
}
