<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendSuperAdminChatMessageRequest;
use App\Models\SuperAdminChatMessage;
use App\Services\GeminiChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class SuperAdminChatController extends Controller
{
    public function history(Request $request): JsonResponse
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);
        $thread = trim((string) $request->query('thread_uuid'));
        if (! preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $thread)) {
            return response()->json(['messages' => []]);
        }

        $messages = SuperAdminChatMessage::query()
            ->where('user_id', $request->user()->id)
            ->where('thread_uuid', $thread)
            ->latest('id')
            ->limit(max(5, min(50, (int) config('services.gemini.history_limit', 20))))
            ->get()
            ->reverse()
            ->values()
            ->map(fn (SuperAdminChatMessage $row) => [
                'id' => $row->id,
                'question' => $row->question,
                'answer' => $row->answer,
                'intent' => $row->intent,
                'status' => $row->status,
                'created_at' => $row->created_at?->toIso8601String(),
            ]);

        return response()->json(['messages' => $messages]);
    }

    public function ask(SendSuperAdminChatMessageRequest $request, GeminiChatService $chat): JsonResponse
    {
        $data = $request->validated();
        $user = $request->user();
        $history = SuperAdminChatMessage::query()
            ->where('user_id', $user->id)
            ->where('thread_uuid', $data['thread_uuid'])
            ->where('status', 'answered')
            ->latest('id')
            ->limit(5)
            ->get()
            ->reverse()
            ->map(fn (SuperAdminChatMessage $row) => [
                'question' => (string) $row->question,
                'answer' => (string) $row->answer,
            ])
            ->values()
            ->all();

        try {
            $result = $chat->ask(trim($data['message']), $history);
            $record = SuperAdminChatMessage::query()->create([
                'user_id' => $user->id,
                'thread_uuid' => $data['thread_uuid'],
                'intent' => mb_substr(implode(',', $result['intents']), 0, 80),
                'question' => trim($data['message']),
                'answer' => $result['answer'],
                'status' => 'answered',
            ]);

            return response()->json([
                'id' => $record->id,
                'answer' => $result['answer'],
                'intent' => $result['intent'],
                'intents' => $result['intents'],
                'created_at' => $record->created_at?->toIso8601String(),
            ]);
        } catch (Throwable $e) {
            $message = $this->publicErrorMessage($e);

            Log::warning('Super Admin chatbot request failed.', [
                'user_id' => $user->id,
                'reason' => $e->getMessage(),
            ]);
            if ($message !== $e->getMessage()) {
                report($e);
            }
            SuperAdminChatMessage::query()->create([
                'user_id' => $user->id,
                'thread_uuid' => $data['thread_uuid'],
                'question' => trim($data['message']),
                'answer' => $message,
                'status' => 'failed',
            ]);

            return response()->json(['message' => $message], 503);
        }
    }

    private function publicErrorMessage(Throwable $exception): string
    {
        $message = $exception->getMessage();
        $allowed = [
            'The AI service could not be reached. Please try again.',
            'The Gemini API key or configuration is invalid.',
            'The Gemini request limit has been reached. Please try again shortly.',
            'Gemini is temporarily unavailable. Please try again.',
            'Gemini is not configured. Add GEMINI_API_KEY to the server environment.',
        ];

        return $exception instanceof RuntimeException && in_array($message, $allowed, true)
            ? $message
            : 'The assistant could not complete this request. Please try again.';
    }
}
