<?php

namespace App\Http\Controllers;

use App\Models\AiChatMessage;
use App\Services\KarwanAiKnowledgeService;
use App\Services\KarwanAiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class KarwanAiController extends Controller
{
    public function chat(
        Request $request,
        KarwanAiService $karwanAi,
        KarwanAiKnowledgeService $knowledgeService
    ): JsonResponse {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $sessionToken = $this->sessionToken($request);
        $customerId = session('customer_portal_id');

        $answer = $knowledgeService->answer($data['message'])
            ?? $karwanAi->answer($data['message']);

        if (Schema::hasTable('ai_chat_messages')) {
            AiChatMessage::create([
                'session_token' => $sessionToken,
                'customer_id' => $customerId,
                'sender' => 'user',
                'language' => $answer['language'],
                'category' => $answer['category'],
                'message' => $data['message'],
                'needs_human_support' => false,
            ]);

            AiChatMessage::create([
                'session_token' => $sessionToken,
                'customer_id' => $customerId,
                'sender' => 'assistant',
                'language' => $answer['language'],
                'category' => $answer['category'],
                'message' => $answer['reply'],
                'needs_human_support' => $answer['needs_human_support'],
            ]);
        }

        return response()->json([
            'success' => true,
            'reply' => $answer['reply'],
            'language' => $answer['language'],
            'category' => $answer['category'],
            'needs_human_support' => $answer['needs_human_support'],
        ]);
    }

    public function history(Request $request): JsonResponse
    {
        if (! Schema::hasTable('ai_chat_messages')) {
            return response()->json([
                'messages' => [],
            ]);
        }

        $messages = AiChatMessage::query()
            ->where('session_token', $this->sessionToken($request))
            ->latest('id')
            ->limit(30)
            ->get()
            ->reverse()
            ->values()
            ->map(fn (AiChatMessage $message): array => [
                'sender' => $message->sender,
                'message' => $message->message,
                'created_at' => $message->created_at?->format('h:i A'),
            ]);

        return response()->json([
            'messages' => $messages,
        ]);
    }

    public function clear(Request $request): JsonResponse
    {
        if (Schema::hasTable('ai_chat_messages')) {
            AiChatMessage::query()
                ->where('session_token', $this->sessionToken($request))
                ->delete();
        }

        return response()->json([
            'success' => true,
        ]);
    }

    private function sessionToken(Request $request): string
    {
        return hash('sha256', $request->session()->getId());
    }
}