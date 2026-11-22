<?php

namespace App\Http\Controllers;

use App\Models\SupportConversation;
use App\Models\SupportMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Player side of live support: one conversation per user, polled by the chat widget. */
class SupportChatController extends Controller
{
    public function messages(Request $request)
    {
        $conv = SupportConversation::where('user_id', $request->user()->id)->first();
        if (! $conv) {
            return response()->json(['messages' => [], 'unread' => 0, 'status' => 'new']);
        }

        $after = (int) $request->query('after', 0);
        $rows = SupportMessage::where('conversation_id', $conv->id)->where('id', '>', $after)
            ->orderBy('id')->limit(200)->get();

        if ($request->boolean('seen') && $conv->user_unread > 0) {
            $conv->update(['user_unread' => 0]);
        }

        return response()->json([
            'messages' => $rows->map->toChat()->values(),
            'unread' => $request->boolean('seen') ? 0 : $conv->user_unread,
            'status' => $conv->status,
        ]);
    }

    public function send(Request $request)
    {
        $data = $request->validate(['body' => 'required|string|max:2000']);
        $body = trim($data['body']);
        if ($body === '') {
            return response()->json(['message' => 'Message is empty.'], 422);
        }

        $msg = DB::connection((new SupportConversation)->getConnectionName())->transaction(function () use ($request, $body) {
            $conv = SupportConversation::firstOrCreate(['user_id' => $request->user()->id], ['status' => 'open']);
            $conv->status = 'open';
            $conv->admin_unread = $conv->admin_unread + 1;
            $conv->last_message_at = now();
            $conv->save();

            return SupportMessage::create(['conversation_id' => $conv->id, 'sender' => 'user', 'body' => $body]);
        });

        return response()->json(['message' => $msg->toChat()]);
    }
}
