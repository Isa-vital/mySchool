<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolClass extends Model
{
    protected $fillable = ['name', 'code', 'level', 'category', 'description', 'is_active'];

    protected $casts = ['is_active' => 'boolean', 'level' => 'integer'];

    public function sections()
    {
        return $this->hasMany(Section::class);
    }

    /**
     * Uganda class category, derived from the stored category or numeric level.
     * Convention: Nursery <= 0, P.1-P.7 => 1-7, S.1-S.6 => 8-13.
     */
    public function category(): string
    {
        if (! empty($this->category)) {
            return $this->category;
        }

        return match (true) {
            $this->level <= 0 => 'nursery',
            $this->level <= 4 => 'lower_primary',
            $this->level <= 7 => 'upper_primary',
            $this->level <= 11 => 'o_level',
            default => 'a_level',
        };
    }

    public function isOLevel(): bool
    {
        return $this->category() === 'o_level';
    }

    public function isALevel(): bool
    {
        return $this->category() === 'a_level';
    }

    /**
     * National examination sat at the end of this class, if any.
     * P.7 => PLE, S.4 => UCE, S.6 => UACE.
     */
    public function nationalExam(): ?string
    {
        return match ($this->level) {
            7 => 'PLE',
            11 => 'UCE',
            13 => 'UACE',
            default => null,
        };
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

    /**
     * CHANGED: limit classes to the school level chosen in settings so a
     * secondary-only school never sees primary classes (and vice versa).
     * primary => nursery + P.1-P.7 (level <= 7), secondary => S.1-S.6 (level >= 8).
     */
    public function scopeForSchoolLevel($query, ?string $schoolLevel = null)
    {
        $schoolLevel = $schoolLevel ?? setting('school_level', 'both');

        return match ($schoolLevel) {
            'primary' => $query->where('level', '<=', 7),
            'secondary' => $query->where('level', '>=', 8),
            default => $query,
        };
    }
}
