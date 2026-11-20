@props(['tenant', 'size' => 'md'])

@php
    $sizeClasses = match ($size) {
        'lg' => 'h-20 w-20',
        'sm' => 'h-12 w-12',
        default => 'h-16 w-16',
    };
    $logoUrl = $tenant->logoUrl();
    $initials = collect(explode(' ', $tenant->name))
        ->reject(fn (string $word): bool => in_array(strtolower($word), ['barangay', 'brgy', 'brgy.'], true))
        ->map(fn (string $word): string => strtoupper(substr($word, 0, 1)))
        ->take(2)
        ->implode('') ?: strtoupper(substr($tenant->name, 0, 2));
@endphp

<div {{ $attributes->merge(['class' => "{$sizeClasses} shrink-0 flex items-center justify-center"]) }}>
    @if ($logoUrl)
        <img src="{{ $logoUrl }}"
             alt="{{ $tenant->name }} League Logo"
             class="h-full w-full object-contain drop-shadow-[0_6px_14px_rgba(0,0,0,0.6)] transition-transform duration-300 group-hover:scale-105">
    @else
        <svg viewBox="0 0 120 120" class="h-full w-full drop-shadow-[0_6px_14px_rgba(0,0,0,0.6)] transition-transform duration-300 group-hover:scale-105" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M60 8L104 22V58C104 86 84 104 60 113C36 104 16 86 16 58V22L60 8Z" fill="#0d1833" stroke="#f97316" stroke-width="4"/>
            <path d="M60 14L98 26V58C98 81 80 97 60 105C40 97 22 81 22 58V26L60 14Z" stroke="#fbbf24" stroke-opacity="0.35" stroke-width="1.5"/>
            <text x="60" y="56" text-anchor="middle" fill="#ffffff" font-family="Arial, sans-serif" font-weight="900" font-size="26" letter-spacing="1">{{ $initials }}</text>
            <circle cx="60" cy="84" r="14" fill="#ea580c" stroke="#091022" stroke-width="2"/>
            <path d="M46 84H74M60 70V98M50 74C55 79 55 89 50 94M70 74C65 79 65 89 70 94" stroke="#091022" stroke-width="1.6" stroke-linecap="round"/>
        </svg>
    @endif
</div>
