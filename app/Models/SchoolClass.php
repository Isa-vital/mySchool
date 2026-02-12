<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolClass extends Model
{
    protected $fillable = ['name', 'code', 'level', 'description', 'is_active'];

    protected $casts = ['is_active' => 'boolean', 'level' => 'integer'];

    public function sections()
    {
        return $this->hasMany(Section::class);
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'class_subject');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function students()
    {
        return $this->hasManyThrough(Student::class, Enrollment::class, 'school_class_id', 'id', 'id', 'student_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
