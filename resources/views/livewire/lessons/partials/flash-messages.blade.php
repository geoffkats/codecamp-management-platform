{{-- ── Standard session toasts ──────────────────────────────────────── --}}
@if(session()->has('message'))
<div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
     x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
     x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
     class="fixed bottom-5 right-5 z-50 flex items-center gap-3 bg-green-600 text-white px-5 py-3 rounded-2xl shadow-xl">
    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
    </svg>
    <span class="text-sm font-semibold">{{ session('message') }}</span>
</div>
@endif

@if(session()->has('error'))
<div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 7000)"
     x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
     x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
     class="fixed bottom-5 right-5 z-50 flex items-center gap-3 bg-red-600 text-white px-5 py-3 rounded-2xl shadow-xl">
    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
    </svg>
    <span class="text-sm font-semibold">{{ session('error') }}</span>
</div>
@endif

{{-- ── Lesson-completion XP burst + confetti ───────────────────────── --}}
<div
    x-data="{
        show: false,
        xp: 0,
        title: '',
        init() {
            this.$wire.on('lesson-completed', ({ xp, title }) => {
                this.xp    = xp;
                this.title = title;
                this.show  = true;
                this.launchConfetti();
                setTimeout(() => this.show = false, 5000);
            });
        },
        launchConfetti() {
            const canvas = document.getElementById('confetti-canvas');
            if (!canvas) return;
            canvas.style.display = 'block';
            const ctx = canvas.getContext('2d');
            canvas.width  = window.innerWidth;
            canvas.height = window.innerHeight;

            const colours = ['#f97316','#fb923c','#fbbf24','#4ade80','#60a5fa','#a78bfa','#f472b6'];
            const pieces  = Array.from({ length: 120 }, () => ({
                x: Math.random() * canvas.width,
                y: Math.random() * -canvas.height,
                w: 8 + Math.random() * 8,
                h: 6 + Math.random() * 6,
                rot: Math.random() * 360,
                rotV: (Math.random() - 0.5) * 8,
                vy: 2 + Math.random() * 5,
                vx: (Math.random() - 0.5) * 4,
                colour: colours[Math.floor(Math.random() * colours.length)],
                opacity: 1,
            }));

            let frame;
            const draw = () => {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                let alive = false;
                pieces.forEach(p => {
                    p.y   += p.vy;
                    p.x   += p.vx;
                    p.rot += p.rotV;
                    if (p.y > canvas.height * 0.6) p.opacity = Math.max(0, p.opacity - 0.025);
                    if (p.opacity > 0) alive = true;
                    ctx.save();
                    ctx.globalAlpha = p.opacity;
                    ctx.fillStyle   = p.colour;
                    ctx.translate(p.x + p.w / 2, p.y + p.h / 2);
                    ctx.rotate(p.rot * Math.PI / 180);
                    ctx.fillRect(-p.w / 2, -p.h / 2, p.w, p.h);
                    ctx.restore();
                });
                if (alive) { frame = requestAnimationFrame(draw); }
                else { canvas.style.display = 'none'; ctx.clearRect(0, 0, canvas.width, canvas.height); }
            };
            frame = requestAnimationFrame(draw);
        }
    }"
>
    {{-- Full-screen confetti canvas --}}
    <canvas id="confetti-canvas"
            style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;pointer-events:none;z-index:9998;">
    </canvas>

    {{-- XP toast --}}
    <div
        x-show="show"
        x-transition:enter="transition ease-out duration-400"
        x-transition:enter-start="opacity-0 scale-75 translate-y-6"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-90"
        class="fixed bottom-6 right-6 z-[9999] flex items-center gap-4 bg-gradient-to-r from-orange-500 to-orange-600 text-white px-6 py-4 rounded-2xl shadow-2xl"
    >
        <div class="text-3xl leading-none">🎉</div>
        <div>
            <p class="text-sm font-bold leading-tight">Lesson Complete!</p>
            <p class="text-xs text-orange-100 mt-0.5 max-w-[200px] truncate" x-text="title"></p>
        </div>
        <div class="flex flex-col items-center bg-white/25 rounded-xl px-3 py-2 min-w-[56px] text-center">
            <span class="text-xl font-extrabold leading-tight" x-text="'+' + xp"></span>
            <span class="text-[10px] text-orange-100 font-semibold">XP</span>
        </div>
        <button @click="show = false" class="ml-1 p-1 rounded-lg hover:bg-white/20 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
</div>
