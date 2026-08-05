<?php

use App\Livewire\Admin\FrequentWordsChart;
use App\Models\ChatHistory;
use App\Models\CustomStopword;
use App\Models\User;
use App\Services\QuestionAnalysisService;
use Livewire\Livewire;

test('question analysis service extracts unigrams, applies stopwords, and stems words using sastrawi', function () {
    $user = User::factory()->create();

    // Insert sample chat history with variations of registration words
    ChatHistory::create([
        'question' => 'Berapa biaya pendaftaran PMB di STMIK Bandung?',
        'answer' => 'Biaya pendaftaran adalah 250rb.',
        'status' => 'completed',
    ]);

    ChatHistory::create([
        'question' => 'Bagaimana cara mendaftar secara online?',
        'answer' => 'Jalur pendaftaran online...',
        'status' => 'completed',
    ]);

    ChatHistory::create([
        'question' => 'Dimana mendaftarnya untuk jalur beasiswa?',
        'answer' => 'Website PMB...',
        'status' => 'completed',
    ]);

    // Add custom stopword connected to user_id
    CustomStopword::create([
        'user_id' => $user->id,
        'word' => 'stmik',
    ]);

    $service = new QuestionAnalysisService;
    $results = $service->getTopFrequentWords(7, 10);

    $words = array_column($results, 'word');

    // Sastrawi stemmer should stem pendaftaran, mendaftar, and mendaftarnya into 'daftar'
    expect($words)->toContain('daftar')
        ->toContain('biaya')
        ->not->toContain('pendaftaran')
        ->not->toContain('mendaftar')
        ->not->toContain('mendaftarnya')
        ->not->toContain('berapa')
        ->not->toContain('bagaimana')
        ->not->toContain('stmik');

    // Root word 'daftar' should aggregate count of all 3 variations (count = 3)
    $daftarItem = collect($results)->firstWhere('word', 'daftar');
    expect($daftarItem['count'])->toBe(3);
});

test('admin can add and delete custom stopwords via livewire component', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(FrequentWordsChart::class)
        ->set('new_stopwords', 'beasiswa, kampus')
        ->call('addStopwords')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('custom_stopwords', [
        'user_id' => $user->id,
        'word' => 'beasiswa',
    ]);

    $this->assertDatabaseHas('custom_stopwords', [
        'user_id' => $user->id,
        'word' => 'kampus',
    ]);

    $stopword = CustomStopword::where('word', 'beasiswa')->first();

    Livewire::actingAs($user)
        ->test(FrequentWordsChart::class)
        ->call('deleteStopword', $stopword->id);

    $this->assertDatabaseMissing('custom_stopwords', [
        'id' => $stopword->id,
    ]);
});
