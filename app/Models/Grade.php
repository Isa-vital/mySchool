<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    protected $fillable = [
        'exam_id',
        'student_id',
        'subject_id',
        'school_class_id',
        'marks_obtained',
        'ca_marks',
        'grade_letter',
        'achievement_level',
        'remarks',
        'graded_by',
    ];

    protected $casts = [
        'marks_obtained' => 'decimal:2',
        'ca_marks' => 'decimal:2',
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function gradedBy()
    {
        return $this->belongsTo(User::class, 'graded_by');
    }
}
