<?php

use App\Enums\UserRole;
use App\Models\ChatHistory;
use App\Models\User;
use Livewire\Livewire;

test('chat history defaults is_validated to false', function () {
    $chat = ChatHistory::create([
        'question' => 'Berapa spp?',
        'answer' => 'Maaf, data belum tersedia.',
    ]);

    expect($chat->is_validated)->toBeFalse();
});

test('unanswered questions alert displays questions containing maaf or belum tersedia in answer when unvalidated', function () {
    $admin = User::factory()->create(['role' => UserRole::ADMIN]);
    $this->actingAs($admin);

    ChatHistory::create([
        'question' => 'Apakah ada beasiswa?',
        'answer' => 'Maaf, informasi beasiswa belum tersedia.',
        'is_validated' => false,
    ]);

    ChatHistory::create([
        'question' => 'Berapa biaya pendaftaran?',
        'answer' => 'Biaya pendaftaran adalah Rp 250.000',
        'is_validated' => false,
    ]);

    Livewire::test('admin.unanswered-questions-alert')
        ->assertSee('Pertanyaan yang tidak dapat dijawab.')
        ->assertSee('Apakah ada beasiswa?')
        ->assertDontSee('Berapa biaya pendaftaran?');
});

test('does not include questions where user wrote maaf in question but answer was valid', function () {
    $admin = User::factory()->create(['role' => UserRole::ADMIN]);
    $this->actingAs($admin);

    ChatHistory::create([
        'question' => 'Maaf kak, mau tanya biaya pendaftaran berapa?',
        'answer' => 'Biaya pendaftaran adalah Rp 250.000',
        'is_validated' => false,
    ]);

    Livewire::test('admin.unanswered-questions-alert')
        ->assertDontSee('Maaf kak, mau tanya biaya pendaftaran berapa?');
});

test('admin can mark unanswered question as validated', function () {
    $admin = User::factory()->create(['role' => UserRole::ADMIN]);
    $this->actingAs($admin);

    $chat = ChatHistory::create([
        'question' => 'Fasilitas perpustakaan?',
        'answer' => 'Maaf, sistem tidak menemukan data.',
        'is_validated' => false,
    ]);

    Livewire::test('admin.unanswered-questions-alert')
        ->call('markAsValidated', $chat->id)
        ->assertSee('Pertanyaan berhasil divalidasi!');

    expect($chat->fresh()->is_validated)->toBeTrue();
});

test('admin can mark all unanswered questions as validated', function () {
    $admin = User::factory()->create(['role' => UserRole::ADMIN]);
    $this->actingAs($admin);

    $chat1 = ChatHistory::create([
        'question' => 'Pertanyaan 1',
        'answer' => 'Maaf, belum tersedia.',
        'is_validated' => false,
    ]);

    $chat2 = ChatHistory::create([
        'question' => 'Pertanyaan 2',
        'answer' => 'Maaf, data tidak ditemukan.',
        'is_validated' => false,
    ]);

    Livewire::test('admin.unanswered-questions-alert')
        ->call('markAllAsValidated')
        ->assertSee('Berhasil memvalidasi 2 pertanyaan!');

    expect($chat1->fresh()->is_validated)->toBeTrue()
        ->and($chat2->fresh()->is_validated)->toBeTrue();
});

test('supervisor cannot mark unanswered question as validated', function () {
    $supervisor = User::factory()->create(['role' => UserRole::SUPERVISOR]);
    $this->actingAs($supervisor);

    $chat = ChatHistory::create([
        'question' => 'Jam operasional kampus?',
        'answer' => 'Maaf, belum tersedia.',
        'is_validated' => false,
    ]);

    Livewire::test('admin.unanswered-questions-alert')
        ->call('markAsValidated', $chat->id);

    expect($chat->fresh()->is_validated)->toBeFalse();
});
