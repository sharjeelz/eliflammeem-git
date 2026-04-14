<?php

namespace App\Services;

use App\Models\AiUsageLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiAnalysisService
{
    private const MODEL_VERSION = 'v2';

    private const SYSTEM_PROMPT = <<<'PROMPT'
You are an expert school issue analyst. Parents and teachers submit complaints and feedback
about school-related matters. Your job is to analyse each submission across multiple dimensions
and return structured data that helps school administrators prioritise and respond effectively.

Rules:
- urgency 8-10 → urgency_flag = "escalate"
- urgency 5-7  → urgency_flag = "monitor"
- urgency 1-4  → urgency_flag = "normal"
- themes: 1-3 short noun phrases describing the core topics (e.g. "food quality", "hygiene", "bus delay")
- suggested_category: the most appropriate category from the school's standard categories
- admin_summary: one clear sentence summarising the issue for admin review
- acknowledgment: a warm, proactive 1-2 sentence reply directed at the parent/teacher that:
  (a) acknowledges their specific concern by name, (b) assures them the school team has received
  it and is actively working on it, (c) does not make specific promises about outcomes or timelines.
  Always include "we are looking into this" or equivalent. Never be vague or passive.
- suggested_actions: 2-3 concrete, specific action steps the school admin should take to resolve
  this complaint. Be specific to the issue type — e.g. "Contact the transport provider to
  investigate the reported delay", "Arrange a meeting with the class teacher within 2 working days",
  "Inspect the facility and log a maintenance request with photos". Never use vague steps like
  "look into it", "follow up", or "investigate further". Each step must be actionable by a
  non-technical school administrator.
- submission_type: classify the submission as one of:
  "complaint" — reports a problem, dissatisfaction, or negative experience
  "suggestion" — constructive idea for improvement (not a current problem)
  "compliment" — positive feedback, appreciation, or praise
  Use the parent's intent, not just their tone. A frustrated parent suggesting an improvement
  is a "suggestion". Praise for a teacher is a "compliment". A submission that contains both a
  complaint AND a suggestion should be classified as "suggestion" (constructive intent wins).
- suggested_actions_ur: the same 2-3 action steps as suggested_actions but written in Urdu script
- acknowledgment_ur: the same acknowledgment reply as acknowledgment but written in Urdu script, warm and professional
PROMPT;

    /**
     * JSON schema for OpenAI structured outputs — mirrors FullAnalysisResponse in analysis.py.
     */
    private const RESPONSE_SCHEMA = [
        'type'       => 'json_schema',
        'json_schema' => [
            'name'   => 'IssueAnalysis',
            'strict' => true,
            'schema' => [
                'type'                 => 'object',
                'additionalProperties' => false,
                'required'             => [
                    'sentiment',
                    'sentiment_score',
                    'urgency',
                    'urgency_flag',
                    'themes',
                    'suggested_category',
                    'parent_tone',
                    'admin_summary',
                    'acknowledgment',
                    'acknowledgment_ur',
                    'suggested_actions',
                    'suggested_actions_ur',
                    'submission_type',
                ],
                'properties' => [
                    'sentiment'          => ['type' => 'string', 'enum' => ['positive', 'neutral', 'negative']],
                    'sentiment_score'    => ['type' => 'number', 'minimum' => 0.0, 'maximum' => 1.0],
                    'urgency'            => ['type' => 'integer', 'minimum' => 1, 'maximum' => 10],
                    'urgency_flag'       => ['type' => 'string', 'enum' => ['escalate', 'monitor', 'normal']],
                    'themes'             => ['type' => 'array', 'items' => ['type' => 'string']],
                    'suggested_category' => ['type' => 'string'],
                    'parent_tone'        => ['type' => 'string', 'enum' => ['distressed', 'frustrated', 'concerned', 'calm', 'positive']],
                    'admin_summary'      => ['type' => 'string'],
                    'acknowledgment'     => ['type' => 'string'],
                    'suggested_actions'    => ['type' => 'array', 'items' => ['type' => 'string']],
                    'suggested_actions_ur' => ['type' => 'array', 'items' => ['type' => 'string']],
                    'acknowledgment_ur'    => ['type' => 'string'],
                    'submission_type'      => ['type' => 'string', 'enum' => ['complaint', 'suggestion', 'compliment']],
                ],
            ],
        ],
    ];

    /**
     * Analyse a school issue submission across multiple dimensions.
     * Returns array matching FullAnalysisResponse from analysis.py, or [] on failure.
     */
    public function analyze(string $text, string $title = '', ?string $categoryName = null): array
    {
        $apiKey = config('services.openai.key');

        if (empty($apiKey)) {
            Log::warning('AiAnalysisService: OPENAI_API_KEY not set');
            return [];
        }

        $userContent = implode("\n", array_filter([
            "Issue Title: {$title}",
            'Category: ' . ($categoryName ?? 'Unknown'),
            '',
            'Description:',
            $text,
        ]));

        try {
            $request = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type'  => 'application/json',
            ])->timeout(45);

            if (app()->environment('local')) {
                $request = $request->withOptions(['verify' => false]);
            }

            $response = $request->post('https://api.openai.com/v1/chat/completions', [
                'model'           => config('services.openai.chat_model', 'gpt-4o-mini'),
                'messages'        => [
                    ['role' => 'system', 'content' => self::SYSTEM_PROMPT],
                    ['role' => 'user',   'content' => $userContent],
                ],
                'response_format' => self::RESPONSE_SCHEMA,
                'temperature'     => 0.2,
                'max_tokens'      => 1400,
            ]);
            $data = $response->json();
            if (! $response->successful()) {
                Log::error('AiAnalysisService: OpenAI API error', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return [];
            }

            if (!isset($data['choices'][0]['message']['content'])) {
                throw new \Exception('Invalid response format from OpenAI API');
            }


            $usage = $data['usage'] ?? [];
            AiUsageLog::record(
                callType: 'issue_analysis',
                model: config('services.openai.chat_model', 'gpt-4o-mini'),
                promptTokens: (int) ($usage['prompt_tokens'] ?? 0),
                completionTokens: (int) ($usage['completion_tokens'] ?? 0),
            );

            $content = $data['choices'][0]['message']['content'] ?? '';

            if (empty($content)) {
                return [];
            }

            $data = json_decode($content, true);

            if (! is_array($data) || empty($data)) {
                return [];
            }

            $data['model_version'] = self::MODEL_VERSION;

            return $data;
        } catch (\Throwable $e) {
            Log::error('AiAnalysisService: exception', ['error' => $e->getMessage()]);
            return [];
        }
    }
}
