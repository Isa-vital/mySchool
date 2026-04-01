<?php

namespace App\Mail;

use App\Models\Exam;
use App\Models\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ExamResultsPublishedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Student $student,
        public Exam $exam,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Exam Results Published - ' . $this->exam->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.exam-results-published',
        );
    }
}
