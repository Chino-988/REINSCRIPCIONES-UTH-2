<x-app-layout>
    <div class="px-6 py-6">
        <h1 class="text-2xl font-semibold mb-5">Panel de Caja</h1>

        {{-- Tarjetas: deja tus clases/estilos como las tengas.
             ÚNICO requisito: que el número esté dentro de los <span> con estos ids --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="rounded-xl bg-slate-800/50 border border-slate-800 p-5">
                <div class="text-slate-300 font-semibold">Pendientes</div>
                <div class="text-4xl font-extrabold mt-2"><span id="m-pend">{{ $totalPend }}</span></div>
                <div class="text-xs text-slate-400 mt-1">Incluye pendientes y en proceso.</div>
            </div>

            <div class="rounded-xl bg-slate-800/50 border border-slate-800 p-5">
                <div class="text-slate-300 font-semibold">Validados</div>
                <div class="text-4xl font-extrabold mt-2 text-emerald-400"><span id="m-ok">{{ $totalOk }}</span></div>
                <div class="text-xs text-slate-400 mt-1">Confirmados por Banco/Caja.</div>
            </div>

            <div class="rounded-xl bg-slate-800/50 border border-slate-800 p-5">
                <div class="text-slate-300 font-semibold">Rechazados</div>
                <div class="text-4xl font-extrabold mt-2 text-rose-400"><span id="m-rech">{{ $totalRech }}</span></div>
                <div class="text-xs text-slate-400 mt-1">Referencias inválidas o con discrepancia.</div>
            </div>
        </div>

        <div class="mt-6">
            <a href="{{ route('caja.pendientes') }}" class="inline-flex items-center px-4 py-2 rounded-md bg-emerald-600 text-white hover:bg-emerald-700">
                Ver pendientes
            </a>
            <span class="ml-3 text-sm text-slate-400">Última actualización: <span id="m-last">—</span></span>
        </div>
    </div>

    {{-- Script mínimo para refrescar SIN tocar estilos --}}
    <script>
        (function(){
            const $pend = document.getElementById('m-pend');
            const $ok   = document.getElementById('m-ok');
            const $rech = document.getElementById('m-rech');
            const $last = document.getElementById('m-last');

            function animate($el, to){
                const from = parseInt($el.textContent || '0', 10) || 0;
                const diff = to - from;
                if (diff === 0) { $el.textContent = String(to); return; }

                const steps = 12, dur = 260;
                let i = 0;
                const inc = diff / steps;
                const t = setInterval(() => {
                    i++;
                    const val = i>=steps ? to : Math.round(from + inc*i);
                    $el.textContent = String(val);
                    if (i>=steps) clearInterval(t);
                }, dur/steps);
            }

            async function refresh(){
                try{
                    const res = await fetch('{{ route('caja.metrics') }}', { headers:{'Accept':'application/json'} });
                    if(!res.ok) return;
                    const json = await res.json();
                    if(!json.ok || !json.data) return;

                    animate($pend, json.data.pendientes ?? 0);
                    animate($ok,   json.data.validados  ?? 0);
                    animate($rech, json.data.rechazados ?? 0);
                    $last.textContent = new Date().toLocaleTimeString();
                }catch(e){ /* silencioso */ }
            }

            refresh();                 // primera carga
            setInterval(refresh, 15000); // cada 15s
        })();
    </script>
</x-app-layout>
