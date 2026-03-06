<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\ChatMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HelpdeskController extends Controller
{
    public function getChats(): JsonResponse
    {
        $user = auth()->user();

        if (!$user->is_helpdesk_agent) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $chats = Chat::with(['user', 'messages.sender'])
            ->whereIn('status', ['open', 'in_progress'])
            ->orWhere('agent_id', $user->id)
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($chats);
    }

    public function getChat($id): JsonResponse
    {
        $user = auth()->user();

        if (!$user->is_helpdesk_agent) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $chat = Chat::with(['user', 'messages.sender'])->find($id);

        if (!$chat) {
            return response()->json(['error' => 'Chat not found'], 404);
        }

        return response()->json($chat);
    }

    public function assignChat(Request $request, $id): JsonResponse
    {
        $user = auth()->user();

        if (!$user->is_helpdesk_agent) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $chat = Chat::find($id);

        if (!$chat) {
            return response()->json(['error' => 'Chat not found'], 404);
        }

        $chat->update([
            'agent_id' => $user->id,
            'status' => 'in_progress',
        ]);

        return response()->json($chat);
    }

    public function sendMessage(Request $request, $id): JsonResponse
    {
        $user = auth()->user();

        if (!$user->is_helpdesk_agent) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $chat = Chat::find($id);

        if (!$chat) {
            return response()->json(['error' => 'Chat not found'], 404);
        }

        $message = ChatMessage::create([
            'chat_id' => $chat->id,
            'sender_id' => $user->id,
            'message' => $request->message,
            'sender_type' => 'agent',
        ]);

        return response()->json($message, 201);
    }

    public function closeChat($id): JsonResponse
    {
        $user = auth()->user();

        if (!$user->is_helpdesk_agent) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $chat = Chat::find($id);

        if (!$chat) {
            return response()->json(['error' => 'Chat not found'], 404);
        }

        $chat->update(['status' => 'closed']);

        return response()->json(['message' => 'Chat closed successfully']);
    }
}
