<?php

namespace App\Http\Controllers;

use App\Models\ReportCard;
use App\Services\ReportCardFormatter;

// CHANGED (verification): public, read-only lookup for printed report cards.
// Shows only minimal confirmation data — no contacts, no per-subject marks.
class ReportVerificationController extends Controller
{
    public function show(string $code)
    {
        $reportCard = ReportCard::with([
            'student',
            'exam.term',
            'exam.academicYear',
        ])->where('verification_code', strtoupper(trim($code)))->first();

        $enrollment = null;
        if ($reportCard) {
            $enrollment = $reportCard->student?->enrollments()
                ->where('academic_year_id', $reportCard->exam->academic_year_id)
                ->with('schoolClass', 'section')
                ->first();
        }

        return view('verify', [
            'reportCard' => $reportCard,
            'enrollment' => $enrollment,
        ]);
    }
}
