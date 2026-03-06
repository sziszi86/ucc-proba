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
        $message = strtolower(trim($message));

        // 1. Ügynök kérés kezelése (Prioritás)
        if ($this->shouldTransferToHuman($message)) {
            return "I understand you'd like to speak with a human. I'm connecting you to one of our agents right now. Please wait a moment.";
        }

        // 2. Ha van API kulcs, használjuk a valódi AI-t
        if ($this->apiKey && !str_contains($this->apiKey, 'YOUR_')) {
            try {
                $systemPrompt = "You are a helpful support assistant for UCC Event System. Features: Events (CRUD), MFA (Google Auth), Login. Keep it 1-2 sentences.";
                $response = Http::timeout(5)->post($this->baseUrl . '?key=' . $this->apiKey, [
                    'contents' => [['parts' => [['text' => $systemPrompt . "\n\nUser: " . $message]]]]
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    return $data['candidates'][0]['content']['parts'][0]['text'] ?? $this->getSmartFallback($message);
                }
            } catch (\Exception $e) {
                // Hiba esetén megyünk tovább a fallback-re
            }
        }

        // 3. Okos Hibrid Fallback (API kulcs nélkül is működik)
        return $this->getSmartFallback($message);
    }

    public function shouldTransferToHuman(string $message): bool
    {
        $msg = strtolower($message);
        $keywords = ['human', 'agent', 'person', 'speak to', 'operator', 'representative', 'help from a real', 'staff'];
        foreach ($keywords as $keyword) {
            if (str_contains($msg, $keyword)) return true;
        }
        return false;
    }

    protected function getSmartFallback(string $msg): string
    {
        // Üdvözlés
        if (preg_match('/(hi|hello|hey|greetings)/', $msg)) {
            return "Hello! I'm your UCC AI assistant. How can I help you with your events or account settings today?";
        }

        // Esemény létrehozás
        if (str_contains($msg, 'event') && (str_contains($msg, 'create') || str_contains($msg, 'add') || str_contains($msg, 'new'))) {
            return "To create a new event, just click the 'New Event' button on the Events page. You'll need a title and a date.";
        }

        // Esemény módosítás/törlés
        if (str_contains($msg, 'event') && (str_contains($msg, 'edit') || str_contains($msg, 'update') || str_contains($msg, 'delete') || str_contains($msg, 'change'))) {
            return "You can manage your events by clicking the icons on each event card. You can update the description or remove events permanently.";
        }

        // MFA / Biztonság
        if (str_contains($msg, 'mfa') || str_contains($msg, '2fa') || str_contains($msg, 'security') || str_contains($msg, 'protect')) {
            return "For extra security, you can enable Multi-Factor Authentication in your Settings. We support Google Authenticator.";
        }

        // Jelszó
        if (str_contains($msg, 'password') || str_contains($msg, 'login') || str_contains($msg, 'account')) {
            return "You can manage your account details in the Settings page. If you forgot your password, use the reset link on the login screen.";
        }

        // Köszönet
        if (preg_match('/(thanks|thank you|cool|great|awesome)/', $msg)) {
            return "You're very welcome! Is there anything else I can help you with?";
        }

        // Ha semmit nem ismer fel, de "hogyan" kérdés
        if (str_contains($msg, 'how') || str_contains($msg, 'help') || str_contains($msg, 'can you')) {
            return "I can help you manage your events, setup MFA security, or connect you to a human agent. What would you like to do?";
        }

        // Végső válasz (kicsit barátságosabb)
        return "I'm not sure I quite followed that. Could you please clarify if you're asking about events, security, or if you'd like me to find a human agent for you?";
    }
}
