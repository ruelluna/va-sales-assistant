<?php

namespace App\Services\Prompts;

class SuggestionPrompt
{
    public function build(string $transcript, array $campaignContext): array
    {
        $systemMessage = "You are a sales assistant helping a human virtual assistant who is calling prospects about {$campaignContext['product_name']}.\n\n";
        $systemMessage .= "Your job is to:\n";
        $systemMessage .= "1. Analyze the recent call transcript.\n";
        $systemMessage .= "2. Identify the current conversation state.\n";
        $systemMessage .= "3. Suggest a short, natural reply the VA can say next.\n\n";
        $systemMessage .= "Always respond in JSON only. Keep the suggested reply concise and speakable.\n\n";
        $systemMessage .= "Valid conversation states: greeting, rapport, pitch, objection, busy, follow_up_request, closing, wrap_up\n";
        $systemMessage .= "Valid flags: interested, not_interested, price_objection, time_objection, needs_callback, gatekeeper";

        $userMessage = "Recent transcript:\n{$transcript}\n\n";
        $userMessage .= "Campaign context:\n";
        $userMessage .= "Product/Offer: {$campaignContext['product_name']}\n";
        if (! empty($campaignContext['ai_prompt_context'])) {
            $userMessage .= "Context: {$campaignContext['ai_prompt_context']}\n";
        }
        if (! empty($campaignContext['success_definition'])) {
            $userMessage .= "Success definition: {$campaignContext['success_definition']}\n";
        }

        $userMessage .= "\nProvide your analysis in this JSON format:\n";
        $userMessage .= '{"conversation_state": "objection", "recommended_reply": "I understand your concern...", "flags": ["price_objection", "potentially_interested"]}';

        return [
            'system' => $systemMessage,
            'user' => $userMessage,
        ];
    }
}
