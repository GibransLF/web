<x-layouts::auth :title="__('Log in')" :logo="false">
    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-8 shadow-sm flex flex-col gap-6">
        <!-- Header -->
        <div class="text-center">
            <div class="mb-4 inline-flex items-center justify-center w-12 h-12 rounded-full bg-[#1B287D]/10 dark:bg-[#F9CE04]/10">
                <flux:icon name="academic-cap" class="w-6 h-6 text-[#1B287D] dark:text-[#F9CE04]" />
            </div>
            <h1 class="text-xl font-bold text-zinc-950 dark:text-white">{{ __('Selamat Datang') }}</h1>
            <flux:text class="text-xs mt-1">{{ __('Masuk ke Portal Penerimaan Mahasiswa Baru STMIK Bandung') }}</flux:text>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-6">
            @csrf

            <!-- Email Address -->
            <flux:field>
                <flux:label>{{ __('Alamat Email') }}</flux:label>
                <flux:input
                    name="email"
                    :value="old('email')"
                    type="email"
                    autofocus
                    autocomplete="email"
                    placeholder="nama@email.com"
                    icon="envelope"
                    :invalid="$errors->has('email')"
                />
                <flux:error name="email" />
            </flux:field>

            <!-- Password -->
            <flux:field>
                <flux:label>{{ __('Kata Sandi') }}</flux:label>
                <flux:input
                    name="password"
                    type="password"
                    autocomplete="current-password"
                    placeholder="••••••••"
                    icon="lock-closed"
                    viewable
                    :invalid="$errors->has('password')"
                />
                <flux:error name="password" />
            </flux:field>

            <!-- Remember Me -->
            <flux:checkbox name="remember" :label="__('Ingat saya')" :checked="old('remember')" />

            <!-- Submit Button -->
            <flux:button variant="primary" type="submit" class="w-full !bg-[#1B287D] hover:!bg-[#121c5a] !text-white py-3 rounded-lg font-semibold" icon-trailing="arrow-right-start-on-rectangle" data-test="login-button">
                {{ __('Masuk') }}
            </flux:button>

            <!-- Forgot Password -->
            @if (Route::has('password.request'))
                <div class="text-center">
                    <flux:link class="text-xs font-semibold text-[#1B287D] dark:text-[#F9CE04] hover:underline" :href="route('password.request')" wire:navigate>
                        {{ __('Lupa kata sandi?') }}
                    </flux:link>
                </div>
            @endif
        </form>

        <div class="relative py-2">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-zinc-200 dark:border-zinc-800"></div>
            </div>
            <div class="relative flex justify-center text-xs">
                <span class="bg-white dark:bg-zinc-900 px-4 text-zinc-500 uppercase tracking-widest">{{ __('Atau') }}</span>
            </div>
        </div>

        <div class="text-sm text-center text-zinc-600 dark:text-zinc-400">
            <span>{{ __('Belum punya akun?') }}</span>
            <flux:link class="text-[#1B287D] dark:text-[#F9CE04] font-bold hover:underline" :href="route('register')" wire:navigate>{{ __('Daftar sekarang') }}</flux:link>
        </div>
    </div>
</x-layouts::auth>
