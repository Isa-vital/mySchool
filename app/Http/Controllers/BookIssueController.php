<?php

namespace App\Http\Controllers;

use App\Models\BookIssue;
use App\Models\Book;
use App\Models\Student;
use App\Models\Staff;
use Illuminate\Http\Request;

class BookIssueController extends Controller
{
    public function index(Request $request)
    {
        $query = BookIssue::with(['book', 'borrower', 'issuedBy']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $issues = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        $books = Book::where('available_copies', '>', 0)->where('is_active', true)->orderBy('title')->get();
        $students = Student::active()->orderBy('first_name')->get();

        return view('book-issues.index', compact('issues', 'books', 'students'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'book_id' => 'required|exists:books,id',
            'borrower_type' => 'required|in:App\Models\Student,App\Models\Staff',
            'borrower_id' => 'required|integer',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issue_date',
        ]);

        $book = Book::find($request->book_id);
        if ($book->available_copies <= 0) {
            return redirect()->back()->with('error', 'No copies available for this book.');
        }

        BookIssue::create([
            'book_id' => $request->book_id,
            'borrower_type' => $request->borrower_type,
            'borrower_id' => $request->borrower_id,
            'issue_date' => $request->issue_date,
            'due_date' => $request->due_date,
            'status' => 'issued',
            'issued_by' => auth()->id(),
        ]);

        $book->decrement('available_copies');

        return redirect()->route('book-issues.index')->with('success', 'Book issued successfully.');
    }

    public function returnBook(BookIssue $bookIssue)
    {
        $bookIssue->update([
            'return_date' => today(),
            'status' => 'returned',
        ]);

        $bookIssue->book->increment('available_copies');

        return redirect()->route('book-issues.index')->with('success', 'Book returned successfully.');
    }
}
