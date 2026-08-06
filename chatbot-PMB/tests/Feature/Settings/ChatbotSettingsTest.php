<?php

use App\Models\ChatbotSetting;
use App\Models\User;
use Livewire\Livewire;

test('chatbot settings page is displayed for authenticated user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.chatbot-setting'))
        ->assertOk();
});

test('guest cannot access chatbot settings page', function () {
    $this->get(route('admin.chatbot-setting'))
        ->assertRedirect(route('login'));
});

test('chatbot settings can be updated successfully', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test('pages::admin.chatbot-settings')
        ->set('max_input_character', 800)
        ->set('max_chat_memory', 3)
        ->set('max_guest_chat', 5)
        ->set('top_k', 6)
        ->set('fetch_k', 20)
        ->set('temperature', 0.2)
        ->set('system_prompt', 'System prompt tes PMB STMIK Bandung.')
        ->call('saveSettings')
        ->assertHasNoErrors()
        ->assertSee('Konfigurasi Chatbot AI berhasil diperbarui');

    $setting = ChatbotSetting::current()->fresh();

    expect($setting->max_input_character)->toBe(800);
    expect($setting->max_chat_memory)->toBe(3);
    expect($setting->max_guest_chat)->toBe(5);
    expect($setting->top_k)->toBe(6);
    expect($setting->fetch_k)->toBe(20);
    expect((float) $setting->temperature)->toBe(0.2);
    expect($setting->system_prompt)->toBe('System prompt tes PMB STMIK Bandung.');
});

test('max_chat_memory can be set to zero for no conversation memory', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test('pages::admin.chatbot-settings')
        ->set('max_chat_memory', 0)
        ->call('saveSettings')
        ->assertHasNoErrors()
        ->assertSee('Konfigurasi Chatbot AI berhasil diperbarui');

    $setting = ChatbotSetting::current()->fresh();
    expect($setting->max_chat_memory)->toBe(0);
});

test('max_guest_chat can be set to zero to disable guest chat', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test('pages::admin.chatbot-settings')
        ->set('max_guest_chat', 0)
        ->call('saveSettings')
        ->assertHasNoErrors()
        ->assertSee('Konfigurasi Chatbot AI berhasil diperbarui');

    $setting = ChatbotSetting::current()->fresh();
    expect($setting->max_guest_chat)->toBe(0);
});

test('fetch_k must be greater than or equal to top_k', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test('pages::admin.chatbot-settings')
        ->set('top_k', 10)
        ->set('fetch_k', 5)
        ->call('saveSettings')
        ->assertHasErrors(['fetch_k']);
});

test('reset defaults restores initial chatbot configuration', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test('pages::admin.chatbot-settings')
        ->set('max_input_character', 1500)
        ->set('max_guest_chat', 10)
        ->set('temperature', 0.8)
        ->call('resetDefaults')
        ->assertSet('max_input_character', 50)
        ->assertSet('max_chat_memory', 0)
        ->assertSet('max_guest_chat', 4)
        ->assertSet('top_k', 7)
        ->assertSet('temperature', 0.2)
        ->assertSee('Form konfigurasi telah dikembalikan ke nilai default PMB');
});
