<?php

namespace App\Services;

use OpenAI\Laravel\Facades\OpenAI;

class OpenAIService
{
    public function generateSuggestion(string $transcript, array $campaignContext): array
    {
        $prompt = app(\App\Services\Prompts\SuggestionPrompt::class)->build($transcript, $campaignContext);

        $response = OpenAI::chat()->create([
            'model' => 'gpt-4o-mini',
            'messages' => [
                ['role' => 'system', 'content' => $prompt['system']],
                ['role' => 'user', 'content' => $prompt['user']],
            ],
            'response_format' => ['type' => 'json_object'],
            'temperature' => 0.7,
        ]);

        $content = $response->choices[0]->message->content;
        $data = json_decode($content, true);

        return [
            'conversation_state' => $data['conversation_state'] ?? 'unknown',
            'recommended_reply' => $data['recommended_reply'] ?? '',
            'flags' => $data['flags'] ?? [],
        ];
    }

    public function summarizeCall(string $transcript, array $campaignContext): array
    {
        $prompt = app(\App\Services\Prompts\SummaryPrompt::class)->build($transcript, $campaignContext);

        $response = OpenAI::chat()->create([
            'model' => 'gpt-4o-mini',
            'messages' => [
                ['role' => 'system', 'content' => $prompt['system']],
                ['role' => 'user', 'content' => $prompt['user']],
            ],
            'response_format' => ['type' => 'json_object'],
            'temperature' => 0.3,
        ]);

        $content = $response->choices[0]->message->content;
        $data = json_decode($content, true);

        return [
            'summary' => $data['summary'] ?? '',
            'outcome' => $data['outcome'] ?? 'other',
            'outcome_confidence' => $data['outcome_confidence'] ?? 0.5,
            'next_action' => $data['next_action'] ?? null,
            'ideal_follow_up_date' => isset($data['ideal_follow_up_date']) ? $data['ideal_follow_up_date'] : null,
        ];
    }
}
