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
        // O-Level activity/CA layer: EOT score, teacher-entered identifier, project work.
        'eot_raw_score',
        'eot_max_score',
        'eot_status',
        'identifier',
        'project_score_raw',
        'project_score_max',
        'project_status',
        'grade_letter',
        'achievement_level',
        'remarks',
        'graded_by',
    ];

    protected $casts = [
        'marks_obtained' => 'decimal:2',
        'ca_marks' => 'decimal:2',
        'eot_raw_score' => 'decimal:2',
        'eot_max_score' => 'decimal:2',
        'project_score_raw' => 'decimal:2',
        'project_score_max' => 'decimal:2',
        'identifier' => 'integer',
    ];

    protected static function booted(): void
    {
        // Save-time guards (all DB drivers): identifier is 1/2/3 or null, and an EOT
        // score above its max must never persist (a real card printed 1,066.67 / 80).
        static::saving(function (self $grade) {
            if ($grade->identifier !== null && ! in_array((int) $grade->identifier, [1, 2, 3], true)) {
                throw new \InvalidArgumentException('Identifier must be 1, 2 or 3.');
            }
            if ($grade->eot_raw_score !== null && $grade->eot_max_score !== null
                && (float) $grade->eot_raw_score > (float) $grade->eot_max_score) {
                throw new \InvalidArgumentException('EOT score cannot exceed its maximum of ' . (float) $grade->eot_max_score . '.');
            }
            if ($grade->project_score_raw !== null && $grade->project_score_max !== null
                && (float) $grade->project_score_raw > (float) $grade->project_score_max) {
                throw new \InvalidArgumentException('Project score cannot exceed its maximum of ' . (float) $grade->project_score_max . '.');
            }
        });
    }

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
