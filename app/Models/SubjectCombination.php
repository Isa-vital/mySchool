<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubjectCombination extends Model
{
    protected $fillable = ['code', 'name', 'level', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'combination_subject')
            ->withPivot('is_principal');
    }

    public function principalSubjects()
    {
        return $this->subjects()->wherePivot('is_principal', true);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
