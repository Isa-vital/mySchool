<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UaceGradePoint extends Model
{
    public $timestamps = false;

    protected $fillable = ['exam_cycle_id', 'grade_code', 'points'];
}
