<x-layouts.landing title="AI Assistant PMB STMIK Bandung - Pusat Layanan Informasi Pendaftaran">
    <div class="space-y-10 pb-16">
        <!-- Hero Section -->
        <section>
            <div class="bg-[#1B287D] rounded-3xl p-8 lg:p-14 flex flex-col md:flex-row items-center justify-between text-white overflow-hidden relative shadow-lg border border-blue-900/50 min-h-[320px]">
                
                <!-- Right Side Fade Background Image -->
                <div class="absolute right-0 top-0 bottom-0 w-full md:w-1/2 lg:w-3/5 pointer-events-none overflow-hidden">
                    <img src="{{ asset('images/stmik.jpg') }}" alt="Kampus STMIK Bandung" class="w-full h-full object-cover object-center opacity-30 md:opacity-40">
                    <div class="absolute inset-0 bg-gradient-to-r from-[#1B287D] via-[#1B287D]/75 to-transparent"></div>
                    <div class="absolute inset-0 bg-gradient-to-t from-[#1B287D]/50 via-transparent to-[#1B287D]/30"></div>
                </div>

                <!-- Left Content Area -->
                <div class="relative z-10 max-w-2xl space-y-6">
                    <h1 class="text-3xl md:text-4xl lg:text-5xl font-extrabold tracking-tight leading-tight text-white">
                        Layanan Informasi PMB <br class="hidden lg:block" />
                        <span class="text-[#F9CE04]">STMIK Bandung</span>
                    </h1>

                    <p class="text-base md:text-lg text-[#BCC3FF] leading-relaxed max-w-xl">
                        Tanyakan apa saja seputar pendaftaran, rincian biaya kuliah, beasiswa, dan program studi secara cepat, akurat, dan interaktif bersama AI Assistant kami.
                    </p>

                    <div class="flex flex-col sm:flex-row gap-4 pt-2">
                        <flux:button variant="primary"
                            class="!bg-[#F9CE04] !text-[#1B287D] !border-[#F9CE04] hover:!bg-[#e0ba03] hover:!text-[#1B287D] px-8 py-3.5 h-auto text-base rounded-xl shadow-md font-bold w-full sm:w-auto"
                            href="https://pmb.stmik-bandung.ac.id" target="_blank">
                            Daftar PMB Sekarang
                            <flux:icon name="arrow-right" class="w-5 h-5 ml-2" />
                        </flux:button>

                        <flux:button href="{{ route('chat') }}" wire:navigate
                            class="lg:hidden !bg-white/15 backdrop-blur-md !text-white !border border-white/30 hover:!bg-white/25 px-6 py-3.5 h-auto text-base rounded-xl font-bold w-full flex items-center justify-center gap-2">
                            <flux:icon name="sparkles" class="w-5 h-5 text-[#F9CE04]" />
                            <span>Tanya AI Chatbot</span>
                        </flux:button>
                    </div>
                </div>
            </div>
        </section>

        <!-- External Website Navigation Section (Below Hero) -->
        <section class="space-y-4">
            <h2 class="text-lg font-bold text-[#1B287D] dark:text-[#F9CE04] tracking-wide uppercase">
                Tautan Website PMB STMIK Bandung
            </h2>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <!-- Beranda -->
                <a href="https://pmb.stmik-bandung.ac.id/" target="_blank"
                    class="flex items-center justify-between p-4 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl shadow-sm hover:shadow-md hover:border-[#1B287D] dark:hover:border-[#F9CE04] hover:-translate-y-0.5 transition-all group">
                    <span class="font-bold text-zinc-800 dark:text-zinc-100 group-hover:text-[#1B287D] dark:group-hover:text-[#F9CE04] transition-colors">Beranda</span>
                    <div class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-zinc-800 group-hover:bg-[#1B287D] group-hover:text-[#F9CE04] text-[#1B287D] dark:text-indigo-400 flex items-center justify-center transition-colors">
                        <flux:icon name="arrow-right" class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" />
                    </div>
                </a>

                <!-- FAQ -->
                <a href="https://pmb.stmik-bandung.ac.id/faq" target="_blank"
                    class="flex items-center justify-between p-4 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl shadow-sm hover:shadow-md hover:border-[#1B287D] dark:hover:border-[#F9CE04] hover:-translate-y-0.5 transition-all group">
                    <span class="font-bold text-zinc-800 dark:text-zinc-100 group-hover:text-[#1B287D] dark:group-hover:text-[#F9CE04] transition-colors">FAQ</span>
                    <div class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-zinc-800 group-hover:bg-[#1B287D] group-hover:text-[#F9CE04] text-[#1B287D] dark:text-indigo-400 flex items-center justify-center transition-colors">
                        <flux:icon name="arrow-right" class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" />
                    </div>
                </a>

                <!-- Biaya -->
                <a href="https://pmb.stmik-bandung.ac.id/pricing" target="_blank"
                    class="flex items-center justify-between p-4 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl shadow-sm hover:shadow-md hover:border-[#1B287D] dark:hover:border-[#F9CE04] hover:-translate-y-0.5 transition-all group">
                    <span class="font-bold text-zinc-800 dark:text-zinc-100 group-hover:text-[#1B287D] dark:group-hover:text-[#F9CE04] transition-colors">Biaya</span>
                    <div class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-zinc-800 group-hover:bg-[#1B287D] group-hover:text-[#F9CE04] text-[#1B287D] dark:text-indigo-400 flex items-center justify-center transition-colors">
                        <flux:icon name="arrow-right" class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" />
                    </div>
                </a>

                <!-- Hubungi Kami -->
                <a href="https://pmb.stmik-bandung.ac.id/contact" target="_blank"
                    class="flex items-center justify-between p-4 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl shadow-sm hover:shadow-md hover:border-[#1B287D] dark:hover:border-[#F9CE04] hover:-translate-y-0.5 transition-all group">
                    <span class="font-bold text-zinc-800 dark:text-zinc-100 group-hover:text-[#1B287D] dark:group-hover:text-[#F9CE04] transition-colors">Hubungi Kami</span>
                    <div class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-zinc-800 group-hover:bg-[#1B287D] group-hover:text-[#F9CE04] text-[#1B287D] dark:text-indigo-400 flex items-center justify-center transition-colors">
                        <flux:icon name="arrow-right" class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" />
                    </div>
                </a>
            </div>
        </section>
    </div>
</x-layouts.landing>
