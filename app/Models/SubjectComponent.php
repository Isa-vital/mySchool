<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// CHANGED (A6): weighted assessment component of a subject within one exam
// (e.g. "Paper 1 (Theory)" weight 60 / "Paper 2 (Practical)" weight 40).
class SubjectComponent extends Model
{
    protected $fillable = ['subject_id', 'name', 'weight', 'max_score'];

    protected $casts = [
        'weight' => 'decimal:2',
        'max_score' => 'decimal:2',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function grades()
    {
        return $this->hasMany(Grade::class);
    }
}
