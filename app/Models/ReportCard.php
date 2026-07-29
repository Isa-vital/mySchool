<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportCard extends Model
{
    protected $fillable = [
        'student_id',
        'exam_id',
        'total_marks',
        'average',
        'aggregate',
        'result',
        'position',
        'class_size',
        'conduct',
        'class_teacher_comment',
        'head_teacher_comment',
        'next_term_begins',
        'verification_code',
    ];

    protected $casts = [
        'total_marks' => 'decimal:2',
        'average' => 'decimal:2',
        'aggregate' => 'integer',
        'position' => 'integer',
        'class_size' => 'integer',
        'next_term_begins' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }
}
