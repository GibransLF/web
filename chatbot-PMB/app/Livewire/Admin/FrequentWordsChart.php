<?php

namespace App\Livewire\Admin;

use App\Models\CustomStopword;
use App\Services\QuestionAnalysisService;
use Livewire\Component;

class FrequentWordsChart extends Component
{
    public string $new_stopwords = '';

    public string $search_custom = '';

    protected array $rules = [
        'new_stopwords' => 'required|string|min:2',
    ];

    protected array $messages = [
        'new_stopwords.required' => 'Masukkan setidaknya satu kata stopword tambahan.',
        'new_stopwords.min' => 'Stopword minimal terdiri dari 2 karakter.',
    ];

    public function addStopwords(): void
    {
        if (! auth()->user()->isAdmin()) {
            return;
        }

        $this->validate();

        // Split by comma, spaces, or line breaks
        $words = preg_split('/[\s,\n]+/', strtolower($this->new_stopwords), -1, PREG_SPLIT_NO_EMPTY);
        $addedCount = 0;

        $userId = auth()->id();

        foreach ($words as $w) {
            $cleanWord = trim($w);
            if (mb_strlen($cleanWord) >= 2) {
                // Ensure duplicate words are not created
                $exists = CustomStopword::where('word', $cleanWord)->exists();
                if (! $exists) {
                    CustomStopword::create([
                        'user_id' => $userId,
                        'word' => $cleanWord,
                    ]);
                    $addedCount++;
                }
            }
        }

        $this->reset('new_stopwords');

        if ($addedCount > 0) {
            session()->flash('success_stopword', "Berhasil menambahkan {$addedCount} stopword baru!");
        } else {
            session()->flash('info_stopword', 'Kata-kata yang dimasukkan sudah ada di daftar stopword.');
        }
    }

    public function deleteStopword(int $id): void
    {
        if (! auth()->user()->isAdmin()) {
            return;
        }

        $stopword = CustomStopword::find($id);
        if ($stopword) {
            $stopword->delete();
            session()->flash('success_stopword', "Stopword \"{$stopword->word}\" berhasil dihapus.");
        }
    }

    public function render(QuestionAnalysisService $analysisService)
    {
        $topWords = $analysisService->getTopFrequentWords(7, 10);
        $customStopwords = CustomStopword::with('user')
            ->when($this->search_custom, fn ($q) => $q->where('word', 'like', "%{$this->search_custom}%"))
            ->latest()
            ->get();

        return view('livewire.admin.frequent-words-chart', [
            'topWords' => $topWords,
            'customStopwords' => $customStopwords,
            'totalCustomCount' => CustomStopword::count(),
        ]);
    }
}
