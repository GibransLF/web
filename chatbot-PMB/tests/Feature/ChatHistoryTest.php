<?php

use App\Jobs\ProcessAiChatResponse;
use App\Models\ChatbotSetting;
use App\Models\ChatHistory;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;

test('guests are redirected from chat history page', function () {
    $response = $this->get(route('admin.chat-history'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can render admin chat history page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('admin.chat-history'));
    $response->assertOk();
});

test('chatbot saves pending record and dispatches queue job when user sends message', function () {
    Queue::fake();

    $user = User::factory()->create(['name' => 'Admin PMB']);
    $this->actingAs($user);

    Livewire::test('chat-widget')
        ->set('message', 'Berapa biaya pendaftaran PMB?')
        ->call('sendMessage');

    $history = ChatHistory::first();
    expect($history)->not->toBeNull()
        ->and($history->user_id)->toBe($user->id)
        ->and($history->guest_id)->toBeNull()
        ->and($history->question)->toBe('Berapa biaya pendaftaran PMB?')
        ->and($history->status)->toBe('pending');

    Queue::assertPushed(ProcessAiChatResponse::class, function ($job) use ($history) {
        return $job->chatHistoryId === $history->id;
    });
});

test('chatbot saves guest message as pending and dispatches queue job', function () {
    Queue::fake();

    Livewire::test('chat-widget')
        ->set('message', 'Persyaratan pendaftaran?')
        ->call('sendMessage');

    $history = ChatHistory::first();
    expect($history)->not->toBeNull()
        ->and($history->user_id)->toBeNull()
        ->and($history->guest_id)->not->toBeNull()
        ->and($history->question)->toBe('Persyaratan pendaftaran?')
        ->and($history->status)->toBe('pending');

    Queue::assertPushed(ProcessAiChatResponse::class);
});

test('job handles ai response successfully', function () {
    Http::fake([
        '*/service/chat' => Http::response([
            'success' => true,
            'response' => 'Biaya pendaftaran adalah Rp 250.000',
        ], 200),
    ]);

    $history = ChatHistory::create([
        'user_id' => null,
        'guest_id' => 'guest_test',
        'question' => 'Berapa biaya pendaftaran?',
        'answer' => null,
        'status' => 'pending',
    ]);

    $job = new ProcessAiChatResponse($history->id, 'Berapa biaya pendaftaran?');
    $job->handle();

    $history->refresh();
    expect($history->status)->toBe('completed')
        ->and($history->answer)->toBe('Biaya pendaftaran adalah Rp 250.000');
});

test('polling retrieves completed ai response', function () {
    $history = ChatHistory::create([
        'user_id' => null,
        'guest_id' => 'guest_test',
        'question' => 'Syarat pendaftaran?',
        'answer' => 'Syaratnya adalah FC Ijazah',
        'status' => 'completed',
    ]);

    Livewire::test('chat-widget')
        ->set('isProcessing', true)
        ->set('pendingHistoryId', $history->id)
        ->call('checkPendingResponse')
        ->assertSet('isProcessing', false)
        ->assertSee('Syaratnya adalah FC Ijazah');
});

test('chat history displays user name for user_id and guest for guest_id', function () {
    $user = User::factory()->create(['name' => 'Dr. Admin']);

    ChatHistory::create([
        'user_id' => $user->id,
        'guest_id' => null,
        'question' => 'Pertanyaan User Admin',
        'answer' => 'Jawaban Admin',
    ]);

    ChatHistory::create([
        'user_id' => null,
        'guest_id' => 'guest_222',
        'question' => 'Pertanyaan Tamu',
        'answer' => 'Jawaban Tamu',
    ]);

    $this->actingAs($user);

    Livewire::test('pages::admin.chat-history')
        ->assertSee('Dr. Admin')
        ->assertSee('guest (guest_222)');
});

test('chat history can be cleared', function () {
    $user = User::factory()->create();

    ChatHistory::create([
        'guest_id' => 'guest_999',
        'question' => 'Syarat pendaftaran?',
        'answer' => 'FC Ijazah & KTP.',
    ]);

    expect(ChatHistory::count())->toBe(1);

    $this->actingAs($user);

    Livewire::test('pages::admin.chat-history')
        ->call('clearAllHistory');

    expect(ChatHistory::count())->toBe(0);
});

test('guest chat history is restored on component mount', function () {
    session()->put('pmb_guest_id', 'guest_test_123');

    ChatHistory::create([
        'guest_id' => 'guest_test_123',
        'question' => 'Berapa biaya kuliah?',
        'answer' => 'Biaya kuliah Rp 3.500.000 per semester.',
    ]);

    Livewire::test('chat-widget')
        ->assertSee('Berapa biaya kuliah?')
        ->assertSee('Biaya kuliah Rp 3.500.000 per semester.');
});

test('authenticated user chat history is restored for today only on component mount', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    // Yesterday's chat
    $oldChat = ChatHistory::create([
        'user_id' => $user->id,
        'guest_id' => 'guest_old',
        'question' => 'Pertanyaan Kemarin',
        'answer' => 'Jawaban Kemarin',
    ]);
    $oldChat->timestamps = false;
    $oldChat->created_at = now()->subDays(2);
    $oldChat->save();

    // Today's chat
    ChatHistory::create([
        'user_id' => $user->id,
        'guest_id' => 'guest_today',
        'question' => 'Pertanyaan Hari Ini',
        'answer' => 'Jawaban Hari Ini',
        'created_at' => now(),
    ]);

    Livewire::test('chat-widget')
        ->assertSee('Pertanyaan Hari Ini')
        ->assertSee('Jawaban Hari Ini')
        ->assertDontSee('Pertanyaan Kemarin');
});

test('message input collapses multiple spaces via regex', function () {
    Livewire::test('chat-widget')
        ->set('message', '   Berapa   biaya   pendaftaran   PMB?   ')
        ->call('sendMessage')
        ->assertSet('messages.0.content', 'Berapa biaya pendaftaran PMB?');
});

test('history payload sent to AI service is limited by max_chat_memory setting', function () {
    ChatbotSetting::current()->update(['max_chat_memory' => 1]);
    $component = Livewire::test('chat-widget');
    expect($component->get('maxChatMemory'))->toBe(1);

    ChatbotSetting::current()->update(['max_chat_memory' => 0]);
    $componentZero = Livewire::test('chat-widget');
    expect($componentZero->get('maxChatMemory'))->toBe(0);
});
