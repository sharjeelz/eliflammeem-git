<?php

namespace App\Http\Controllers\Api\V1\Parent;

use App\Http\Controllers\Controller;
use App\Models\AccessCode;
use App\Models\Issue;
use App\Models\IssueCategory;
use App\Services\IssueActionService;
use App\Services\IssueSubmissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ParentIssueController extends Controller
{
    public function __construct(
        private readonly IssueSubmissionService $submissionService,
        private readonly IssueActionService $actionService,
    ) {}

    /**
     * GET /api/v1/parent/categories
     */
    public function categories(): JsonResponse
    {
        $categories = IssueCategory::where('tenant_id', tenant('id'))
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json(['data' => $categories]);
    }

    /**
     * GET /api/v1/parent/issues
     */
    public function index(Request $request): JsonResponse
    {
        $contact = $request->user();

        $issues = Issue::where('tenant_id', tenant('id'))
            ->where('roster_contact_id', $contact->id)
            ->where('is_spam', false)
            ->with(['branch:id,name', 'issueCategory:id,name'])
            ->latest()
            ->paginate(20);

        return response()->json([
            'data' => $issues->map(fn ($issue) => $this->issueListResource($issue)),
            'meta' => [
                'current_page' => $issues->currentPage(),
                'last_page'    => $issues->lastPage(),
                'per_page'     => $issues->perPage(),
                'total'        => $issues->total(),
            ],
        ]);
    }

    /**
     * POST /api/v1/parent/issues
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'description'   => ['required', 'string', 'min:10', 'max:8000'],
            'category_id'   => ['nullable', 'integer', 'exists:issue_categories,id'],
            'attachments'   => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'max:4096', 'mimes:jpg,jpeg,png,pdf,doc,docx'],
        ]);

        $contact = $request->user();

        $code = AccessCode::where('tenant_id', tenant('id'))
            ->where('roster_contact_id', $contact->id)
            ->active()
            ->first();

        if (! $code) {
            // Try to find any code (used) — the contact may already have an open issue
            $hasOpen = Issue::where('tenant_id', tenant('id'))
                ->where('roster_contact_id', $contact->id)
                ->where('status', '!=', 'closed')
                ->exists();

            if ($hasOpen) {
                return response()->json([
                    'error'   => 'open_issue_conflict',
                    'message' => 'You already have an open issue. Please wait for it to be resolved or close it first.',
                ], 422);
            }

            return response()->json([
                'error'   => 'no_active_code',
                'message' => 'No active access code found. Please contact the school.',
            ], 422);
        }

        $result = $this->submissionService->submit(
            contact: $contact,
            code: $code,
            data: $request->only('description', 'category_id'),
            files: $request->file('attachments', []),
            portalBaseUrl: $request->getSchemeAndHttpHost(),
        );

        if ($result['error'] === 'open_issue_conflict') {
            return response()->json([
                'error'   => 'open_issue_conflict',
                'message' => 'You already have an open issue. Please wait for it to be resolved or close it first.',
            ], 422);
        }

        if ($result['error'] === 'access_revoked') {
            return response()->json([
                'error'   => 'access_revoked',
                'message' => 'Your access has been revoked due to repeated violations.',
            ], 403);
        }

        if ($result['error']) {
            return response()->json([
                'error'   => 'submission_failed',
                'message' => $result['error'],
            ], 422);
        }

        $issue = $result['issue'];

        return response()->json([
            'data'    => $this->issueDetailResource($issue->load(['branch', 'issueCategory', 'attachments'])),
            'message' => 'Issue submitted successfully. Tracking ID: ' . $issue->public_id,
        ], 201);
    }

    /**
     * GET /api/v1/parent/issues/{public_id}
     */
    public function show(Request $request, string $public_id): JsonResponse
    {
        $contact = $request->user();

        $issue = Issue::where('tenant_id', tenant('id'))
            ->where('public_id', $public_id)
            ->where('roster_contact_id', $contact->id)
            ->with([
                'branch:id,name',
                'issueCategory:id,name',
                'attachments' => fn ($q) => $q->whereNull('issue_message_id'),
                'messages'    => fn ($q) => $q
                    ->where('is_internal', false)
                    ->with(['attachments', 'author'])
                    ->orderBy('created_at'),
            ])
            ->first();

        if (! $issue) {
            return response()->json(['error' => 'not_found', 'message' => 'Issue not found.'], 404);
        }

        return response()->json(['data' => $this->issueDetailResource($issue)]);
    }

    /**
     * POST /api/v1/parent/issues/{public_id}/reply
     */
    public function reply(Request $request, string $public_id): JsonResponse
    {
        $request->validate(['message' => ['required', 'string', 'max:3000']]);

        $contact = $request->user();
        $issue   = $this->findOwnedIssue($public_id, $contact->id);

        if (! $issue) {
            return response()->json(['error' => 'not_found', 'message' => 'Issue not found.'], 404);
        }

        try {
            $msg = $this->actionService->addReply($issue, $contact, $request->message);
        } catch (\RuntimeException $e) {
            return response()->json(['error' => 'action_failed', 'message' => $e->getMessage()], 422);
        }

        return response()->json([
            'data'    => $this->messageResource($msg),
            'message' => 'Reply sent.',
        ], 201);
    }

    /**
     * POST /api/v1/parent/issues/{public_id}/close
     */
    public function close(Request $request, string $public_id): JsonResponse
    {
        $request->validate([
            'close_reason' => ['required', 'string', Rule::in(array_keys(IssueActionService::CONTACT_CLOSE_REASONS))],
        ]);

        $contact = $request->user();
        $issue   = $this->findOwnedIssue($public_id, $contact->id);

        if (! $issue) {
            return response()->json(['error' => 'not_found', 'message' => 'Issue not found.'], 404);
        }

        try {
            $issue = $this->actionService->close(
                issue: $issue,
                contact: $contact,
                reason: $request->close_reason,
                portalBaseUrl: $request->getSchemeAndHttpHost(),
            );
        } catch (\Exception $e) {
            return response()->json(['error' => 'action_failed', 'message' => $e->getMessage()], 422);
        }

        return response()->json([
            'data'    => ['status' => $issue->status],
            'message' => 'Issue closed. You can now submit a new one.',
        ]);
    }

    /**
     * POST /api/v1/parent/issues/{public_id}/reopen
     */
    public function reopen(Request $request, string $public_id): JsonResponse
    {
        $contact = $request->user();
        $issue   = $this->findOwnedIssue($public_id, $contact->id);

        if (! $issue) {
            return response()->json(['error' => 'not_found', 'message' => 'Issue not found.'], 404);
        }

        try {
            $issue = $this->actionService->reopen($issue, $contact);
        } catch (\RuntimeException $e) {
            return response()->json(['error' => 'action_failed', 'message' => $e->getMessage()], 422);
        }

        return response()->json([
            'data'    => ['status' => $issue->status],
            'message' => 'Issue reopened. The team has been notified.',
        ]);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function findOwnedIssue(string $publicId, int $contactId): ?Issue
    {
        return Issue::where('tenant_id', tenant('id'))
            ->where('public_id', $publicId)
            ->where('roster_contact_id', $contactId)
            ->first();
    }

    private function issueListResource(Issue $issue): array
    {
        return [
            'public_id'        => $issue->public_id,
            'title'            => $issue->title,
            'status'           => $issue->status,
            'priority'         => $issue->priority,
            'category'         => $issue->issueCategory?->name,
            'branch'           => $issue->branch?->name,
            'created_at'       => $issue->created_at?->toIso8601String(),
            'last_activity_at' => $issue->last_activity_at?->toIso8601String(),
        ];
    }

    private function issueDetailResource(Issue $issue): array
    {
        return [
            'public_id'        => $issue->public_id,
            'title'            => $issue->title,
            'description'      => $issue->description,
            'status'           => $issue->status,
            'priority'         => $issue->priority,
            'is_spam'          => $issue->is_spam,
            'category'         => $issue->issueCategory?->name,
            'branch'           => $issue->branch?->name,
            'created_at'       => $issue->created_at?->toIso8601String(),
            'last_activity_at' => $issue->last_activity_at?->toIso8601String(),
            'attachments'      => $issue->attachments->map(fn ($a) => [
                'id'       => $a->id,
                'url'      => $a->storage_url,
                'mime'     => $a->mime,
                'size'     => $a->size,
            ]),
            'messages' => $issue->relationLoaded('messages')
                ? $issue->messages->map(fn ($m) => $this->messageResource($m))
                : [],
        ];
    }

    private function messageResource($msg): array
    {
        return [
            'id'           => $msg->id,
            'sender'       => $msg->sender,
            'sender_label' => $msg->sender_label,
            'message'      => $msg->message,
            'created_at'   => $msg->created_at?->toIso8601String(),
        ];
    }
}
