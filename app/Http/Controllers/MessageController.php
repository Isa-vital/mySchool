<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use App\Http\Requests\StoreMessageRequest;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index()
    {
        $received = Message::with('sender')
            ->where('receiver_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('messages.index', compact('received'));
    }

    public function create()
    {
        $users = User::where('id', '!=', auth()->id())->active()->orderBy('name')->get();
        return view('messages.create', compact('users'));
    }

    public function store(StoreMessageRequest $request)
    {
        $validated = $request->validated();

        Message::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $validated['receiver_id'],
            'subject' => $validated['subject'] ?? null,
            'body' => $validated['body'],
        ]);

        return redirect()->route('messages.index')->with('success', 'Message sent successfully.');
    }

    public function show(Message $message)
    {
        if ($message->receiver_id === auth()->id() && !$message->read_at) {
            $message->update(['read_at' => now()]);
        }

        return view('messages.show', compact('message'));
    }
}
