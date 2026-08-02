<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamCycle extends Model
{
    protected $fillable = ['name', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function bandBoundaries()
    {
        return $this->hasMany(PaperBandBoundary::class);
    }

    public function combinationRules()
    {
        return $this->hasMany(CombinationRule::class);
    }

    public function gradePoints()
    {
        return $this->hasMany(UaceGradePoint::class);
    }

    public function subsidiaryConfig()
    {
        return $this->hasOne(SubsidiaryConfig::class);
    }

    public static function active(): ?self
    {
        return static::where('is_active', true)->orderByDesc('id')->first();
    }
}
