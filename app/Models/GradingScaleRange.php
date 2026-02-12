<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GradingScaleRange extends Model
{
    protected $fillable = ['grading_scale_id', 'grade', 'min_mark', 'max_mark', 'grade_point', 'description'];

    protected $casts = [
        'min_mark' => 'decimal:2',
        'max_mark' => 'decimal:2',
        'grade_point' => 'decimal:1',
    ];

    public function gradingScale()
    {
        return $this->belongsTo(GradingScale::class);
    }
}
