<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\AgentConversation;
use App\Models\AgentConversationMessage;
use App\Services\ChatbotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ChatbotController extends Controller
{
    public function __construct(
        protected ChatbotService $chatbot
    ) {}

    /**
     * Display the chatbot interface.
     */
    public function index()
    {
        $conversations = AgentConversation::query()
            ->where('user_id', auth()->id())
            ->latest()
            ->take(10)
            ->get();

        return view('tenant.parent.chatbot.index', [
            'conversations' => $conversations,
        ]);
    }

    /**
     * Create a new conversation.
     */
    public function createConversation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $conversation = $this->chatbot->getOrCreateConversation(
                auth()->id(),
                $request->input('title', 'New Conversation')
            );

            return response()->json([
                'success' => true,
                'conversation' => [
                    'id' => $conversation->id,
                    'title' => $conversation->title,
                    'created_at' => $conversation->created_at->toISOString(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create conversation', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create conversation. Please try again.',
            ], 500);
        }
    }

    /**
     * Get conversation history.
     */
    public function getConversation(Request $request, string $conversationId)
    {
        $conversation = AgentConversation::query()
            ->where('id', $conversationId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $messages = AgentConversationMessage::query()
            ->where('conversation_id', $conversationId)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($message) {
                return [
                    'id' => $message->id,
                    'role' => $message->role,
                    'content' => $message->content,
                    'confidence' => $message->confidence_score,
                    'created_at' => $message->created_at->toISOString(),
                    'sources' => $message->isAssistant() ? $message->getSourceDocuments()->map(function ($doc) {
                        return [
                            'id' => $doc->id,
                            'title' => $doc->title,
                            'category' => $doc->category->name ?? 'Uncategorized',
                        ];
                    }) : [],
                ];
            });

        return response()->json([
            'success' => true,
            'conversation' => [
                'id' => $conversation->id,
                'title' => $conversation->title,
                'created_at' => $conversation->created_at->toISOString(),
            ],
            'messages' => $messages,
        ]);
    }

    /**
     * Send a message and get a response.
     */
    public function sendMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'conversation_id' => [
                'required', 'string',
                Rule::exists('agent_conversations', 'id')
                    ->where('tenant_id', tenant('id'))
                    ->where('user_id', auth()->id()),
            ],
            'message' => 'required|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Verify conversation belongs to user
        $conversation = AgentConversation::query()
            ->where('id', $request->input('conversation_id'))
            ->where('user_id', auth()->id())
            ->firstOrFail();

        try {
            $response = $this->chatbot->processUserMessage(
                $request->input('conversation_id'),
                $request->input('message')
            );

            return response()->json([
                'success' => true,
                'response' => [
                    'answer' => $response['answer'],
                    'confidence' => $response['confidence'],
                    'reasoning' => $response['reasoning'],
                    'sources' => $response['sources'],
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to process chatbot message', [
                'error' => $e->getMessage(),
                'conversation_id' => $request->input('conversation_id'),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process your message. Please try again.',
            ], 500);
        }
    }

    /**
     * Delete a conversation.
     */
    public function deleteConversation(Request $request, string $conversationId)
    {
        $conversation = AgentConversation::query()
            ->where('id', $conversationId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        try {
            // Delete all messages first
            AgentConversationMessage::query()
                ->where('conversation_id', $conversationId)
                ->delete();

            // Delete conversation
            $conversation->delete();

            return response()->json([
                'success' => true,
                'message' => 'Conversation deleted successfully.',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to delete conversation', [
                'error' => $e->getMessage(),
                'conversation_id' => $conversationId,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete conversation.',
            ], 500);
        }
    }
}
