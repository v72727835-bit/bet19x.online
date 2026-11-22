<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportConversation;
use App\Models\SupportMessage;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    public function index(Request $request)
    {
        $q = SupportConversation::with('user')->orderByDesc('admin_unread')->orderByDesc('last_message_at');
        if (in_array($request->get('status'), ['open', 'closed'], true)) {
            $q->where('status', $request->get('status'));
        }
        $conversations = $q->paginate(30)->withQueryString();

        $previews = SupportMessage::whereIn('id', function ($sub) use ($conversations) {
            $sub->selectRaw('MAX(id)')->from('support_messages')
                ->whereIn('conversation_id', $conversations->pluck('id')->all() ?: [0])->groupBy('conversation_id');
        })->get()->keyBy('conversation_id');

        return view('admin.support.index', compact('conversations', 'previews'));
    }

    public function show(SupportConversation $conversation)
    {
        $conversation->load('user');
        $messages = $conversation->messages()->orderBy('id')->get();
        $conversation->update(['admin_unread' => 0]);

        return view('admin.support.show', compact('conversation', 'messages'));
    }

    public function messages(Request $request, SupportConversation $conversation)
    {
        $rows = $conversation->messages()->where('id', '>', (int) $request->query('after', 0))->orderBy('id')->get();
        if ($rows->isNotEmpty()) {
            $conversation->update(['admin_unread' => 0]);
        }

        return response()->json(['messages' => $rows->map->toChat()->values(), 'status' => $conversation->status]);
    }

    public function reply(Request $request, SupportConversation $conversation)
    {
        $data = $request->validate(['body' => 'required|string|max:2000']);

        $msg = SupportMessage::create([
            'conversation_id' => $conversation->id,
            'sender' => 'admin',
            'admin_id' => auth('admin')->id(),
            'body' => trim($data['body']),
        ]);
        $conversation->update([
            'user_unread' => $conversation->user_unread + 1,
            'last_message_at' => now(),
            'status' => 'open',
        ]);

        if ($request->expectsJson()) {
            return response()->json(['message' => $msg->toChat()]);
        }

        return back();
    }

    public function status(Request $request, SupportConversation $conversation)
    {
        $request->validate(['status' => 'required|in:open,closed']);
        $conversation->update(['status' => $request->status]);

        return back()->with('status', $request->status === 'closed' ? 'Chat close ho gaya.' : 'Chat dobara open ho gaya.');
    }
}
