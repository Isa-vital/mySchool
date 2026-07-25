<?php

namespace App\Jobs;

// CHANGED (A3): whole-class report card PDFs are generated on the queue instead of
// synchronously in the request (a 40+ student class risked a PHP timeout), and class
// totals are computed ONCE for the run instead of once per student (was O(N²)).

use App\Models\Exam;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Services\ReportCardCompositionService;
use App\Services\ReportCardDataService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class GenerateBulkReportCardsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 900;

    public function __construct(
        protected int $examId,
        protected int $classId,
        protected int $userId,
        protected string $progressKey,
    ) {}

    public static function progressCacheKey(string $key): string
    {
        return 'bulk-report-cards:' . $key;
    }

    protected function setProgress(array $state): void
    {
        Cache::put(self::progressCacheKey($this->progressKey), $state, now()->addHours(2));
    }

    public function handle(): void
    {
        $exam = Exam::findOrFail($this->examId);
        $schoolClass = SchoolClass::findOrFail($this->classId);

        $students = Student::whereHas('enrollments', function ($q) use ($exam) {
            $q->where('school_class_id', $this->classId)
                ->where('academic_year_id', $exam->academic_year_id)
                ->where('status', 'active');
        })->orderBy('first_name')->get();

        $total = $students->count();
        $this->setProgress(['status' => 'running', 'done' => 0, 'total' => $total, 'url' => null]);

        if ($total === 0) {
            $this->setProgress(['status' => 'failed', 'done' => 0, 'total' => 0, 'url' => null, 'message' => 'No enrolled students found for this class.']);
            return;
        }

        // O(N²) fix: rank the class ONCE, reuse for every student.
        $classTotals = ReportCardCompositionService::classTotals($exam, $this->classId, $exam->academic_year_id);

        $reports = [];
        foreach ($students as $index => $student) {
            $reports[] = ReportCardDataService::buildReportData($student, $exam, $classTotals);
            $this->setProgress(['status' => 'running', 'done' => $index + 1, 'total' => $total, 'url' => null]);
        }

        $pdf = Pdf::loadView('report-cards.pdf-bulk', [
            'exam' => $exam,
            'className' => $schoolClass->name,
            'reports' => $reports,
        ]);

        $fileName = 'report-cards/' . preg_replace('/[^A-Za-z0-9\-_]+/', '-', "{$schoolClass->code}-{$exam->name}") . '-' . $this->progressKey . '.pdf';
        Storage::disk('public')->put($fileName, $pdf->output());

        $this->setProgress([
            'status' => 'done',
            'done' => $total,
            'total' => $total,
            'url' => Storage::disk('public')->url($fileName),
        ]);
    }

    public function failed(\Throwable $e): void
    {
        $this->setProgress(['status' => 'failed', 'done' => 0, 'total' => 0, 'url' => null, 'message' => $e->getMessage()]);
    }
}
