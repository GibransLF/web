<?php

namespace App\Services;

use App\Models\ChatHistory;
use App\Models\CustomStopword;
use Illuminate\Support\Carbon;
use Sastrawi\Stemmer\StemmerFactory;
use Sastrawi\StopWordRemover\StopWordRemoverFactory;

class QuestionAnalysisService
{
    /**
     * Get the top N most frequent unigram words from user questions in the last $days days.
     * Filtered using Sastrawi default stopwords + admin custom stopwords and stemmed via Sastrawi Stemmer.
     *
     * @return array<int, array{word: string, count: int, percentage: float}>
     */
    public function getTopFrequentWords(int $days = 7, int $limit = 10): array
    {
        $startDate = Carbon::now()->subDays($days);

        // Fetch questions in the last $days
        $questions = ChatHistory::where('created_at', '>=', $startDate)
            ->pluck('question');

        if ($questions->isEmpty()) {
            return [];
        }

        // Load Sastrawi default stopwords
        $stopWordFactory = new StopWordRemoverFactory;
        $defaultStopwordsArray = $stopWordFactory->getStopWords();

        // Load Sastrawi Stemmer
        $stemmerFactory = new StemmerFactory;
        $stemmer = $stemmerFactory->createStemmer();

        // Standard Indonesian question & conversational noise words fallback
        $conversationalNoise = [
            'berapa', 'bagaimana', 'gimana', 'apa', 'apakah', 'siapa', 'dimana', 'kapan',
            'kenapa', 'mengapa', 'mana', 'ke', 'dari', 'di', 'ini', 'itu', 'kak', 'min',
            'admin', 'info', 'halo', 'hai', 'permisi', 'tolong', 'dong', 'sih', 'ya', 'kah',
            'mau', 'tanya', 'ingin', 'sy', 'saya',
        ];

        // Load admin custom stopwords from database
        $customStopwordsArray = CustomStopword::pluck('word')
            ->map(fn ($w) => strtolower(trim($w)))
            ->filter()
            ->toArray();

        // Merge all stopwords into a lookup dictionary
        $mergedStopwords = array_merge($defaultStopwordsArray, $conversationalNoise, $customStopwordsArray);
        $stopwordDict = array_flip(array_map('strtolower', $mergedStopwords));

        $wordCounts = [];

        foreach ($questions as $question) {
            if (empty($question)) {
                continue;
            }

            // Case folding & remove non-alphabetical characters
            $cleanText = strtolower($question);
            $cleanText = preg_replace('/[^a-z\s]/u', ' ', $cleanText);

            // Tokenize into single words (unigram)
            $tokens = preg_split('/\s+/', $cleanText, -1, PREG_SPLIT_NO_EMPTY);

            foreach ($tokens as $token) {
                // Ignore short tokens (length < 3) and stopwords
                if (mb_strlen($token) < 3 || isset($stopwordDict[$token])) {
                    continue;
                }

                // Apply Sastrawi Stemmer to convert word to root word
                $stemmedWord = strtolower($stemmer->stem($token));

                if (mb_strlen($stemmedWord) < 3 || isset($stopwordDict[$stemmedWord])) {
                    continue;
                }

                $wordCounts[$stemmedWord] = ($wordCounts[$stemmedWord] ?? 0) + 1;
            }
        }

        if (empty($wordCounts)) {
            return [];
        }

        // Sort by frequency descending
        arsort($wordCounts);

        $topWords = [];
        $maxCount = max($wordCounts);

        foreach (array_slice($wordCounts, 0, $limit, true) as $word => $count) {
            $percentage = round(($count / $maxCount) * 100, 1);
            $topWords[] = [
                'word' => $word,
                'count' => $count,
                'percentage' => $percentage,
            ];
        }

        return $topWords;
    }
}
