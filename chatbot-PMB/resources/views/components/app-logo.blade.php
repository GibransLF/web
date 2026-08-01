@props([
    'sidebar' => false,
])

@if($sidebar)
    <a href="{{ $attributes->get('href', route('dashboard')) }}" {{ $attributes->merge(['class' => 'h-10 min-w-0 flex items-center gap-2.5 px-2 in-data-flux-sidebar-collapsed-desktop:w-10 in-data-flux-sidebar-collapsed-desktop:px-0 in-data-flux-sidebar-collapsed-desktop:justify-center']) }} data-flux-sidebar-brand>
        <img src="{{ asset('images/logoSTMIK.png') }}" alt="Logo STMIK Bandung" class="h-8 w-auto max-h-8 object-contain shrink-0" />
        <div class="border-l border-white/25 pl-2.5 flex flex-col justify-center in-data-flux-sidebar-collapsed-desktop:hidden">
            <span class="font-extrabold text-white text-xs leading-tight tracking-tight whitespace-nowrap">Chatbot Informasi PMB</span>
            <span class="text-[10px] font-semibold text-[#F9CE04] tracking-wider uppercase whitespace-nowrap">STMIK Bandung</span>
        </div>
    </a>
@else
    <a href="{{ $attributes->get('href', '/') }}" {{ $attributes->merge(['class' => 'inline-flex items-center gap-3.5 hover:opacity-95 transition-opacity']) }}>
        <img src="{{ asset('images/logoSTMIK.png') }}" alt="Logo STMIK Bandung" class="h-10 md:h-11 w-auto object-contain shrink-0" />
        <div class="border-l border-white/25 pl-3.5 py-0.5 flex flex-col justify-center">
            <span class="font-extrabold text-white text-base md:text-lg leading-tight tracking-tight">Chatbot Informasi PMB</span>
            <span class="text-[11px] font-semibold text-[#F9CE04] tracking-wider uppercase">STMIK Bandung</span>
        </div>
    </a>
@endif


