@props([
    'score' => 0,
    'accent' => 'orange',
    'size' => 'md',
])

@php
    $textColor = $accent === 'orange' ? 'text-orange-400' : 'text-white';
    $sizeClasses = $size === 'lg'
        ? 'min-w-[5.5rem] sm:min-w-[7rem] h-16 sm:h-20 text-6xl sm:text-7xl'
        : 'min-w-[4.25rem] sm:min-w-[5.25rem] h-14 sm:h-16 text-5xl sm:text-6xl';
@endphp

<div x-data="{
        current: {{ (int) $score }},
        showingDelta: false,
        deltaText: '',
        animKey: 0,
        timer: null,
        observer: null,
        init() {
            this.observer = new MutationObserver(() => {
                const next = parseInt(this.$el.getAttribute('data-score') || '0', 10);
                if (Number.isNaN(next) || next === this.current) {
                    return;
                }

                const delta = next - this.current;
                this.current = next;
                clearTimeout(this.timer);

                if (delta > 0) {
                    this.deltaText = '+' + delta;
                    this.showingDelta = true;
                    this.animKey++;
                    this.timer = setTimeout(() => {
                        this.showingDelta = false;
                        this.animKey++;
                    }, 650);
                } else {
                    this.showingDelta = false;
                    this.animKey++;
                }
            });
            this.observer.observe(this.$el, { attributes: true, attributeFilter: ['data-score'] });
        },
        destroy() {
            clearTimeout(this.timer);
            if (this.observer) this.observer.disconnect();
        }
     }"
     data-score="{{ (int) $score }}"
     {{ $attributes->merge(['class' => 'inline-flex items-center justify-center select-none']) }}>
    <span class="sr-only">{{ (int) $score }}</span>

    <div wire:ignore class="{{ $sizeClasses }} inline-flex items-center justify-center font-display font-extrabold italic leading-none tabular-nums">
        <template x-if="showingDelta">
            <span :key="'delta-' + animKey"
                  class="score-burst-in rounded-xl bg-gradient-to-r from-orange-500 to-amber-400 px-3 py-1 text-slate-950 shadow-[0_0_24px_rgba(249,115,22,0.65)]"
                  x-text="deltaText"></span>
        </template>

        <template x-if="!showingDelta">
            <span :key="'score-' + animKey"
                  class="score-burst-in {{ $textColor }}"
                  x-text="current">{{ (int) $score }}</span>
        </template>
    </div>
</div>
