<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    private $geminiApiKey;
    private $geminiApiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent';

    public function __construct()
    {
        $this->geminiApiKey = config('services.gemini.api_key');
    }

    /**
     * Get or create a chat session
     */
    public function getOrCreateChat(): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }

            // Get the most recent chat or create new one
            $chat = Chat::where('user_id', $user->id)
                ->latest()
                ->first();

            if (!$chat) {
                $chat = Chat::create([
                    'user_id' => $user->id,
                    'topic' => 'general',
                ]);
            }

            return response()->json([
                'chat_id' => $chat->id,
                'messages' => $chat->messages()->get()->toArray(),
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Chat Init Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'error' => 'Failed to initialize chat: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send a message and get AI response
     */
    public function sendMessage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'chat_id' => 'required|exists:chats,id',
            'message' => 'required|string|max:1000',
        ]);

        $chat = Chat::findOrFail($validated['chat_id']);

        // Verify ownership
        if ($chat->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Store user message
        ChatMessage::create([
            'chat_id' => $chat->id,
            'role' => 'user',
            'content' => $validated['message'],
        ]);

        try {
            // Get conversation history for context
            $conversationHistory = $chat->messages()
                ->latest()
                ->limit(10)
                ->get()
                ->reverse();

            // Build context for Gemini
            $systemPrompt = $this->buildSystemPrompt($chat);
            $messages = $this->buildMessagesForGemini($conversationHistory, $systemPrompt);

            // Call Gemini API with SSL verification disabled for Laragon
            $response = Http::timeout(30)
                ->withoutVerifying()
                ->post($this->geminiApiUrl . '?key=' . $this->geminiApiKey, [
                    'contents' => $messages,
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'topK' => 40,
                        'topP' => 0.95,
                        'maxOutputTokens' => 500,
                    ],
                    'safetySettings' => [
                        [
                            'category' => 'HARM_CATEGORY_HARASSMENT',
                            'threshold' => 'BLOCK_NONE',
                        ],
                        [
                            'category' => 'HARM_CATEGORY_HATE_SPEECH',
                            'threshold' => 'BLOCK_NONE',
                        ],
                        [
                            'category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT',
                            'threshold' => 'BLOCK_NONE',
                        ],
                        [
                            'category' => 'HARM_CATEGORY_DANGEROUS_CONTENT',
                            'threshold' => 'BLOCK_NONE',
                        ],
                    ],
                ]);

            if (!$response->successful()) {
                // Log the full response for debugging
                \Log::error('Gemini API Error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'headers' => $response->headers(),
                ]);
                
                // Provide user-friendly error messages
                $errorMessage = match($response->status()) {
                    429 => 'API quota exceeded for today. The free tier has limited requests. Please try again tomorrow or upgrade your API plan.',
                    401 => 'API key is invalid or expired.',
                    403 => 'Access forbidden. Check your API key permissions.',
                    default => 'Failed to get AI response (Status: ' . $response->status() . ')',
                };
                
                return response()->json([
                    'error' => $errorMessage,
                    'details' => $response->body()
                ], 500);
            }

            $aiResponse = $this->parseGeminiResponse($response->json());

            if (!$aiResponse) {
                return response()->json([
                    'error' => 'Invalid response from AI'
                ], 500);
            }

            // Store AI response
            ChatMessage::create([
                'chat_id' => $chat->id,
                'role' => 'assistant',
                'content' => $aiResponse,
            ]);

            return response()->json([
                'success' => true,
                'message' => $aiResponse,
                'chat_id' => $chat->id,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error processing message: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get chat history
     */
    public function getMessages($chatId): JsonResponse
    {
        $chat = Chat::findOrFail($chatId);

        if ($chat->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json($chat->messages);
    }

    /**
     * Create a new chat
     */
    public function createChat(): JsonResponse
    {
        $chat = Chat::create([
            'user_id' => Auth::id(),
            'topic' => 'general',
        ]);

        return response()->json([
            'chat_id' => $chat->id,
            'messages' => [],
        ]);
    }

    /**
     * Build system prompt with context about SmartShopper
     */
    private function buildSystemPrompt(Chat $chat): string
    {
        $user = $chat->user;
        
        return <<<PROMPT
You are a helpful shopping assistant for SmartShopper, a Malaysian price comparison platform.

User Information:
- Name: {$user->name}
- Email: {$user->email}

Your role is to:
1. Help users find products and compare prices across retailers (Mydin, Shopee)
2. Provide budget planning and spending advice
3. Answer questions about shopping lists and budgets
4. Give product recommendations based on user needs
5. Help users save money by finding better deals

Be friendly, concise, and helpful. Always encourage users to check the SmartShopper marketplace for the latest prices.
PROMPT;
    }

    /**
     * Build messages array for Gemini API
     */
    private function buildMessagesForGemini($conversationHistory, $systemPrompt): array
    {
        $messages = [
            [
                'role' => 'user',
                'parts' => [['text' => $systemPrompt]],
            ],
            [
                'role' => 'model',
                'parts' => [['text' => 'I understand. I\'m SmartShopper\'s AI assistant and will help you with shopping, price comparisons, and budget planning.']],
            ],
        ];

        foreach ($conversationHistory as $msg) {
            $messages[] = [
                'role' => $msg->role === 'user' ? 'user' : 'model',
                'parts' => [['text' => $msg->content]],
            ];
        }

        return $messages;
    }

    /**
     * Parse Gemini API response
     */
    private function parseGeminiResponse($response): ?string
    {
        if (!isset($response['candidates']) || empty($response['candidates'])) {
            return null;
        }

        $candidate = $response['candidates'][0];

        if (!isset($candidate['content']['parts']) || empty($candidate['content']['parts'])) {
            return null;
        }

        return $candidate['content']['parts'][0]['text'] ?? null;
    }

    /**
     * Delete a chat
     */
    public function deleteChat($chatId): JsonResponse
    {
        $chat = Chat::findOrFail($chatId);

        if ($chat->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $chat->delete();

        return response()->json(['success' => true]);
    }
}
