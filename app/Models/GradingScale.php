<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GradingScale extends Model
{
    protected $fillable = ['name', 'is_default'];

    protected $casts = ['is_default' => 'boolean'];

    public function ranges()
    {
        return $this->hasMany(GradingScaleRange::class)->orderByDesc('min_mark');
    }

    public function getGradeLetter(float $marks): ?string
    {
        $range = $this->ranges()->where('min_mark', '<=', $marks)->where('max_mark', '>=', $marks)->first();
        return $range?->grade;
    }

    public static function default()
    {
        return static::where('is_default', true)->first();
    }
}
