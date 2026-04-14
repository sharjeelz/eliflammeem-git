<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SavedFilter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SavedFilterController extends Controller
{
    private const FILTER_KEYS = [
        'search', 'status', 'priority', 'assigned_user_id',
        'branch_id', 'category_id', 'from', 'to',
        'urgency', 'theme', 'sentiment', 'submission_type', 'spam',
    ];

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:60'],
        ]);

        $queryParams = array_filter(
            $request->only(self::FILTER_KEYS),
            fn ($v) => $v !== '' && $v !== null
        );

        if (empty($queryParams)) {
            return response()->json(['error' => 'No active filters to save.'], 422);
        }

        $count = SavedFilter::where('tenant_id', tenant('id'))
            ->where('user_id', auth()->id())
            ->count();

        if ($count >= 20) {
            return response()->json(['error' => 'Maximum of 20 saved filters reached.'], 422);
        }

        $filter = SavedFilter::create([
            'tenant_id'   => tenant('id'),
            'user_id'     => auth()->id(),
            'name'        => $data['name'],
            'query_params' => $queryParams,
        ]);

        return response()->json([
            'id'           => $filter->id,
            'name'         => $filter->name,
            'query_params' => $filter->query_params,
        ]);
    }

    public function destroy(SavedFilter $savedFilter): JsonResponse
    {
        abort_unless(
            $savedFilter->user_id === auth()->id() && $savedFilter->tenant_id === tenant('id'),
            403
        );

        $savedFilter->delete();

        return response()->json(['ok' => true]);
    }
}
