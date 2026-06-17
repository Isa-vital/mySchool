<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    protected $fillable = [
        'name',
        'academic_year_id',
        'term_id',
        'start_date',
        'end_date',
        'description',
        'is_published',
        'assessment_format',
        'max_points',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_published' => 'boolean',
        'assessment_format' => 'string',
        'max_points' => 'integer',
    ];

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    public function schedules()
    {
        return $this->hasMany(ExamSchedule::class);
    }

    public function grades()
    {
        return $this->hasMany(Grade::class);
    }
}
