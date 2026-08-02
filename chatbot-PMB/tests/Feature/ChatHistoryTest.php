<?php

use App\Models\ChatHistory;
use App\Models\User;
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

test('chatbot saves user_id when authenticated user sends message', function () {
    $user = User::factory()->create(['name' => 'Admin PMB']);
    $this->actingAs($user);

    Livewire::test('pages::chat')
        ->set('message', 'Berapa biaya pendaftaran PMB?')
        ->call('sendMessage');

    $history = ChatHistory::first();
    expect($history)->not->toBeNull()
        ->and($history->user_id)->toBe($user->id)
        ->and($history->question)->toBe('Berapa biaya pendaftaran PMB?');
});

test('chatbot saves user_id as null when unauthenticated guest sends message', function () {
    Livewire::test('pages::chat')
        ->set('message', 'Persyaratan pendaftaran?')
        ->call('sendMessage');

    $history = ChatHistory::first();
    expect($history)->not->toBeNull()
        ->and($history->user_id)->toBeNull()
        ->and($history->guest_id)->not->toBeNull()
        ->and($history->question)->toBe('Persyaratan pendaftaran?');
});

test('chat history displays user name for user_id and guest for guest_id', function () {
    $user = User::factory()->create(['name' => 'Dr. Admin']);

    ChatHistory::create([
        'user_id' => $user->id,
        'guest_id' => 'guest_111',
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
