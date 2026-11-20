@props(['poster'])

<div x-data="{
        poster: @js($poster),
        init() {
            this.$nextTick(() => this.drawPoster());
        },
        drawPoster() {
            const canvas = this.$refs.bpgCanvas;
            if (!canvas || !this.poster) return;
            const ctx = canvas.getContext('2d');
            const w = 1080;
            const h = 1350;

            // Obsidian Background
            const bgGrad = ctx.createLinearGradient(0, 0, w, h);
            bgGrad.addColorStop(0, '#050811');
            bgGrad.addColorStop(0.5, '#0c1733');
            bgGrad.addColorStop(1, '#06080d');
            ctx.fillStyle = bgGrad;
            ctx.fillRect(0, 0, w, h);

            // Orange Arena Spotlight
            const spot = ctx.createRadialGradient(w * 0.5, h * 0.34, 40, w * 0.5, h * 0.34, 600);
            spot.addColorStop(0, 'rgba(249, 115, 22, 0.38)');
            spot.addColorStop(1, 'rgba(249, 115, 22, 0)');
            ctx.fillStyle = spot;
            ctx.fillRect(0, 0, w, h);

            // Top Accent Stripe
            ctx.fillStyle = this.poster.primary_color || '#f97316';
            ctx.fillRect(0, 0, w, 18);

            // Barangay & Tournament Header
            ctx.textAlign = 'center';
            ctx.fillStyle = '#fb923c';
            ctx.font = 'bold 34px Poppins, sans-serif';
            ctx.fillText((this.poster.barangay || '').toUpperCase(), w / 2, 95);

            ctx.fillStyle = '#cbd5e1';
            ctx.font = '600 28px Poppins, sans-serif';
            ctx.fillText(this.poster.tournament || '', w / 2, 140);

            // Award Banner
            ctx.fillStyle = '#f97316';
            ctx.fillRect(210, 180, 660, 66);
            ctx.fillStyle = '#ffffff';
            ctx.font = 'italic 800 42px Barlow Condensed, sans-serif';
            ctx.fillText('BEST PLAYER OF THE GAME', w / 2, 228);

            // Oversized Jersey Watermark
            ctx.fillStyle = 'rgba(255, 255, 255, 0.08)';
            ctx.font = 'italic 900 320px Barlow Condensed, sans-serif';
            ctx.fillText('#' + this.poster.jersey_number, w / 2, 560);

            // Player Jersey Badge Circle
            ctx.beginPath();
            ctx.arc(w / 2, 470, 145, 0, Math.PI * 2);
            ctx.fillStyle = this.poster.primary_color || '#ea580c';
            ctx.fill();
            ctx.lineWidth = 10;
            ctx.strokeStyle = '#fbbf24';
            ctx.stroke();

            ctx.fillStyle = '#ffffff';
            ctx.font = 'italic 900 120px Barlow Condensed, sans-serif';
            ctx.fillText('#' + this.poster.jersey_number, w / 2, 510);

            // Player Name & Team
            ctx.fillStyle = '#ffffff';
            ctx.font = 'italic 900 76px Barlow Condensed, sans-serif';
            ctx.fillText((this.poster.player_name || '').toUpperCase(), w / 2, 705);

            ctx.fillStyle = '#fbbf24';
            ctx.font = '700 32px Poppins, sans-serif';
            ctx.fillText((this.poster.team_name || '').toUpperCase() + '  •  ' + (this.poster.position || ''), w / 2, 755);

            // Stat Grid Box
            ctx.fillStyle = 'rgba(10, 16, 32, 0.9)';
            ctx.strokeStyle = 'rgba(249, 115, 22, 0.55)';
            ctx.lineWidth = 3;
            ctx.fillRect(80, 815, 920, 220);
            ctx.strokeRect(80, 815, 920, 220);

            const stats = [
                { label: 'PTS', val: this.poster.pts },
                { label: 'REB', val: this.poster.reb },
                { label: 'AST', val: this.poster.ast },
                { label: 'STL', val: this.poster.stl },
                { label: 'BLK', val: this.poster.blk },
                { label: 'EFF', val: this.poster.eff }
            ];
            const colWidth = 920 / stats.length;
            stats.forEach((s, i) => {
                const cx = 80 + colWidth * i + colWidth / 2;
                ctx.fillStyle = s.label === 'EFF' ? '#fbbf24' : '#ffffff';
                ctx.font = 'italic 800 76px Barlow Condensed, sans-serif';
                ctx.fillText(String(s.val ?? 0), cx, 930);

                ctx.fillStyle = '#94a3b8';
                ctx.font = '700 24px Poppins, sans-serif';
                ctx.fillText(s.label, cx, 985);
            });

            // Final Score Strip
            ctx.fillStyle = '#cbd5e1';
            ctx.font = '600 26px Poppins, sans-serif';
            ctx.fillText('FINAL SCORE', w / 2, 1125);

            ctx.fillStyle = '#ffffff';
            ctx.font = 'italic 800 50px Barlow Condensed, sans-serif';
            ctx.fillText(
                this.poster.home_team + '  ' + this.poster.home_score + '  -  ' + this.poster.away_score + '  ' + this.poster.away_team,
                w / 2,
                1188
            );

            // Footer Brand
            ctx.fillStyle = '#f97316';
            ctx.font = '700 24px Poppins, sans-serif';
            ctx.fillText('POWERED BY BARANGAYHOOPS', w / 2, 1290);
        },
        downloadPng() {
            const canvas = this.$refs.bpgCanvas;
            if (!canvas) return;
            const link = document.createElement('a');
            const safeName = (this.poster.player_name || 'bpg-poster').toLowerCase().replace(/[^a-z0-9]+/g, '-');
            link.download = `bpg-${safeName}.png`;
            link.href = canvas.toDataURL('image/png');
            link.click();
        }
     }"
     x-effect="poster = @js($poster); $nextTick(() => drawPoster())"
     class="arena-card rounded-2xl border-orange-500/40 p-6 space-y-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <div class="text-xs font-bold uppercase tracking-wider text-orange-400">Facebook-Ready BPG Graphic (1080 &times; 1350)</div>
            <h3 class="font-display text-2xl font-bold text-white">Best Player of the Game Poster</h3>
        </div>
        <div class="flex items-center gap-2.5">
            <button @click="downloadPng()" type="button"
                    class="btn-hoops-primary rounded-xl px-4 py-2 text-xs font-bold text-white cursor-pointer">
                Download BPG Poster (PNG)
            </button>
            @if ($attributes->has('on-close'))
                <button wire:click="{{ $attributes->get('on-close') }}" type="button"
                        class="rounded-xl border border-white/10 bg-white/5 px-3.5 py-2 text-xs font-semibold text-slate-200 hover:bg-white/10 cursor-pointer">
                    Close
                </button>
            @endif
        </div>
    </div>

    <div class="flex justify-center">
        <canvas x-ref="bpgCanvas" width="1080" height="1350"
                class="w-full max-w-sm rounded-2xl border border-white/15 shadow-2xl"></canvas>
    </div>
</div>
