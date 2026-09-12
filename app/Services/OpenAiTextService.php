<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * OpenAI text completion for non-chatbot features (checklist সারসংক্ষেপ, etc.).
 * Production chatbot stays on GeminiChatService — keep these providers separate.
 */
class OpenAiTextService
{
    /**
     * @param  array{temperature?:float,maxOutputTokens?:int}  $config
     */
    public function complete(string $systemInstruction, string $userPrompt, array $config = []): string
    {
        $this->ensureConfigured();

        $model = (string) config('services.openai.model', 'gpt-4o-mini');
        $payload = [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $systemInstruction],
                ['role' => 'user', 'content' => $userPrompt],
            ],
            'temperature' => (float) ($config['temperature'] ?? 0.2),
            'max_tokens' => (int) ($config['maxOutputTokens'] ?? 900),
        ];

        try {
            $response = Http::acceptJson()
                ->withToken((string) config('services.openai.key'))
                ->timeout(max(5, (int) config('services.openai.timeout', 45)))
                ->retry(2, 300)
                ->post('https://api.openai.com/v1/chat/completions', $payload)
                ->throw();
        } catch (ConnectionException $e) {
            throw new RuntimeException('The AI service could not be reached. Please try again.', previous: $e);
        } catch (RequestException $e) {
            $status = $e->response?->status();
            $message = match ($status) {
                400 => 'OpenAI rejected the request. Check the checklist content and try again.',
                401, 403 => 'The OpenAI API key is invalid or unauthorized.',
                429 => 'OpenAI rate limit reached. Please try again shortly.',
                default => 'OpenAI is temporarily unavailable. Please try again.',
            };
            throw new RuntimeException($message, previous: $e);
        }

        $text = trim((string) data_get($response->json(), 'choices.0.message.content', ''));
        if ($text === '') {
            throw new RuntimeException('OpenAI returned an empty response.');
        }

        return $text;
    }

    public function isConfigured(): bool
    {
        return trim((string) config('services.openai.key')) !== '';
    }

    private function ensureConfigured(): void
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('OpenAI is not configured. Add OPENAI_API_KEY to the server environment.');
        }
    }
}
