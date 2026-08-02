<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class PaperResult extends Model
{
    use LogsActivity; // marks changes are audit-logged like Grade rows

    public const STATUSES = ['scored', 'absent', 'withheld'];

    protected $fillable = ['enrollment_id', 'subject_id', 'exam_id', 'paper_number', 'raw_percentage', 'status', 'paper_grade', 'graded_by'];

    protected $casts = ['raw_percentage' => 'decimal:2'];

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }
}
