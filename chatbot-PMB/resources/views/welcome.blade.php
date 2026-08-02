<x-layouts.landing title="AI Assistant PMB STMIK Bandung - Pusat Layanan Informasi Pendaftaran">
    <div class="space-y-20 pb-20">
        <!-- Hero Section -->
        <section>
            <div class="bg-[#1B287D] rounded-3xl p-8 lg:p-14 flex flex-col md:flex-row items-center justify-between text-white overflow-hidden relative shadow-lg border border-blue-900/50">
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

        <!-- Layanan Unggulan -->
        <section>
            <h2 class="text-3xl font-bold text-[#1B287D] dark:text-[#F9CE04] mb-8">Layanan Unggulan Kami</h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Card 1 -->
                <flux:card
                    class="border border-zinc-200 dark:border-zinc-800 shadow-sm hover:shadow-lg hover:-translate-y-1 hover:border-[#1B287D] dark:hover:border-[#F9CE04] hover:bg-indigo-50/50 dark:hover:bg-zinc-800/50 transition-all duration-300 bg-white dark:bg-zinc-900 rounded-2xl p-6 cursor-pointer">
                    <div
                        class="w-14 h-14 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center text-[#1B287D] dark:text-indigo-400 mb-6">
                        <flux:icon name="light-bulb" class="w-7 h-7" />
                    </div>
                    <flux:heading size="lg" class="mb-3 text-zinc-900 dark:text-white font-semibold text-xl">Smart Campus</flux:heading>
                    <flux:text class="text-zinc-600 dark:text-zinc-400">
                        Mengkombinasikan dan Mengimplementasikan proses belajar dalam lingkungan kampus dengan menggunakan Teknologi Informasi canggih.
                    </flux:text>
                </flux:card>

                <!-- Card 2 -->
                <flux:card
                    class="border border-zinc-200 dark:border-zinc-800 shadow-sm hover:shadow-lg hover:-translate-y-1 hover:border-[#1B287D] dark:hover:border-[#F9CE04] hover:bg-indigo-50/50 dark:hover:bg-zinc-800/50 transition-all duration-300 bg-white dark:bg-zinc-900 rounded-2xl p-6 cursor-pointer">
                    <div
                        class="w-14 h-14 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center text-[#1B287D] dark:text-indigo-400 mb-6">
                        <flux:icon name="wallet" class="w-7 h-7" />
                    </div>
                    <flux:heading size="lg" class="mb-3 text-zinc-900 dark:text-white font-semibold text-xl">Biaya Terjangkau</flux:heading>
                    <flux:text class="text-zinc-600 dark:text-zinc-400">
                        Kuliah di STMIK Bandung terdapat biaya kuliah yang dapat diangsur.
                    </flux:text>
                </flux:card>

                <!-- Card 3 -->
                <flux:card
                    class="border border-zinc-200 dark:border-zinc-800 shadow-sm hover:shadow-lg hover:-translate-y-1 hover:border-[#1B287D] dark:hover:border-[#F9CE04] hover:bg-indigo-50/50 dark:hover:bg-zinc-800/50 transition-all duration-300 bg-white dark:bg-zinc-900 rounded-2xl p-6 cursor-pointer">
                    <div
                        class="w-14 h-14 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center text-[#1B287D] dark:text-indigo-400 mb-6">
                        <flux:icon name="academic-cap" class="w-7 h-7" />
                    </div>
                    <flux:heading size="lg" class="mb-3 text-zinc-900 dark:text-white font-semibold text-xl">Jaringan Alumni</flux:heading>
                    <flux:text class="text-zinc-600 dark:text-zinc-400">
                        Beasiswa Berprestasi, Ikatan Dinas, Atlet, Tidak Mampu, dan lain lain.
                    </flux:text>
                </flux:card>
            </div>
        </section>

        <!-- Mengenal PMB Universitas -->
        <section class="bg-white dark:bg-zinc-900 rounded-3xl border border-zinc-200 dark:border-zinc-800 p-8 lg:p-12 shadow-sm">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div>
                    <h2 class="text-3xl font-bold text-[#1B287D] dark:text-[#F9CE04] mb-6">Mengenal STMIK Bandung</h2>
                    <div class="space-y-4 text-lg text-zinc-600 dark:text-zinc-300">
                        <p>
                            STMIK Bandung merupakan STMIK pertama di Jawa Barat dan pelopor pendidikan tinggi inforamtika swasta dengan fokus untuk mencetak tenaga profesional dan technopreneur IT.
                        </p>
                    </div>
                </div>
                <div class="relative">
                    <div class="aspect-4/3 rounded-2xl overflow-hidden shadow-lg bg-zinc-200 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700">
                        <img src="{{ asset('images/stmik.jpg') }}" alt="Kampus Utama STMIK Bandung" class="w-full h-full object-cover">
                    </div>
                </div>
            </div>
        </section>
    </div>
</x-layouts.landing>
