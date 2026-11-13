<?php

namespace App\Services;

use App\Models\BadWord;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class CommentModerationService
{
    protected const CACHE_BAD_WORDS_KEY = 'moderation_active_bad_words_v4';

    protected const CACHE_TTL = 86400; // 24 jam

    /**
     * Cache in-memory selama lifecycle request berlangsung.
     *
     * @var array<array{word: string, severity: string}>|null
     */
    protected static ?array $runtimeWords = null;

    /**
     * Jalankan moderasi lengkap pada teks komentar.
     *
     * Alur: normalisasi teks -> deteksi kata terlarang -> hasil moderasi.
     *
     * @return array{
     *     detected: bool,
     *     words: array<string>,
     *     severity: string,
     *     censored_text: string,
     *     match_count: int
     * }
     */
    public function moderate(?string $text): array
    {
        if (empty($text) || trim($text) === '') {
            return [
                'detected' => false,
                'words' => [],
                'severity' => 'none',
                'censored_text' => '',
                'match_count' => 0,
            ];
        }

        [$normalizedText, $originalStartByByte, $originalEndByByte] = $this->normalizeWithOffsets($text);

        // Daftar kata terlarang tidak dapat dilewati oleh whitelist.
        $badWordsData = $this->getActiveBadWords();

        if (empty($badWordsData)) {
            return [
                'detected' => false,
                'words' => [],
                'severity' => 'none',
                'censored_text' => $text,
                'match_count' => 0,
            ];
        }

        // Teks sementara untuk pemrosesan regex
        $censoredText = $text;
        $matchedOriginalRanges = [];
        $detectedWords = [];
        $highestSeverityScore = 0; // 1: low, 2: medium, 3: high
        $matchCount = 0;

        $maskedText = $normalizedText;

        foreach ($badWordsData as $item) {
            $word = is_array($item) ? ($item['word'] ?? '') : (string) $item;
            $severityStr = is_array($item) ? ($item['severity'] ?? 'medium') : 'medium';
            $cleanWord = trim(strtolower($word));

            if (empty($cleanWord)) {
                continue;
            }

            $pattern = $this->buildRegexPattern($cleanWord);

            if (preg_match_all($pattern, $maskedText, $matches, PREG_OFFSET_CAPTURE)) {
                $detectedWords[] = $cleanWord;
                $currentMatchCount = count($matches[0]);
                $matchCount += $currentMatchCount;

                // Hitung severity score
                $severityScore = match (strtolower($severityStr)) {
                    'high' => 3,
                    'low' => 1,
                    default => 2,
                };
                if ($severityScore > $highestSeverityScore) {
                    $highestSeverityScore = $severityScore;
                }

                foreach ($matches[0] as [$matchedString, $normalizedOffset]) {
                    $normalizedEnd = $normalizedOffset + strlen($matchedString) - 1;
                    if (! isset($originalStartByByte[$normalizedOffset], $originalEndByByte[$normalizedEnd])) {
                        continue;
                    }
                    $matchedOriginalRanges[] = [
                        $originalStartByByte[$normalizedOffset],
                        $originalEndByByte[$normalizedEnd],
                    ];
                }
            }
        }

        $matchedOriginalRanges = array_values(array_unique($matchedOriginalRanges, SORT_REGULAR));
        usort($matchedOriginalRanges, fn ($a, $b) => $b[0] <=> $a[0]);
        foreach ($matchedOriginalRanges as [$start, $end]) {
            $matchedOriginal = substr($censoredText, $start, $end - $start);
            $stars = str_repeat('*', max(4, mb_strlen($matchedOriginal, 'UTF-8')));
            $censoredText = substr_replace($censoredText, $stars, $start, $end - $start);
        }

        $detectedWords = array_values(array_unique($detectedWords));
        $isDetected = ! empty($detectedWords);

        // Hitung tingkat keparahan akhir
        $finalSeverity = 'none';
        if ($isDetected) {
            if ($highestSeverityScore >= 3 || $matchCount >= 3) {
                $finalSeverity = 'high';
            } elseif ($highestSeverityScore === 2 || $matchCount === 2) {
                $finalSeverity = 'medium';
            } else {
                $finalSeverity = 'low';
            }
        }

        return [
            'detected' => $isDetected,
            'words' => $detectedWords,
            'severity' => $finalSeverity,
            'censored_text' => $censoredText,
            'match_count' => $matchCount,
        ];
    }

    /**
     * Normalisasi teks secara wajar tanpa menyebabkan false positive agresif.
     */
    public function normalizeText(string $text): string
    {
        $normalized = preg_replace('/\s+/', ' ', $this->normalizeCharacter($text));

        return trim($normalized ?? '');
    }

    private function normalizeCharacter(string $text): string
    {
        $normalized = mb_strtolower($text);

        // Substitusi karakter leetspeak standar yang umum
        $replacements = [
            '@' => 'a',
            '4' => 'a',
            '0' => 'o',
            '1' => 'i',
            '!' => 'i',
            '3' => 'e',
            '$' => 's',
            '5' => 's',
            '7' => 't',
        ];

        // Lakukan substitusi hanya ketika dikelilingi huruf atau pemisah
        $normalized = strtr($normalized, $replacements);

        return $normalized;
    }

    /**
     * Normalize per Unicode character while retaining byte offsets into the original text.
     * The moderation pattern accepts separators, so whitespace does not need collapsing.
     *
     * @return array{string, array<int, int>, array<int, int>}
     */
    private function normalizeWithOffsets(string $text): array
    {
        $characters = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $normalized = '';
        $startByByte = [];
        $endByByte = [];
        $originalOffset = 0;

        foreach ($characters as $character) {
            $characterBytes = strlen($character);
            $normalizedCharacter = $this->normalizeCharacter($character);
            $normalizedBytes = strlen($normalizedCharacter);
            $normalized .= $normalizedCharacter;
            for ($i = 0; $i < $normalizedBytes; $i++) {
                $startByByte[] = $originalOffset;
                $endByByte[] = $originalOffset + $characterBytes;
            }
            $originalOffset += $characterBytes;
        }

        return [$normalized, $startByByte, $endByByte];
    }

    /**
     * Ambil kata-kata kasar aktif dari Cache atau in-memory memoization.
     *
     * @return array<array{word: string, severity: string}>
     */
    public function getActiveBadWords(): array
    {
        if (static::$runtimeWords !== null) {
            return static::$runtimeWords;
        }

        $cached = null;
        try {
            $cached = Cache::get(self::CACHE_BAD_WORDS_KEY);
        } catch (\Throwable $e) {
            report($e);
        }

        if (is_array($cached) && ! empty($cached)) {
            return static::$runtimeWords = $cached;
        }

        $words = [];
        $dictionaryPath = resource_path('dictionaries/badwords-list.json');

        if (is_file($dictionaryPath)) {
            $dictionary = json_decode((string) file_get_contents($dictionaryPath), true) ?: [];
            foreach (['en', 'id'] as $key) {
                foreach (($dictionary[$key] ?? []) as $word) {
                    $words[] = ['word' => $word, 'severity' => 'medium'];
                }
            }
        }

        try {
            if (Schema::hasTable('bad_words')) {
                $customWords = BadWord::where('is_active', true)
                    ->get(['word', 'severity'])
                    ->toArray();
                $words = array_merge($words, $customWords);
            }
        } catch (\Throwable $e) {
            // Daftar bawaan tetap dapat dipakai jika database belum tersedia.
        }

        $result = collect($words)
            ->filter(fn ($item) => ! empty($item['word']))
            ->unique(fn ($item) => mb_strtolower(trim($item['word'])))
            ->map(fn ($item) => [
                'word' => trim($item['word']),
                'severity' => $item['severity'] ?? 'medium',
            ])
            ->values()
            ->all();

        try {
            Cache::put(self::CACHE_BAD_WORDS_KEY, $result, self::CACHE_TTL);
        } catch (\Throwable $e) {
            report($e);
        }

        return static::$runtimeWords = $result;
    }

    /** Bersihkan cache daftar kata terlarang. */
    public function clearCache(): void
    {
        static::$runtimeWords = null;

        try {
            Cache::forget(self::CACHE_BAD_WORDS_KEY);
        } catch (\Throwable $e) {
            // Ignore
        }

        try {
            Cache::store('file')->forget(self::CACHE_BAD_WORDS_KEY);
        } catch (\Throwable $e) {
            // Ignore if store not supported
        }
    }

    /**
     * Membangun regex pattern yang menangani variasi penulisan tanpa merusak kata normal.
     */
    protected function buildRegexPattern(string $word): string
    {
        $chars = preg_split('//u', $word, -1, PREG_SPLIT_NO_EMPTY);
        $escapedChars = array_map(function ($char) {
            return preg_quote($char, '/');
        }, $chars);

        // Gabungkan tiap karakter dengan opsi pemisah opsional (spasi, strip, titik)
        $charPattern = implode('[\s\p{P}\p{S}\p{Cf}]*', $escapedChars);

        // Boundary agar tidak mencocokkan di dalam kata lain yang valid
        return '/(?<![a-zA-Z0-9])'.$charPattern.'(?![a-zA-Z0-9])/iu';
    }
}
