<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequirementSubmission extends Model
{
    protected $fillable = [
        'requirement_id',
        'student_id',
        'quantity_brought',
        'submitted_at',
        'recorded_by',
    ];

    protected $casts = [
        'submitted_at' => 'date',
        'quantity_brought' => 'integer',
    ];

    public function requirement()
    {
        return $this->belongsTo(Requirement::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
