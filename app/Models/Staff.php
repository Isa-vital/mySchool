<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    protected $fillable = [
        'user_id', 'staff_number', 'first_name', 'last_name', 'gender',
        'date_of_birth', 'phone', 'email', 'address', 'designation',
        'department', 'qualification', 'join_date', 'employment_type',
        'photo', 'is_active',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'join_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function timetableSlots()
    {
        return $this->hasMany(TimetableSlot::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
