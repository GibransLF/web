<?php

use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');
Route::livewire('chat', 'pages::chat')->name('chat');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('admin', function (Request $request) {
        return $request->user()->isAdmin()
            ? redirect()->route('admin.dashboard')
            : redirect()->route('dashboard');
    });

    Route::get('dashboard', function (Request $request) {
        if ($request->user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        return view('dashboard');
    })->name('dashboard');

    Route::get('chat-history', function (Request $request) {
        if ($request->user()->isAdmin()) {
            return redirect()->route('admin.chat-history');
        }
    });
    Route::livewire('chat-history', 'pages::admin.chat-history')->name('supervisor.chat-history');
    Route::redirect('historychat', 'chat-history');

    // Admin Only Routes
    Route::middleware([EnsureUserIsAdmin::class])->group(function () {
        Route::view('admin/dashboard', 'dashboard')->name('admin.dashboard');
        Route::livewire('admin/chat-history', 'pages::admin.chat-history')->name('admin.chat-history');
        Route::livewire('admin/knowledge-base', 'pages::admin.knowledge-base')->name('admin.knowledge-base');
        Route::livewire('admin/chatbot-setting', 'pages::admin.chatbot-settings')->name('admin.chatbot-setting');
        Route::livewire('admin/chatbot-settings', 'pages::admin.chatbot-settings')->name('admin.chatbot-settings');
        Route::livewire('admin/supervisors', 'pages::admin.supervisors')->name('admin.supervisors');
    });
});

require __DIR__.'/settings.php';
