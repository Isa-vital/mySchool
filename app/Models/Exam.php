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
        'is_report_card',
        'grading_scale_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_published' => 'boolean',
        'assessment_format' => 'string',
        'max_points' => 'integer',
        'is_report_card' => 'boolean',
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

    public function gradingScale()
    {
        return $this->belongsTo(GradingScale::class);
    }

    public function reportComponents()
    {
        return $this->belongsToMany(self::class, 'exam_report_components', 'report_exam_id', 'component_exam_id')
            ->withPivot(['weight', 'display_order'])
            ->withTimestamps()
            ->orderBy('exam_report_components.display_order')
            ->orderBy('exam_report_components.id');
    }

    public function parentReportExams()
    {
        return $this->belongsToMany(self::class, 'exam_report_components', 'component_exam_id', 'report_exam_id')
            ->withPivot(['weight', 'display_order'])
            ->withTimestamps();
    }
}
