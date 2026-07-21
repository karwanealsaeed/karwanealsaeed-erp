<?php

namespace App\Services;

use App\Models\AiKnowledgeBase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class KarwanAiKnowledgeService
{
    public function answer(string $message): ?array
    {
        if (
            ! Schema::hasTable('ai_knowledge_bases')
            || ! class_exists(AiKnowledgeBase::class)
        ) {
            return null;
        }

        $language = $this->detectLanguage($message);
        $normalizedMessage = $this->normalize($message);

        if ($normalizedMessage === '') {
            return null;
        }

        $records = AiKnowledgeBase::query()
            ->where('status', 'Active')
            ->orderByDesc('priority')
            ->orderBy('id')
            ->get();

        $bestRecord = null;
        $bestScore = 0;

        foreach ($records as $record) {
            $score = $this->score($record, $normalizedMessage);

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestRecord = $record;
            }
        }

        if (! $bestRecord || $bestScore <= 0) {
            return null;
        }

        $reply = $this->localizedAnswer($bestRecord, $language);

        if (blank($reply)) {
            return null;
        }

        return [
            'reply' => $reply,
            'language' => $language,
            'category' => $bestRecord->category ?: 'Knowledge Base',
            'needs_human_support' => false,
        ];
    }

    private function score(
        AiKnowledgeBase $record,
        string $normalizedMessage
    ): int {
        $score = 0;

        $title = $this->normalize((string) $record->title);
        $category = $this->normalize((string) $record->category);

        if ($title !== '' && Str::contains($normalizedMessage, $title)) {
            $score += 8;
        }

        if ($category !== '' && Str::contains($normalizedMessage, $category)) {
            $score += 3;
        }

        $keywords = preg_split(
            '/[\r\n,;|]+/u',
            (string) $record->keywords,
            -1,
            PREG_SPLIT_NO_EMPTY
        ) ?: [];

        foreach ($keywords as $keyword) {
            $normalizedKeyword = $this->normalize($keyword);

            if ($normalizedKeyword === '') {
                continue;
            }

            if (Str::contains($normalizedMessage, $normalizedKeyword)) {
                $wordCount = count(
                    preg_split('/\s+/u', $normalizedKeyword, -1, PREG_SPLIT_NO_EMPTY)
                    ?: []
                );

                $score += max(2, $wordCount * 2);
            }
        }

        if ($score <= 0) {
            return 0;
        }

        $score +=
            min(
                10,
                max(
                    0,
                    (int) $record->priority,
                ),
            );

        return $score;
    }

    private function localizedAnswer(
        AiKnowledgeBase $record,
        string $language
    ): ?string {
        return match ($language) {
            'Urdu' => $record->answer_urdu
                ?: $record->answer_roman_urdu
                ?: $record->answer_english,

            'English' => $record->answer_english
                ?: $record->answer_roman_urdu
                ?: $record->answer_urdu,

            default => $record->answer_roman_urdu
                ?: $record->answer_english
                ?: $record->answer_urdu,
        };
    }

    private function detectLanguage(string $message): string
    {
        if (preg_match('/[\x{0600}-\x{06FF}]/u', $message)) {
            return 'Urdu';
        }

        $lower = Str::lower($message);

        foreach ([
            'what',
            'how',
            'please',
            'need',
            'want',
            'where',
            'when',
            'price',
            'available',
            'information',
            'requirement',
        ] as $term) {
            if (Str::contains($lower, $term)) {
                return 'English';
            }
        }

        return 'Roman Urdu';
    }

    private function normalize(string $value): string
    {
        $value = Str::lower(trim($value));

        $value = preg_replace(
            '/[^\p{L}\p{N}\s]+/u',
            ' ',
            $value
        ) ?? $value;

        return preg_replace('/\s+/u', ' ', trim($value)) ?? trim($value);
    }
}
