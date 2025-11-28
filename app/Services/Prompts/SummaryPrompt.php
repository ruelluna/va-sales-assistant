<?php

namespace App\Services\Prompts;

class SummaryPrompt
{
    public function build(string $transcript, array $campaignContext): array
    {
        $systemMessage = "You are analyzing a completed sales call for quality, outcome, and next actions.\n\n";
        $systemMessage .= "Based on the full transcript and campaign details, you will:\n";
        $systemMessage .= "1. Summarize the call in 2-3 sentences.\n";
        $systemMessage .= "2. Decide the outcome label.\n";
        $systemMessage .= "3. Decide if follow-up is required and when.\n\n";
        $systemMessage .= "Always respond in JSON only.";

        $userMessage = "Full transcript:\n{$transcript}\n\n";
        $userMessage .= "Campaign context:\n";
        $userMessage .= "Product/Offer: {$campaignContext['product_name']}\n";
        if (! empty($campaignContext['ai_prompt_context'])) {
            $userMessage .= "Context: {$campaignContext['ai_prompt_context']}\n";
        }
        if (! empty($campaignContext['success_definition'])) {
            $userMessage .= "Success definition: {$campaignContext['success_definition']}\n";
        }

        $userMessage .= "\nValid outcomes: sale_won, appointment_booked, qualified_lead, not_interested, busy_callback, voicemail, no_answer, other\n\n";
        $userMessage .= "Provide your analysis in this JSON format:\n";
        $userMessage .= '{"summary": "The VA introduced...", "outcome": "appointment_booked", "outcome_confidence": 0.92, "next_action": "Send confirmation email", "ideal_follow_up_date": "2025-12-05T15:00:00Z"}';

        return [
            'system' => $systemMessage,
            'user' => $userMessage,
        ];
    }
}
