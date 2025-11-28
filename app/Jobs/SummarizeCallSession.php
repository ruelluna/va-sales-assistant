<?php

namespace App\Jobs;

use App\Models\CallSession;
use App\Services\OpenAIService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SummarizeCallSession implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public CallSession $callSession
    ) {
    }

    public function handle(OpenAIService $openAIService): void
    {
        $transcripts = $this->callSession->transcripts()
            ->orderBy('timestamp', 'asc')
            ->get();

        if ($transcripts->isEmpty()) {
            return;
        }

        $transcriptText = $transcripts->map(function ($t) {
            $speaker = $t->speaker === 'va' ? 'VA' : 'Prospect';
            return "{$speaker}: {$t->text}";
        })->implode("\n");

        $campaign = $this->callSession->campaign;
        $product = $campaign?->product;

        $campaignContext = [
            'product_name' => $product?->name ?? $campaign?->product_name ?? 'Product',
            'ai_prompt_context' => $campaign?->ai_prompt_context ?? $product?->ai_prompt_context ?? '',
            'success_definition' => $campaign?->success_definition ?? $product?->success_definition ?? '',
        ];

        try {
            $summary = $openAIService->summarizeCall($transcriptText, $campaignContext);

            $updateData = [
                'summary' => $summary['summary'],
                'outcome' => $summary['outcome'],
                'outcome_confidence' => $summary['outcome_confidence'],
                'next_action' => $summary['next_action'],
            ];

            if ($summary['ideal_follow_up_date']) {
                $updateData['next_action_due_at'] = \Carbon\Carbon::parse($summary['ideal_follow_up_date']);
            }

            $this->callSession->update($updateData);
        } catch (\Exception $e) {
            Log::error('Failed to summarize call session', [
                'call_session_id' => $this->callSession->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
