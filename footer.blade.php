@if(!request()->is('chat'))
<!-- Footer -->
<footer class="bg-white border-t border-zinc-200 dark:bg-zinc-900 dark:border-zinc-800 py-12 mt-auto lg:w-2/3 lg:pr-10">
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-4 gap-8">
        <div>
            <div class="mb-4">
                <img src="{{ asset('images/logoSTMIK.png') }}" alt="Logo STMIK Bandung"
                    class="h-10 w-auto object-contain">
            </div>
            <div class="space-y-2 text-sm text-zinc-600 dark:text-zinc-400 mt-4">
                <div class="flex items-start gap-2.5">
                    <flux:icon name="map-pin" class="w-4 h-4 mt-0.5 text-zinc-500 dark:text-zinc-400 flex-shrink-0" />
                    <span>Jl. Cikutra No.113, Bandung, Kota Bandung, Jawa Barat 40124, Indonesia</span>
                </div>
                <div class="flex items-center gap-2.5">
                    <flux:icon name="phone" class="w-4 h-4 text-zinc-500 dark:text-zinc-400 flex-shrink-0" />
                    <span>022-7207777</span>
                </div>
                <div class="flex items-center gap-2.5">
                    <flux:icon name="envelope" class="w-4 h-4 text-zinc-500 dark:text-zinc-400 flex-shrink-0" />
                    <a href="mailto:marketing@stmik-bandung.ac.id" class="hover:underline">marketing@stmik-bandung.ac.id</a>
                </div>
            </div>
        </div>
        <div>
            <h3 class="font-semibold text-sm text-[#1B287D] mb-4 uppercase tracking-wider">STMIK Bandung</h3>
            <ul class="space-y-2 text-sm text-zinc-600 dark:text-zinc-400">
                <li><a href="https://stmik-bandung.ac.id/" wire:navigate class="hover:text-[#F9CE04]">Situs Utama</a>
                </li>
                <li><a href="https://journal.stmik-bandung.ac.id/index.php/JurnalTI"
                        class="hover:text-[#F9CE04]">Jurnal</a></li>
                <li><a href="https://stmik-bandung.merdeka.academy/" class="hover:text-[#F9CE04]">Mata Kuliah</a></li>
            </ul>
        </div>
        <div>
            <h3 class="font-semibold text-sm text-[#1B287D] mb-4 uppercase tracking-wider">Tautan</h3>
            <ul class="space-y-2 text-sm text-zinc-600 dark:text-zinc-400">
                <li><a href="/" wire:navigate class="hover:text-[#F9CE04]">Beranda</a></li>
                <li><a href="{{ route('tuition') }}" wire:navigate class="hover:text-[#F9CE04]">Biaya</a></li>
                <li><a href="{{ route('faq') }}" wire:navigate class="hover:text-[#F9CE04]">FAQ</a></li>
                <li><a href="{{ route('about') }}" wire:navigate class="hover:text-[#F9CE04]">Tentang Kami</a></li>
                <li><a href="{{ route('contact') }}" wire:navigate class="hover:text-[#F9CE04]">Hubungi Kami</a></li>
            </ul>
        </div>
        <div>
            <h3 class="font-semibold text-sm text-[#1B287D] mb-4 uppercase tracking-wider">Sosial Media</h3>
            <div class="flex space-x-3 items-center">
                <a href="{{ env('SOCIAL_YOUTUBE_URL', '#') }}" target="_blank"
                    class="w-10 h-10 flex items-center justify-center rounded-xl bg-[#1B287D] text-white hover:bg-[#F9CE04] hover:text-[#1B287D] hover:-translate-y-1 transition-all duration-300 shadow-sm">
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path
                            d="M2.5 17a24.12 24.12 0 0 1 0-10 2 2 0 0 1 1.4-1.4 49.56 49.56 0 0 1 16.2 0A2 2 0 0 1 21.5 7a24.12 24.12 0 0 1 0 10 2 2 0 0 1-1.4 1.4 49.55 49.55 0 0 1-16.2 0A2 2 0 0 1 2.5 17">
                        </path>
                        <path d="m10 15 5-3-5-3z"></path>
                    </svg>
                </a>
                <a href="{{ env('SOCIAL_FACEBOOK_URL', '#') }}" target="_blank"
                    class="w-10 h-10 flex items-center justify-center rounded-xl bg-[#1B287D] text-white hover:bg-[#F9CE04] hover:text-[#1B287D] hover:-translate-y-1 transition-all duration-300 shadow-sm">
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path>
                    </svg>
                </a>
                <a href="{{ env('SOCIAL_INSTAGRAM_URL', '#') }}" target="_blank"
                    class="w-10 h-10 flex items-center justify-center rounded-xl bg-[#1B287D] text-white hover:bg-[#F9CE04] hover:text-[#1B287D] hover:-translate-y-1 transition-all duration-300 shadow-sm">
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <rect width="20" height="20" x="2" y="2" rx="5" ry="5"></rect>
                        <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
                        <line x1="17.5" x2="17.51" y1="6.5" y2="6.5"></line>
                    </svg>
                </a>
                <a href="https://wa.me/{{ env('WHATSAPP_NUMBER', '') }}" target="_blank"
                    class="w-10 h-10 flex items-center justify-center rounded-xl bg-[#1B287D] text-white hover:bg-[#F9CE04] hover:text-[#1B287D] hover:-translate-y-1 transition-all duration-300 shadow-sm">
                    <flux:icon name="phone" class="w-5 h-5" />
                </a>
            </div>
        </div>
    </div>
</footer>

<!-- Mobile Floating Chat Button -->
<div class="lg:hidden fixed bottom-6 right-6 z-50">
    <flux:button href="/chat" wire:navigate variant="primary" icon="chat-bubble-left-ellipsis"
        class="!bg-[#F9CE04] !text-[#1B287D] !border-0 hover:!bg-[#e0ba03] hover:!text-[#1B287D] rounded-full h-14 w-14 shadow-lg hover:scale-105 transition-transform" />
</div>
@endif