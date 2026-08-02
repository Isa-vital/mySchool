<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = ['name', 'code', 'type', 'description', 'is_active', 'paper_count', 'subject_category', 'is_subsidiary'];

    // CHANGED (A6): weighted assessment components (Paper 1/2, theory + practical).
    public function components()
    {
        return $this->hasMany(SubjectComponent::class)->orderBy('id');
    }

    public function hasComponents(): bool
    {
        return $this->components()->exists();
    }

    // UACE paper definitions (2-4 papers per principal subject).
    public function papers()
    {
        return $this->hasMany(SubjectPaper::class)->orderBy('paper_number');
    }

    protected $casts = ['is_active' => 'boolean', 'is_subsidiary' => 'boolean'];

    public function classes()
    {
        return $this->belongsToMany(SchoolClass::class, 'class_subject');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
