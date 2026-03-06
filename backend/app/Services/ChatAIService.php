<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ChatAIService
{
    public function generateResponse(string $message, array $context = []): string
    {
        $lowerMessage = strtolower($message);

        if (str_contains($lowerMessage, 'human') || str_contains($lowerMessage, 'agent') || str_contains($lowerMessage, 'person')) {
            return "I'll connect you with a human agent shortly. Please hold on.";
        }

        if (str_contains($lowerMessage, 'event') && (str_contains($lowerMessage, 'create') || str_contains($lowerMessage, 'add'))) {
            return "To create an event, navigate to the Events section and click the 'Add Event' button. You'll need to provide a title, date & time, and optionally a description.";
        }

        if (str_contains($lowerMessage, 'event') && (str_contains($lowerMessage, 'update') || str_contains($lowerMessage, 'edit'))) {
            return "To update an event, go to your Events list, find the event you want to modify, and click the edit icon. You can change the description, title, or date.";
        }

        if (str_contains($lowerMessage, 'event') && str_contains($lowerMessage, 'delete')) {
            return "To delete an event, navigate to your Events list and click the delete icon next to the event you want to remove. Please note that this action cannot be undone.";
        }

        if (str_contains($lowerMessage, 'password') && str_contains($lowerMessage, 'reset')) {
            return "To reset your password, click on 'Forgot Password' on the login page. Enter your email address and you'll receive a password reset link.";
        }

        if (str_contains($lowerMessage, 'mfa') || str_contains($lowerMessage, '2fa') || str_contains($lowerMessage, 'two-factor')) {
            return "To enable Multi-Factor Authentication (MFA), go to your account settings and select 'Enable MFA'. You'll need to scan a QR code with an authenticator app.";
        }

        if (str_contains($lowerMessage, 'help') || str_contains($lowerMessage, 'how')) {
            return "I'm here to help! You can ask me about creating, updating, or deleting events, password resets, MFA setup, or request to speak with a human agent.";
        }

        return "I'm an AI assistant here to help you. I can answer questions about events, account settings, and more. If you need specific assistance, please let me know, or I can connect you with a human agent.";
    }

    public function shouldTransferToHuman(string $message): bool
    {
        $lowerMessage = strtolower($message);

        return str_contains($lowerMessage, 'human')
            || str_contains($lowerMessage, 'agent')
            || str_contains($lowerMessage, 'person')
            || str_contains($lowerMessage, 'speak to someone');
    }
}
