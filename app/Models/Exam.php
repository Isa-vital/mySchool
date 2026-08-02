<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    // CHANGED (A2): marks lock/moderation workflow states.
    public const STATUS_DRAFT = 'draft';
    public const STATUS_MARKS_ENTRY_OPEN = 'marks_entry_open';
    public const STATUS_LOCKED = 'locked';
    public const STATUS_PUBLISHED = 'published';

    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_MARKS_ENTRY_OPEN,
        self::STATUS_LOCKED,
        self::STATUS_PUBLISHED,
    ];

    protected $fillable = [
        'name',
        'academic_year_id',
        'term_id',
        'start_date',
        'end_date',
        'description',
        'is_published',
        'status',
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

    // CHANGED (UACE paper rebuild): every new exam pins the active grading ruleset
    // (exam cycle) at creation, so later ruleset revisions never rewrite history.
    protected static function booted()
    {
        static::creating(function (self $exam) {
            $exam->exam_cycle_id ??= ExamCycle::active()?->id;
        });
    }

    public function examCycle()
    {
        return $this->belongsTo(ExamCycle::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * CHANGED (A2): marks may only be written before the exam is locked/published.
     */
    public function acceptsMarks(): bool
    {
        return ! in_array($this->status, [self::STATUS_LOCKED, self::STATUS_PUBLISHED], true);
    }

    public function isLocked(): bool
    {
        return $this->status === self::STATUS_LOCKED;
    }

    /**
     * Allowed status transitions. Unlocking and unpublishing require 'exams.moderate'.
     *
     * @return array<string, array{to: string, moderate: bool}>
     */
    public static function statusTransitions(): array
    {
        return [
            'open'      => ['from' => self::STATUS_DRAFT, 'to' => self::STATUS_MARKS_ENTRY_OPEN, 'moderate' => false],
            'lock'      => ['from' => self::STATUS_MARKS_ENTRY_OPEN, 'to' => self::STATUS_LOCKED, 'moderate' => false],
            'publish'   => ['from' => self::STATUS_LOCKED, 'to' => self::STATUS_PUBLISHED, 'moderate' => false],
            'unlock'    => ['from' => self::STATUS_LOCKED, 'to' => self::STATUS_MARKS_ENTRY_OPEN, 'moderate' => true],
            'unpublish' => ['from' => self::STATUS_PUBLISHED, 'to' => self::STATUS_LOCKED, 'moderate' => true],
        ];
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
