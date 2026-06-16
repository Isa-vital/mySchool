<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use LogsActivity;
    protected $fillable = [
        'admission_number',
        'lin', // CHANGED: EMIS Learner Identification Number
        'uneb_index_number', // CHANGED: UNEB candidate index number
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
        'previous_school_grade', // CHANGED: last grade/class attended at previous school
        'previous_school_attachment', // CHANGED: transfer letter / previous report card file
        'admission_date',
        'photo',
        'status',
        'boarding_status', // CHANGED: Uganda fit - day vs boarding scholar
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'admission_date' => 'date',
    ];

    /**
     * Generate the next sequential admission number based on the most recent student.
     * Preserves the existing prefix and zero-padding (e.g. ADM00152 -> ADM00153).
     */
    public static function nextAdmissionNumber(): string
    {
        // CHANGED: honor the admission_number_prefix setting instead of always
        // re-using the prefix of the last student (which kept it stuck on "ADM").
        $prefix = trim((string) setting('admission_number_prefix', 'ADM'));
        if ($prefix === '') {
            $prefix = 'ADM';
        }

        $padLength = 5;
        $number = 1;

        // Continue the sequence from the most recent student using THIS prefix.
        $last = static::where('admission_number', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('admission_number');

        // CHANGED: previous logic read the last student regardless of prefix:
        // $last = static::orderByDesc('id')->value('admission_number');
        // if ($last && preg_match('/^([A-Za-z]*)(\d+)$/', $last, $m)) {
        //     if ($m[1] !== '') { $prefix = $m[1]; }
        //     $padLength = strlen($m[2]);
        //     $number = (int) $m[2] + 1;
        // }
        if ($last && preg_match('/^' . preg_quote($prefix, '/') . '(\d+)$/', $last, $m)) {
            $padLength = strlen($m[1]);
            $number = (int) $m[1] + 1;
        }

        // Guarantee uniqueness in case of gaps or concurrent inserts.
        do {
            $candidate = $prefix . str_pad((string) $number, $padLength, '0', STR_PAD_LEFT);
            $number++;
        } while (static::where('admission_number', $candidate)->exists());

        return $candidate;
    }

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
