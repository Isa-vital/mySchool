<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CombinationRule extends Model
{
    public $timestamps = false;

    protected $fillable = ['exam_cycle_id', 'paper_count', 'rule_order', 'matcher', 'subject_category_override', 'resulting_grade', 'description'];

    protected $casts = ['matcher' => 'array'];
}
