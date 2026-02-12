<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    protected $fillable = ['school_class_id', 'name', 'capacity', 'is_active'];

    protected $casts = ['is_active' => 'boolean', 'capacity' => 'integer'];

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function getFullNameAttribute(): string
    {
        return $this->schoolClass->name . ' - ' . $this->name;
    }
}
