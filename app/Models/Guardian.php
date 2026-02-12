<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Guardian extends Model
{
    protected $fillable = [
        'first_name', 'last_name', 'relationship', 'phone', 'alt_phone',
        'email', 'address', 'occupation', 'national_id',
    ];

    public function students()
    {
        return $this->belongsToMany(Student::class, 'student_guardian')
            ->withPivot('is_primary');
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }
}
