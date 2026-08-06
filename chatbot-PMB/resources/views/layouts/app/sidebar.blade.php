<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" :href="auth()->user()->isAdmin() ? route('admin.dashboard') : route('dashboard')" wire:navigate />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group :heading="__('PMB Management')" class="grid">
                    <flux:sidebar.item icon="home" :href="auth()->user()->isAdmin() ? route('admin.dashboard') : route('dashboard')" :current="request()->routeIs('dashboard') || request()->routeIs('admin.dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>
                    @if(auth()->user()->isAdmin())
                        <flux:sidebar.item icon="book-open" :href="route('admin.knowledge-base')" :current="request()->routeIs('admin.knowledge-base')" wire:navigate>
                            {{ __('Knowledge Base') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="cog" :href="route('admin.chatbot-setting')" :current="request()->routeIs('admin.chatbot-setting')" wire:navigate>
                            {{ __('Chatbot Settings') }}
                        </flux:sidebar.item>
                    @endif
                    <flux:sidebar.item icon="chat-bubble-left-right" :href="auth()->user()->isAdmin() ? route('admin.chat-history') : route('supervisor.chat-history')" :current="request()->routeIs('admin.chat-history') || request()->routeIs('supervisor.chat-history')" wire:navigate>
                        {{ __('Chat History') }}
                    </flux:sidebar.item>
                    @if(auth()->user()->isAdmin())
                        <flux:sidebar.item icon="users" :href="route('admin.supervisors')" :current="request()->routeIs('admin.supervisors')" wire:navigate>
                            {{ __('Kelola Akun') }}
                        </flux:sidebar.item>
                    @endif
                </flux:sidebar.group>
            </flux:sidebar.nav>

            <flux:spacer />

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :initials="auth()->user()->initials()"
                                />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            {{ __('Log out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
