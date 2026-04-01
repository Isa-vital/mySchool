<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DemoRequestController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'school_name' => 'required|string|max:255',
            'contact_name' => 'required|string|max:255',
            'whatsapp' => 'required|string|max:30',
        ]);

        // Store demo request (for now, flash a success message)
        // In production, this could send an email notification or store in DB
        return back()->with('success', 'Thank you! Our team will contact you within one working day.');
    }
}
