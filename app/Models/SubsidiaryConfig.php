<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubsidiaryConfig extends Model
{
    public $timestamps = false;

    protected $fillable = ['exam_cycle_id', 'pass_threshold_pct', 'pass_points', 'fail_points'];
}
