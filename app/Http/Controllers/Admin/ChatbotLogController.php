<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatbotLog;
use Illuminate\Http\Request;

class ChatbotLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ChatbotLog::query()->orderByDesc('created_at');

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->input('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->input('to'));
        }
        if ($request->boolean('no_answer')) {
            $query->where('chunks_found', 0);
        }
        if ($request->filled('confidence')) {
            match ($request->input('confidence')) {
                'high'   => $query->where('confidence', '>=', 0.8),
                'medium' => $query->whereBetween('confidence', [0.5, 0.8]),
                'low'    => $query->where('chunks_found', '>', 0)->where('confidence', '<', 0.5),
                default  => null,
            };
        }
        if ($request->filled('q')) {
            $query->where('question', 'ilike', '%' . $request->input('q') . '%');
        }

        $logs = $query->paginate(25)->withQueryString();

        // KPI aggregates (always across full tenant scope, ignoring current filters)
        $base = ChatbotLog::query();
        $stats = [
            'total_today'   => (clone $base)->whereDate('created_at', today())->count(),
            'total_week'    => (clone $base)->where('created_at', '>=', now()->subDays(7))->count(),
            'total_month'   => (clone $base)->where('created_at', '>=', now()->subDays(30))->count(),
            'no_answer_pct' => null,
            'avg_confidence'=> null,
            'avg_response_ms' => null,
        ];

        $monthTotal = $stats['total_month'];
        if ($monthTotal > 0) {
            $noAnswer = (clone $base)->where('created_at', '>=', now()->subDays(30))->where('chunks_found', 0)->count();
            $stats['no_answer_pct'] = round($noAnswer / $monthTotal * 100, 1);

            $stats['avg_confidence'] = round(
                (clone $base)->where('created_at', '>=', now()->subDays(30))->where('chunks_found', '>', 0)->avg('confidence') * 100,
                1
            );

            $stats['avg_response_ms'] = (int) round(
                (clone $base)->where('created_at', '>=', now()->subDays(30))->avg('response_ms')
            );
        }

        return view('tenant.admin.chatbot.index', compact('logs', 'stats'));
    }
}
