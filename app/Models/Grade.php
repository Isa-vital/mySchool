<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    // CHANGED (A2): every mark create/update/delete is now written to activity_logs
    // (who/when/old/new) so post-entry changes have an audit trail.
    use LogsActivity;
    protected $fillable = [
        'exam_id',
        'student_id',
        'subject_id',
        // CHANGED (A6): NULL = whole-subject score; set = score for one weighted component.
        'subject_component_id',
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

    // CHANGED (A6)
    public function subjectComponent()
    {
        return $this->belongsTo(SubjectComponent::class);
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
