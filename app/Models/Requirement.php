<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Requirement extends Model
{
    protected $fillable = [
        'name',
        'school_class_id',
        'academic_year_id',
        'term_id',
        'quantity',
        'unit',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'quantity' => 'integer',
    ];

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    public function submissions()
    {
        return $this->hasMany(RequirementSubmission::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
