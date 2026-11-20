@props(['size' => 'md'])

@php
    $iconClasses = $size === 'sm' ? 'h-9 w-9' : 'h-11 w-11';
@endphp

<div {{ $attributes->merge(['class' => 'inline-flex items-center gap-3']) }}>
    <span class="relative inline-flex {{ $iconClasses }} items-center justify-center shrink-0">
        <svg viewBox="0 0 64 64" class="h-full w-full drop-shadow-[0_0_14px_rgba(255,115,0,0.65)]" fill="none" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <linearGradient id="bh-shield-grad" x1="8" y1="4" x2="56" y2="60" gradientUnits="userSpaceOnUse">
                    <stop offset="0%" stop-color="#ff8c00"/>
                    <stop offset="55%" stop-color="#ff5100"/>
                    <stop offset="100%" stop-color="#fbbf24"/>
                </linearGradient>
                <linearGradient id="bh-ball-grad" x1="20" y1="12" x2="44" y2="36" gradientUnits="userSpaceOnUse">
                    <stop offset="0%" stop-color="#fbbf24"/>
                    <stop offset="100%" stop-color="#ea580c"/>
                </linearGradient>
            </defs>
            <path d="M32 4L56 13V31C56 46.5 45.5 56 32 60C18.5 56 8 46.5 8 31V13L32 4Z" fill="#06080d" stroke="url(#bh-shield-grad)" stroke-width="3"/>
            <circle cx="32" cy="24" r="12" fill="url(#bh-ball-grad)" stroke="#fef3c7" stroke-width="1.2"/>
            <path d="M20 24H44M32 12V36M23.5 15.5C28 20 28 28 23.5 32.5M40.5 15.5C36 20 36 28 40.5 32.5" stroke="#06080d" stroke-width="1.5" stroke-linecap="round"/>
            <rect x="14" y="33" width="36" height="16" rx="4" fill="#0c1120" stroke="url(#bh-shield-grad)" stroke-width="1.5"/>
            <text x="32" y="45" text-anchor="middle" fill="#ffffff" font-family="Arial, sans-serif" font-weight="900" font-style="italic" font-size="13" letter-spacing="0.5">BH</text>
        </svg>
    </span>
    <div class="leading-none">
        <div class="font-display text-xl sm:text-2xl font-extrabold italic tracking-tight text-white uppercase">
            Barangay<span class="bg-gradient-to-r from-orange-400 to-amber-300 bg-clip-text text-transparent">Hoops</span>
        </div>
        <div class="text-[10px] font-semibold uppercase tracking-[0.22em] text-slate-400">
            One League &bull; One Community
        </div>
    </div>
</div>
