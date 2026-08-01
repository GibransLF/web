<header
    class="sticky top-0 z-50 w-full backdrop-blur-md bg-white/90 border-b border-zinc-200 dark:bg-zinc-900/90 dark:border-zinc-800">
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        <!-- Logo -->
        <div class="flex-shrink-0 flex items-center">
            <a href="/" wire:navigate class="flex items-center">
                <img src="{{ asset('images/logoSTMIK.png') }}" alt="Logo STMIK Bandung"
                    class="h-10 w-auto object-contain">
            </a>
        </div>

        <!-- Desktop Nav -->
        <flux:navbar class="hidden md:flex space-x-4">
            <flux:navbar.item href="/" :current="request()->routeIs('home')" wire:navigate>Beranda</flux:navbar.item>
            <flux:navbar.item href="{{ route('tuition') }}" :current="request()->routeIs('tuition')" wire:navigate>Biaya</flux:navbar.item>
            <flux:navbar.item href="{{ route('faq') }}" :current="request()->routeIs('faq')" wire:navigate>FAQ</flux:navbar.item>
            <flux:navbar.item href="{{ route('about') }}" :current="request()->routeIs('about')" wire:navigate>Tentang Kami</flux:navbar.item>
            <flux:navbar.item href="{{ route('contact') }}" :current="request()->routeIs('contact')" wire:navigate>Hubungi Kami</flux:navbar.item>
        </flux:navbar>

        <!-- Desktop CTA -->
        <div class="hidden md:flex items-center space-x-4">
            @auth
            <flux:button variant="primary" class="!bg-[#1B287D] !text-white hover:!bg-[#121c5a]"
                href="{{ auth()->user()->role === \App\Enums\UserRole::Admin ? route('admin.dashboard') : route('dashboard') }}"
                wire:navigate>Dashboard</flux:button>
            @else
            @if (Route::has('login'))
            <flux:button variant="ghost" class="!text-zinc-700 hover:!text-[#1B287D] dark:!text-zinc-300"
                href="{{ route('login') }}" wire:navigate>Log in</flux:button>
            @endif
            @if (Route::has('register'))
            <flux:button variant="primary" class="!bg-[#F9CE04] !text-[#1B287D] hover:!bg-[#e0ba03] !border-none"
                href="{{ route('register') }}" wire:navigate>Daftar</flux:button>
            @endif
            @endauth
        </div>

        <!-- Mobile Menu Button -->
        <div class="md:hidden flex items-center">
            <flux:dropdown>
                <flux:button variant="ghost" icon="bars-3" aria-label="Menu" />
                <flux:menu>
                    <flux:menu.item href="/" class="{{ request()->routeIs('home') ? 'bg-zinc-100 dark:bg-zinc-800 text-[#1B287D] dark:text-[#F9CE04]' : '' }}" wire:navigate>Beranda</flux:menu.item>
                    <flux:menu.item href="{{ route('tuition') }}" class="{{ request()->routeIs('tuition') ? 'bg-zinc-100 dark:bg-zinc-800 text-[#1B287D] dark:text-[#F9CE04]' : '' }}" wire:navigate>Biaya</flux:menu.item>
                    <flux:menu.item href="{{ route('faq') }}" class="{{ request()->routeIs('faq') ? 'bg-zinc-100 dark:bg-zinc-800 text-[#1B287D] dark:text-[#F9CE04]' : '' }}" wire:navigate>FAQ</flux:menu.item>
                    <flux:menu.item href="{{ route('about') }}" class="{{ request()->routeIs('about') ? 'bg-zinc-100 dark:bg-zinc-800 text-[#1B287D] dark:text-[#F9CE04]' : '' }}" wire:navigate>Tentang Kami</flux:menu.item>
                    <flux:menu.item href="{{ route('contact') }}" class="{{ request()->routeIs('contact') ? 'bg-zinc-100 dark:bg-zinc-800 text-[#1B287D] dark:text-[#F9CE04]' : '' }}" wire:navigate>Hubungi Kami</flux:menu.item>
                    <flux:menu.item href="/chat" class="{{ request()->routeIs('chat') ? 'bg-zinc-100 dark:bg-zinc-800' : '' }} text-[#1B287D]" wire:navigate>
                        Chat AI</flux:menu.item>
                    <flux:menu.separator />
                    @auth
                    <flux:menu.item
                        href="{{ auth()->user()->role === \App\Enums\UserRole::Admin ? route('admin.dashboard') : route('dashboard') }}"
                        wire:navigate>Dashboard</flux:menu.item>
                    @else
                    @if (Route::has('login'))
                    <flux:menu.item href="{{ route('login') }}" wire:navigate>Log in</flux:menu.item>
                    @endif
                    @if (Route::has('register'))
                    <flux:menu.item href="{{ route('register') }}" wire:navigate>Daftar</flux:menu.item>
                    @endif
                    @endauth
                </flux:menu>
            </flux:dropdown>
        </div>
    </div>
</header>