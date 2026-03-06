<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Services\ChatAIService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    protected $chatAI;

    public function __construct(ChatAIService $chatAI)
    {
        $this->chatAI = $chatAI;
    }

    public function index(): JsonResponse
    {
        $user = auth()->user();
        $chats = $user->chats()->with('messages')->orderBy('created_at', 'desc')->get();

        return response()->json($chats);
    }

    public function store(Request $request): JsonResponse
    {
        $user = auth()->user();

        $chat = Chat::create([
            'user_id' => $user->id,
            'status' => 'open',
        ]);

        return response()->json($chat, 201);
    }

    public function show($id): JsonResponse
    {
        $chat = Chat::where('user_id', auth()->id())
            ->with('messages.sender')
            ->find($id);

        if (!$chat) {
            return response()->json(['error' => 'Chat not found'], 404);
        }

        return response()->json($chat);
    }

    public function sendMessage(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $chat = Chat::where('user_id', auth()->id())->find($id);

        if (!$chat) {
            return response()->json(['error' => 'Chat not found'], 404);
        }

        $userMessage = ChatMessage::create([
            'chat_id' => $chat->id,
            'sender_id' => auth()->id(),
            'message' => $request->message,
            'sender_type' => 'user',
        ]);

        if ($chat->status === 'open' && !$chat->needs_human) {
            if ($this->chatAI->shouldTransferToHuman($request->message)) {
                $chat->update(['needs_human' => true]);

                $aiMessage = ChatMessage::create([
                    'chat_id' => $chat->id,
                    'sender_id' => null,
                    'message' => $this->chatAI->generateResponse($request->message),
                    'sender_type' => 'ai',
                ]);

                return response()->json([
                    'user_message' => $userMessage,
                    'ai_message' => $aiMessage,
                    'needs_human' => true,
                ]);
            }

            $aiResponse = $this->chatAI->generateResponse($request->message);

            $aiMessage = ChatMessage::create([
                'chat_id' => $chat->id,
                'sender_id' => null,
                'message' => $aiResponse,
                'sender_type' => 'ai',
            ]);

            return response()->json([
                'user_message' => $userMessage,
                'ai_message' => $aiMessage,
            ]);
        }

        return response()->json(['user_message' => $userMessage]);
    }

    public function destroy($id): JsonResponse
    {
        $chat = Chat::where('user_id', auth()->id())->find($id);

        if (!$chat) {
            return response()->json(['error' => 'Chat not found'], 404);
        }

        $chat->delete();

        return response()->json(['message' => 'Chat deleted successfully']);
    }
}
