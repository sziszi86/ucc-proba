<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatAIService
{
    protected $apiKey;
    protected $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.key');
    }

    public function generateResponse(string $message, array $context = []): string
    {
        $lowerMessage = strtolower($message);

        // Pre-processing: If user wants a human, we still handle it locally for speed
        if ($this->shouldTransferToHuman($message)) {
            return "I'll connect you with a human agent shortly. Please hold on.";
        }

        // If API key is not set, use the old keyword-based fallback
        if (!$this->apiKey) {
            return $this->getFallbackResponse($message);
        }

        try {
            $systemPrompt = "You are a helpful support assistant for the 'UCC Event Management & Helpdesk System'. 
            The system has these features:
            1. Event Management: Users can create, list, update descriptions, and delete events.
            2. Security: We use JWT and Multi-Factor Authentication (Google Authenticator).
            3. Helpdesk: Users can chat with you (AI) or request a human agent.
            
            Guidelines:
            - Be concise and professional.
            - If the user asks how to do something, explain it based on the features above.
            - If the user wants to speak to a person, tell them you are connecting them.
            - Keep answers short (max 2-3 sentences).";

            $response = Http::post($this->baseUrl . '?key=' . $this->apiKey, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $systemPrompt . "\n\nUser: " . $message]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'maxOutputTokens' => 200,
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['candidates'][0]['content']['parts'][0]['text'] ?? $this->getFallbackResponse($message);
            }

            Log::error('Gemini API error: ' . $response->body());
            return $this->getFallbackResponse($message);

        } catch (\Exception $e) {
            Log::error('ChatAIService exception: ' . $e->getMessage());
            return $this->getFallbackResponse($message);
        }
    }

    public function shouldTransferToHuman(string $message): bool
    {
        $lowerMessage = strtolower($message);

        return str_contains($lowerMessage, 'human')
            || str_contains($lowerMessage, 'agent')
            || str_contains($lowerMessage, 'person')
            || str_contains($lowerMessage, 'speak to someone')
            || str_contains($lowerMessage, 'operator');
    }

    protected function getFallbackResponse(string $message): string
    {
        $lowerMessage = strtolower($message);

        if (str_contains($lowerMessage, 'event') && (str_contains($lowerMessage, 'create') || str_contains($lowerMessage, 'add'))) {
            return "To create an event, navigate to the Events section and click the 'Add Event' button. You'll need to provide a title, date & time, and optionally a description.";
        }

        if (str_contains($lowerMessage, 'event') && (str_contains($lowerMessage, 'update') || str_contains($lowerMessage, 'edit'))) {
            return "To update an event, go to your Events list, find the event you want to modify, and click the edit icon. You can change the description, title, or date.";
        }

        if (str_contains($lowerMessage, 'password') && str_contains($lowerMessage, 'reset')) {
            return "To reset your password, click on 'Forgot Password' on the login page. Enter your email address and you'll receive a password reset link.";
        }

        if (str_contains($lowerMessage, 'mfa') || str_contains($lowerMessage, '2fa')) {
            return "To enable Multi-Factor Authentication (MFA), go to Settings and click 'Setup MFA'. You'll need to scan a QR code with an authenticator app.";
        }

        return "I'm an AI assistant here to help you. I can answer questions about events, account settings, and more. If you need specific assistance, please let me know, or I can connect you with a human agent.";
    }
}
