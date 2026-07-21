<?php

declare(strict_types=1);

namespace App\Services\KarwanAi;

use App\Contracts\KarwanAiProvider;
use App\Services\KarwanAiKnowledgeService;
use App\Services\KarwanAiService;
use Throwable;

class LocalKarwanAiProvider implements KarwanAiProvider
{
    public function __construct(
        private readonly KarwanAiKnowledgeService $knowledgeService,
        private readonly KarwanAiService $rulesService,
    ) {
    }

    public function name(): string
    {
        return 'local';
    }

    public function answer(
        string $message,
    ): array {
        $knowledgeFailure =
            false;

        try {
            $knowledgeAnswer =
                $this->knowledgeService->answer(
                    $message,
                );
        } catch (Throwable $exception) {
            report(
                $exception,
            );

            $knowledgeAnswer =
                null;

            $knowledgeFailure =
                true;
        }

        if ($knowledgeAnswer) {
            return array_merge(
                $knowledgeAnswer,
                [
                    'source' =>
                        'knowledge_base',

                    'fallback_used' =>
                        false,

                    'error_code' =>
                        null,

                    'error_message' =>
                        null,
                ],
            );
        }

        $rulesAnswer =
            $this->rulesService->answer(
                $message,
            );

        return array_merge(
            $rulesAnswer,
            [
                'source' =>
                    'local_rules',

                'fallback_used' =>
                    $knowledgeFailure,

                'error_code' =>
                    $knowledgeFailure
                        ? 'KNOWLEDGE_BASE_FAILED'
                        : null,

                'error_message' =>
                    $knowledgeFailure
                        ? 'Knowledge-base lookup failed; local rules were used.'
                        : null,
            ],
        );
    }
}
