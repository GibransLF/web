<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');
Route::livewire('chat', 'pages::⚡chat')->name('chat');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::livewire('admin/knowledge-base', 'pages::admin.⚡knowledge-base')->name('admin.knowledge-base');
    Route::livewire('admin/settings/chatbot', 'pages::admin.⚡chatbot-settings')->name('admin.settings');
    Route::livewire('admin/chat-history', 'pages::admin.⚡chat-history')->name('admin.chat-history');
});

require __DIR__.'/settings.php';
