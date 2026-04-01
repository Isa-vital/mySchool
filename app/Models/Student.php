<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use LogsActivity;
    protected $fillable = [
        'admission_number',
        'first_name',
        'last_name',
        'other_names',
        'gender',
        'date_of_birth',
        'nationality',
        'religion',
        'address',
        'phone',
        'email',
        'blood_group',
        'medical_conditions',
        'previous_school',
        'admission_date',
        'photo',
        'status',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'admission_date' => 'date',
    ];

    public function guardians()
    {
        return $this->belongsToMany(Guardian::class, 'student_guardian')
            ->withPivot('is_primary');
    }

    public function primaryGuardian()
    {
        return $this->guardians()->wherePivot('is_primary', true)->first();
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function currentEnrollment()
    {
        $currentYear = AcademicYear::current();
        return $this->enrollments()->where('academic_year_id', $currentYear?->id)->first();
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function grades()
    {
        return $this->hasMany(Grade::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
