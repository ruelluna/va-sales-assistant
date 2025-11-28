<?php

namespace App\Jobs;

use App\Events\AiSuggestionUpdated;
use App\Models\CallSession;
use App\Services\OpenAIService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class GenerateSuggestionForCallSession implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public CallSession $callSession
    ) {
    }

    public function handle(OpenAIService $openAIService): void
    {
        if (! $this->callSession->isActive()) {
            return;
        }

        $lastSuggestionTime = $this->callSession->ai_state['last_suggestion_at'] ?? null;
        if ($lastSuggestionTime && now()->diffInSeconds($lastSuggestionTime) < 10) {
            return;
        }

        $recentTranscripts = $this->callSession->transcripts()
            ->orderBy('timestamp', 'desc')
            ->limit(20)
            ->get()
            ->reverse();

        if ($recentTranscripts->isEmpty()) {
            return;
        }

        $transcriptText = $recentTranscripts->map(function ($t) {
            $speaker = $t->speaker === 'va' ? 'VA' : 'Prospect';
            return "{$speaker}: {$t->text}";
        })->implode("\n");

        $campaign = $this->callSession->campaign;
        $product = $campaign?->product;

        $campaignContext = [
            'product_name' => $product?->name ?? $campaign?->product_name ?? 'Product',
            'ai_prompt_context' => $campaign?->ai_prompt_context ?? $product?->ai_prompt_context ?? '',
            'success_definition' => $campaign?->success_definition ?? $product?->success_definition ?? '',
            'product_features' => $product?->features ?? [],
            'common_objections' => $product?->common_objections ?? [],
        ];

        try {
            $suggestion = $openAIService->generateSuggestion($transcriptText, $campaignContext);

            $aiState = $this->callSession->ai_state ?? [];
            $aiState['conversation_state'] = $suggestion['conversation_state'];
            $aiState['last_suggestion_at'] = now()->toIso8601String();

            $realTimeTags = $this->callSession->real_time_tags ?? [];
            $realTimeTags = array_unique(array_merge($realTimeTags, $suggestion['flags']));

            $this->callSession->update([
                'ai_state' => $aiState,
                'real_time_tags' => $realTimeTags,
            ]);

            event(new AiSuggestionUpdated(
                $this->callSession,
                $suggestion['conversation_state'],
                $suggestion['recommended_reply'],
                $suggestion['flags']
            ));
        } catch (\Exception $e) {
            Log::error('Failed to generate AI suggestion', [
                'call_session_id' => $this->callSession->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
