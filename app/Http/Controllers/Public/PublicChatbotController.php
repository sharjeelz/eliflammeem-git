<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\ChatbotLog;
use App\Models\School;
use App\Services\ChatbotService;
use App\Services\PlanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PublicChatbotController extends Controller
{
    public function __construct(
        protected ChatbotService $chatbot
    ) {}

    private function chatbotEnabled(): bool
    {
        $school = School::where('tenant_id', tenant('id'))->first();
        return (bool) $school?->setting('chatbot_enabled', false);
    }

    private function schoolInactive(): bool
    {
        $school = School::where('tenant_id', tenant('id'))->first();
        return $school?->status === 'inactive';
    }

    /**
     * Show the public Q&A chatbot page.
     */
    public function index()
    {
        if ($this->schoolInactive()) {
            return redirect('/')->with('error', 'The chatbot is not available while the school portal is suspended.');
        }

        if (! PlanService::forCurrentTenant()->allows('chatbot')) {
            return view('tenant.public.chatbot_disabled');
        }

        if (! $this->chatbotEnabled()) {
            return view('tenant.public.chatbot_disabled');
        }

        $downloads = \App\Models\Document::where('allow_public_download', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderBy('title')
            ->get(['id', 'title', 'path', 'size', 'mime']);

        // Pre-generate signed URLs (24h expiry)
        $downloads = $downloads->map(function ($doc) {
            $doc->signed_url = \Illuminate\Support\Facades\URL::signedRoute(
                'tenant.public.document.download',
                ['document' => $doc->id],
                now()->addHours(24)
            );
            $doc->ext = strtoupper(pathinfo($doc->path, PATHINFO_EXTENSION));
            return $doc;
        });

        return view('tenant.public.chatbot', compact('downloads'));
    }

    /**
     * Answer a single question — stateless, no auth required.
     */
    public function ask(Request $request)
    {
        $request->validate([
            'question' => 'required|string|max:1000',
        ]);

        if ($this->schoolInactive()) {
            return response()->json(['success' => false, 'message' => 'School portal suspended.'], 503);
        }

        $plan = PlanService::forCurrentTenant();

        if (! $plan->allows('chatbot')) {
            return response()->json(['success' => false, 'message' => 'Chatbot is not available on this school\'s plan.'], 403);
        }

        // Daily chatbot limit — atomic Redis counter prevents race-condition bypass
        $dailyLimit = $plan->chatbotDailyLimit();
        if ($dailyLimit !== null && $dailyLimit > 0) {
            $cacheKey = 'chatbot_daily:' . tenant('id') . ':' . today()->toDateString();
            $ttl      = now()->secondsUntilEndOfDay() + 60; // expire just after midnight

            $used = Cache::increment($cacheKey);
            if ($used === 1) {
                // First increment — set TTL (increment creates the key without TTL)
                Cache::put($cacheKey, $used, $ttl);
            }

            if ($used > $dailyLimit) {
                return response()->json([
                    'success' => false,
                    'message' => "Daily chatbot question limit ({$dailyLimit}) has been reached. Please try again tomorrow.",
                ], 429);
            }
        }

        if (! $this->chatbotEnabled()) {
            return response()->json(['success' => false, 'message' => 'Chatbot is currently unavailable.'], 403);
        }

        $question = $request->input('question');

        // Fast-path injection check before hitting the LLM (saves API cost)
        if ($this->chatbot->detectsInjection($this->chatbot->sanitizeInput($question))) {
            Log::warning('Chatbot: injection attempt on public endpoint', [
                'ip'       => $request->ip(),
                'question' => $question,
            ]);
            return response()->json([
                'success' => false,
                'message' => "I'm only able to help with school-related questions.",
            ], 400);
        }

        try {
            $result = $this->chatbot->answerQuestion($question);

            return response()->json([
                'success'     => true,
                'answer'      => $result['answer'],
                'confidence'  => $result['confidence'],
                'attachments' => $result['attachments'] ?? [],
            ]);
        } catch (\Exception $e) {
            Log::error('Public chatbot failed', [
                'question' => $request->input('question'),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to answer your question right now. Please contact the school directly.',
            ], 500);
        }
    }
}
