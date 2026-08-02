<!-- Footer -->
<footer class="bg-white border-t border-zinc-200 dark:bg-zinc-900 dark:border-zinc-800 py-12 mt-auto">
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-4 gap-8">
        <!-- Col 1: Brand & Contact -->
        <div>
            <div class="mb-4">
                <img src="{{ asset('images/logoSTMIK.png') }}" alt="Logo STMIK Bandung"
                    class="h-10 w-auto object-contain">
            </div>
            <div class="space-y-2 text-sm text-zinc-600 dark:text-zinc-400">
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
                    <a href="mailto:marketing@stmik-bandung.ac.id" class="hover:underline hover:text-[#F9CE04]">marketing@stmik-bandung.ac.id</a>
                </div>
            </div>
        </div>

        <!-- Col 2: STMIK Bandung Sub Links -->
        <div>
            <h3 class="font-semibold text-sm text-[#1B287D] dark:text-[#F9CE04] mb-4 uppercase tracking-wider">STMIK Bandung</h3>
            <ul class="space-y-2.5 text-sm text-zinc-600 dark:text-zinc-400">
                <li class="flex items-center gap-2 group">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#1B287D] group-hover:bg-[#F9CE04] transition-colors"></span>
                    <a href="https://stmik-bandung.ac.id/" target="_blank" class="hover:text-[#F9CE04] transition-colors">Situs Utama</a>
                </li>
                <li class="flex items-center gap-2 group">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#1B287D] group-hover:bg-[#F9CE04] transition-colors"></span>
                    <a href="https://pmb.stmik-bandung.ac.id/" target="_blank" class="hover:text-[#F9CE04] transition-colors">Pendaftaran PMB STMIK Bandung</a>
                </li>
                <li class="flex items-center gap-2 group">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#1B287D] group-hover:bg-[#F9CE04] transition-colors"></span>
                    <a href="https://journal.stmik-bandung.ac.id/index.php/JurnalTI" target="_blank" class="hover:text-[#F9CE04] transition-colors">Jurnal</a>
                </li>
                <li class="flex items-center gap-2 group">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#1B287D] group-hover:bg-[#F9CE04] transition-colors"></span>
                    <a href="https://stmik-bandung.merdeka.academy/" target="_blank" class="hover:text-[#F9CE04] transition-colors">Mata Kuliah</a>
                </li>
            </ul>
        </div>

        <!-- Col 3: Tautan Sub Links -->
        <div>
            <h3 class="font-semibold text-sm text-[#1B287D] dark:text-[#F9CE04] mb-4 uppercase tracking-wider">Tautan</h3>
            <ul class="space-y-2.5 text-sm text-zinc-600 dark:text-zinc-400">
                <li class="flex items-center gap-2 group">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#1B287D] group-hover:bg-[#F9CE04] transition-colors"></span>
                    <a href="/" wire:navigate class="hover:text-[#F9CE04] transition-colors">Home</a>
                </li>
                <li class="flex items-center gap-2 group">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#1B287D] group-hover:bg-[#F9CE04] transition-colors"></span>
                    <a href="{{ route('chat') }}" wire:navigate class="hover:text-[#F9CE04] transition-colors">Chatbot</a>
                </li>
            </ul>
        </div>

        <!-- Col 4: Sosial Media -->
        <div>
            <h3 class="font-semibold text-sm text-[#1B287D] dark:text-[#F9CE04] mb-4 uppercase tracking-wider">Sosial Media</h3>
            <div class="flex space-x-3 items-center">
                <a href="https://www.youtube.com/@stmikbandung113" target="_blank"
                    class="w-10 h-10 flex items-center justify-center rounded-xl bg-[#1B287D] text-white hover:bg-[#F9CE04] hover:text-[#1B287D] hover:-translate-y-1 transition-all duration-300 shadow-sm">
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="M2.5 17a24.12 24.12 0 0 1 0-10 2 2 0 0 1 1.4-1.4 49.56 49.56 0 0 1 16.2 0A2 2 0 0 1 21.5 7a24.12 24.12 0 0 1 0 10 2 2 0 0 1-1.4 1.4 49.55 49.55 0 0 1-16.2 0A2 2 0 0 1 2.5 17"></path>
                        <path d="m10 15 5-3-5-3z"></path>
                    </svg>
                </a>
                <a href="https://www.facebook.com/share/NGs46FRHeTMeBZbX/?mibextid=qi2Omg" target="_blank"
                    class="w-10 h-10 flex items-center justify-center rounded-xl bg-[#1B287D] text-white hover:bg-[#F9CE04] hover:text-[#1B287D] hover:-translate-y-1 transition-all duration-300 shadow-sm">
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path>
                    </svg>
                </a>
                <a href="https://www.instagram.com/stmikbandung/" target="_blank"
                    class="w-10 h-10 flex items-center justify-center rounded-xl bg-[#1B287D] text-white hover:bg-[#F9CE04] hover:text-[#1B287D] hover:-translate-y-1 transition-all duration-300 shadow-sm">
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <rect width="20" height="20" x="2" y="2" rx="5" ry="5"></rect>
                        <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
                        <line x1="17.5" x2="17.51" y1="6.5" y2="6.5"></line>
                    </svg>
                </a>
                <a href="{{ config('services.whatsapp_admin', 'https://wa.me/628112342113') }}" target="_blank"
                    class="w-10 h-10 flex items-center justify-center rounded-xl bg-[#1B287D] text-white hover:bg-[#F9CE04] hover:text-[#1B287D] hover:-translate-y-1 transition-all duration-300 shadow-sm">
                    <flux:icon name="phone" class="w-5 h-5" />
                </a>
            </div>
        </div>
    </div>
</footer>

@if(!request()->is('chat'))
<!-- Mobile Floating Chat Button -->
<div class="lg:hidden fixed bottom-6 right-6 z-50">
    <flux:button href="{{ route('chat') }}" wire:navigate variant="primary" icon="chat-bubble-left-ellipsis"
        class="!bg-[#F9CE04] !text-[#1B287D] !border-0 hover:!bg-[#e0ba03] hover:!text-[#1B287D] rounded-full h-14 w-14 shadow-lg hover:scale-105 transition-transform" />
</div>
@endif
