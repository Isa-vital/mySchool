<?php

namespace App\Http\Controllers;

use App\Mail\DemoRequestMail;
use App\Models\DemoRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class DemoRequestController extends Controller
{
    public function store(Request $request)
    {
        // ── Anti-bot measure 1: Honeypot field ──
        // Real users never see/fill the "company_website" field. If it has any
        // value, silently pretend success so bots don't learn they were caught.
        if (filled($request->input('company_website'))) {
            return back()->with('success', 'Thank you! Our team will contact you within one working day.');
        }

        // ── Anti-bot measure 2: Time trap ──
        // The form embeds the time it was rendered. Humans take more than a few
        // seconds to fill it; bots submit almost instantly.
        $renderedAt = (int) $request->input('form_loaded_at');
        if ($renderedAt <= 0 || (time() - $renderedAt) < 3) {
            return back()->with('success', 'Thank you! Our team will contact you within one working day.');
        }

        $validated = $request->validate([
            'school_name' => 'required|string|max:255',
            'contact_name' => 'required|string|max:255',
            'whatsapp' => 'required|string|max:30',
        ]);

        // Persist the lead so nothing is ever lost.
        $demoRequest = DemoRequest::create([
            'school_name' => $validated['school_name'],
            'contact_name' => $validated['contact_name'],
            'whatsapp' => $validated['whatsapp'],
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
        ]);

        // Notify the team by email. Don't fail the request if mail breaks.
        try {
            Mail::to('info@schoolsystem.techmarketug.com')->send(new DemoRequestMail($demoRequest));
        } catch (\Throwable $e) {
            Log::error('Failed to send demo request email: ' . $e->getMessage());
        }

        return back()->with('success', 'Thank you! Our team will contact you within one working day.');
    }

    /**
     * Admin: list all demo requests (newest first).
     */
    public function index()
    {
        $demoRequests = DemoRequest::orderByDesc('created_at')->paginate(20);

        return view('admin.demo-requests.index', compact('demoRequests'));
    }

    /**
     * Admin: toggle the "contacted" status of a demo request.
     */
    public function markContacted(DemoRequest $demoRequest)
    {
        $demoRequest->update([
            'contacted_at' => $demoRequest->contacted_at ? null : now(),
        ]);

        $message = $demoRequest->contacted_at
            ? 'Marked as contacted.'
            : 'Marked as not contacted.';

        return back()->with('success', $message);
    }

    /**
     * Admin: delete a demo request.
     */
    public function destroy(DemoRequest $demoRequest)
    {
        $demoRequest->delete();

        return back()->with('success', 'Demo request deleted.');
    }
}
