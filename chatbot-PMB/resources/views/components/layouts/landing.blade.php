<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    @include('partials.head')
</head>
<body class="bg-[#F5F5F5] dark:bg-zinc-950 text-zinc-900 dark:text-zinc-100 font-sans antialiased min-h-screen flex flex-col selection:bg-[#F9CE04] selection:text-[#1B287D]">

    <!-- Header Navigation -->
    @include('partials.header')

    <!-- Main Container Layout (Split-screen on desktop: Left content stream + Footer, Right fixed Chatbot sidebar) -->
    <div class="flex-1 w-full">
        <div class="max-w-[1280px] mx-auto flex flex-col lg:flex-row min-h-[calc(100vh-4rem)]">
            
            <!-- Left Area: Main Content Stream & Footer -->
            <div class="w-full lg:w-2/3 flex flex-col">
                <main class="flex-1 px-4 sm:px-6 lg:px-8 py-8 space-y-12">
                    {{ $slot }}
                </main>

                <!-- Footer (Positioned on the left column stream as per footer.blade.php specs) -->
                @include('partials.footer')
            </div>

            <!-- Right Area: Fixed Chatbot Sidebar -->
            <div id="chatbot-sidebar" class="w-full lg:w-1/3 lg:sticky lg:top-16 lg:h-[calc(100vh-4rem)] shrink-0">
                <livewire:chat-widget />
            </div>

        </div>
    </div>

    @fluxScripts
</body>
</html>
