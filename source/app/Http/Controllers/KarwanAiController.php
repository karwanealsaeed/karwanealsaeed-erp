<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\AiChatMessage;
use App\Services\KarwanAiKnowledgeService;
use App\Services\KarwanAiService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class KarwanAiController extends Controller
{
    public function chat(
        Request $request,
        KarwanAiService $karwanAi,
        KarwanAiKnowledgeService $knowledgeService,
    ): JsonResponse {
        $disabledResponse =
            $this->disabledResponse();

        if ($disabledResponse) {
            return $disabledResponse;
        }

        $messageMaxLength =
            max(
                100,
                min(
                    (int) config(
                        'karwan_ai.message_max_length',
                        2000,
                    ),
                    5000,
                ),
            );

        $validator =
            Validator::make(
                $request->all(),
                [
                    'message' => [
                        'required',
                        'string',
                        'max:'.$messageMaxLength,
                    ],
                ],
                [
                    'message.required' =>
                        'Please enter a message.',

                    'message.max' =>
                        'Message is too long.',
                ],
            );

        if ($validator->fails()) {
            return response()->json(
                [
                    'success' =>
                        false,

                    'code' =>
                        'VALIDATION_ERROR',

                    'message' =>
                        'The submitted message is invalid.',

                    'errors' =>
                        $validator
                            ->errors()
                            ->toArray(),
                ],
                422,
            );
        }

        $data =
            $validator->validated();

        $message =
            trim(
                (string) $data['message'],
            );

        if ($message === '') {
            throw ValidationException::withMessages(
                [
                    'message' =>
                        'Please enter a message.',
                ],
            );
        }

        $sessionToken =
            $this->sessionToken(
                $request,
            );

        $customerId =
            $this->customerId();

        $knowledgeAnswer =
            $knowledgeService->answer(
                $message,
            );

        $answer =
            $knowledgeAnswer
            ?? $karwanAi->answer(
                $message,
            );

        $answerSource =
            $knowledgeAnswer
                ? 'knowledge_base'
                : 'rules';

        if (
            Schema::hasTable(
                'ai_chat_messages',
            )
        ) {
            DB::transaction(
                function () use (
                    $sessionToken,
                    $customerId,
                    $message,
                    $answer,
                    $answerSource,
                ): void {
                    AiChatMessage::query()->create(
                        [
                            'session_token' =>
                                $sessionToken,

                            'customer_id' =>
                                $customerId,

                            'sender' =>
                                'user',

                            'language' =>
                                $answer['language'],

                            'category' =>
                                $answer['category'],

                            'message' =>
                                $message,

                            'needs_human_support' =>
                                false,

                            'meta' => [
                                'source' =>
                                    'user',

                                'version' =>
                                    1,
                            ],
                        ],
                    );

                    AiChatMessage::query()->create(
                        [
                            'session_token' =>
                                $sessionToken,

                            'customer_id' =>
                                $customerId,

                            'sender' =>
                                'assistant',

                            'language' =>
                                $answer['language'],

                            'category' =>
                                $answer['category'],

                            'message' =>
                                $answer['reply'],

                            'needs_human_support' =>
                                (bool) $answer[
                                    'needs_human_support'
                                ],

                            'meta' => [
                                'source' =>
                                    $answerSource,

                                'version' =>
                                    1,
                            ],
                        ],
                    );
                },
            );
        }

        return response()->json(
            [
                'success' =>
                    true,

                'reply' =>
                    $answer['reply'],

                'language' =>
                    $answer['language'],

                'category' =>
                    $answer['category'],

                'needs_human_support' =>
                    (bool) $answer[
                        'needs_human_support'
                    ],

                'source' =>
                    $answerSource,
            ],
        );
    }

    public function history(
        Request $request,
    ): JsonResponse {
        $disabledResponse =
            $this->disabledResponse();

        if ($disabledResponse) {
            return $disabledResponse;
        }

        if (
            ! Schema::hasTable(
                'ai_chat_messages',
            )
        ) {
            return response()->json(
                [
                    'messages' =>
                        [],
                ],
            );
        }

        $historyLimit =
            max(
                1,
                min(
                    (int) config(
                        'karwan_ai.history_limit',
                        30,
                    ),
                    100,
                ),
            );

        $messages =
            $this->ownedMessagesQuery(
                $request,
            )
                ->latest(
                    'id',
                )
                ->limit(
                    $historyLimit,
                )
                ->get()
                ->reverse()
                ->values()
                ->map(
                    static function (
                        AiChatMessage $message,
                    ): array {
                        return [
                            'sender' =>
                                $message->sender,

                            'message' =>
                                $message->message,

                            'created_at' =>
                                $message
                                    ->created_at
                                    ?->format(
                                        'h:i A',
                                    ),
                        ];
                    },
                );

        return response()->json(
            [
                'messages' =>
                    $messages,
            ],
        );
    }

    public function clear(
        Request $request,
    ): JsonResponse {
        $disabledResponse =
            $this->disabledResponse();

        if ($disabledResponse) {
            return $disabledResponse;
        }

        $deletedMessages =
            0;

        if (
            Schema::hasTable(
                'ai_chat_messages',
            )
        ) {
            $deletedMessages =
                $this->ownedMessagesQuery(
                    $request,
                )->delete();
        }

        return response()->json(
            [
                'success' =>
                    true,

                'deleted_messages' =>
                    $deletedMessages,
            ],
        );
    }

    private function ownedMessagesQuery(
        Request $request,
    ): Builder {
        $query =
            AiChatMessage::query()
                ->where(
                    'session_token',
                    $this->sessionToken(
                        $request,
                    ),
                );

        $customerId =
            $this->customerId();

        if ($customerId === null) {
            $query->whereNull(
                'customer_id',
            );
        } else {
            $query->where(
                'customer_id',
                $customerId,
            );
        }

        return $query;
    }

    private function customerId(): ?int
    {
        $customerId =
            session(
                'customer_portal_id',
            );

        if (! is_numeric($customerId)) {
            return null;
        }

        $customerId =
            (int) $customerId;

        if ($customerId < 1) {
            return null;
        }

        return $customerId;
    }

    private function sessionToken(
        Request $request,
    ): string {
        $sessionSeed =
            $request
                ->session()
                ->get(
                    'karwan_ai_session_seed',
                );

        if (
            ! is_string(
                $sessionSeed,
            )
        ) {
            $sessionSeed =
                '';
        }

        if (
            strlen(
                $sessionSeed,
            ) < 32
        ) {
            $sessionSeed =
                Str::random(
                    64,
                );

            $request
                ->session()
                ->put(
                    'karwan_ai_session_seed',
                    $sessionSeed,
                );
        }

        $hmacKey =
            (string) config(
                'karwan_ai.session_hmac_key',
                '',
            );

        if ($hmacKey === '') {
            $hmacKey =
                (string) config(
                    'app.key',
                    '',
                );
        }

        if ($hmacKey === '') {
            return hash(
                'sha256',
                $sessionSeed,
            );
        }

        return hash_hmac(
            'sha256',
            $sessionSeed,
            $hmacKey,
        );
    }

    private function disabledResponse(): ?JsonResponse
    {
        if (
            (bool) config(
                'karwan_ai.enabled',
                true,
            )
        ) {
            return null;
        }

        return response()->json(
            [
                'success' =>
                    false,

                'code' =>
                    'AI_DISABLED',

                'message' =>
                    'Karwan AI is temporarily unavailable.',
            ],
            503,
        );
    }
}
