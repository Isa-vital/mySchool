<?php

namespace App\Models;

use App\Services\AssessmentGradingService;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class ActivityScore extends Model
{
    public const STATUS_SCORED = 'scored';
    public const STATUS_NOT_YET_ADMINISTERED = 'not_yet_administered';

    protected $fillable = [
        'enrollment_id',
        'subject_id',
        'exam_id',
        'activity_number',
        'raw_score',
        'status',
        'recorded_by',
    ];

    protected $casts = [
        'raw_score' => 'decimal:2',
        'activity_number' => 'integer',
    ];

    protected static function booted(): void
    {
        // Save-time cap: a raw score above the configured activity max must never persist.
        static::saving(function (self $score) {
            if ($score->status === self::STATUS_SCORED) {
                $max = AssessmentGradingService::activityMaxScore();
                $raw = $score->raw_score !== null ? (float) $score->raw_score : null;
                if ($raw === null || $raw < 0 || $raw > $max) {
                    throw new InvalidArgumentException("Activity score must be between 0 and {$max}.");
                }
            }
        });
    }

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
