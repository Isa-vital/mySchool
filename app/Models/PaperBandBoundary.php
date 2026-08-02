<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaperBandBoundary extends Model
{
    public $timestamps = false;

    protected $fillable = ['exam_cycle_id', 'band_code', 'min_pct', 'max_pct'];

    protected $casts = ['min_pct' => 'decimal:2', 'max_pct' => 'decimal:2'];
}
