<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubjectPaper extends Model
{
    protected $fillable = ['subject_id', 'paper_number', 'paper_code', 'display_name'];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function label(): string
    {
        return $this->paper_code ?: ($this->display_name ?: 'Paper ' . $this->paper_number);
    }
}
