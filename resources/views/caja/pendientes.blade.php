<x-app-layout>
    <meta name="csrf-token" content="{{ csrf_token() }}"/>

    <style>
        /* ====== VISIBILIDAD DE TEXTO EN CAMPOS ====== */
        .uth-input{
            background:#ffffff !important;
            color:#0f172a !important;        /* azul oscuro */
            caret-color:#0f172a !important;
            border:1px solid #cbd5e1 !important; /* slate-300 */
            height:44px;
            padding:0 12px;
            border-radius:8px;
            outline:none;
        }
        .uth-input:focus{
            border-color:#059669 !important;      /* emerald-600 */
            box-shadow:0 0 0 3px rgba(5,150,105,.25);
        }
        .uth-input::placeholder{
            color:#64748b !important;             /* slate-500 */
            opacity:1;
        }
        select.uth-input option{ color:#0f172a !important; }
        input[type="date"].uth-input::-webkit-calendar-picker-indicator{
            filter: invert(0);                    /* que se vea en fondo claro */
        }

        /* ====== TOOLBAR (GRID) ====== */
        .toolbar{
            background:rgba(15,23,42,.35);        /* slate-900/35 */
            border:1px solid #1f2937;             /* slate-800 */
            border-radius:14px; padding:14px;
            display:grid; gap:12px;
            grid-template-columns: 1fr;           /* móvil: una columna */
            align-items:end;
        }
        /* Desktop: una fila fija y botones a la derecha */
        @media (min-width: 1024px){
            .toolbar{
                grid-template-columns: 1.6fr 0.8fr 0.7fr 0.7fr auto;
            }
            .fi--btns{ justify-self:end; }
        }
        /* Botonera */
        .btn{
            height:44px; padding:0 14px; border-radius:8px; display:inline-flex; align-items:center;
            font-weight:600; border:1px solid transparent;
        }
        .btn-emerald{ background:#059669; color:#fff; }
        .btn-emerald:hover{ background:#047857; }
        .btn-ghost{ color:#e5e7eb; border-color:#64748b; }
        .btn-ghost:hover{ background:#0f172a; }

        /* ====== TABLA ====== */
        thead th{ text-transform:uppercase; letter-spacing:.06em; font-size:12px; color:#94a3b8; }
        tbody td{ color:#e2e8f0; font-size:15px; }
        .table-wrap{ -webkit-overflow-scrolling:touch; }

    </style>

    <div class="px-6 py-4">
        <h1 class="text-2xl font-semibold mb-4">
            <span class="text-emerald-400">Pagos</span> — Caja
        </h1>

        {{-- Toolbar de filtros (GRID) --}}
        <form method="GET" action="{{ route('caja.pendientes') }}" class="toolbar mb-6">
            <div class="fi fi--q">
                <input
                    type="text" name="q" id="q"
                    value="{{ $q }}"
                    placeholder="Matrícula / nombre / referencia"
                    class="uth-input"
                />
            </div>

            <div class="fi fi--estado">
                @php $opts=['TODOS'=>'Todos','PENDIENTES'=>'Pendientes','EN_PROCESO'=>'En proceso','VALIDADOS'=>'Validados','RECHAZADOS'=>'Rechazados']; @endphp
                <select name="estado" id="estado" class="uth-input">
                    @foreach($opts as $val=>$lbl)
                        <option value="{{ $val }}" @selected($estado===$val)>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>

            <div class="fi fi--desde">
                <input type="date" name="desde" id="desde" value="{{ $desde }}" class="uth-input"/>
            </div>

            <div class="fi fi--hasta">
                <input type="date" name="hasta" id="hasta" value="{{ $hasta }}" class="uth-input"/>
            </div>

            <div class="fi fi--btns">
                <button class="btn btn-emerald" type="submit">FILTRAR</button>
                <a href="{{ route('caja.pendientes') }}" class="btn btn-ghost">LIMPIAR</a>
            </div>
        </form>

        {{-- Tabla --}}
        <div class="bg-slate-900/40 border border-slate-800 rounded-xl overflow-hidden">
            <div class="overflow-x-auto table-wrap">
                <table class="min-w-full">
                    <thead class="bg-slate-900/60">
                        <tr class="text-left">
                            <th class="px-4 py-3">#</th>
                            <th class="px-4 py-3">Fecha</th>
                            <th class="px-4 py-3">Matrícula</th>
                            <th class="px-4 py-3">Alumno</th>
                            <th class="px-4 py-3">Referencia</th>
                            <th class="px-4 py-3">Comprobante</th>
                            <th class="px-4 py-3">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        @forelse($pagos as $p)
                            @php
                                $estadoFila = strtoupper($p->estatus_caja ?? 'PENDIENTE');
                                $classMap = [
                                    'VALIDADO'   => 'bg-emerald-600 text-white shadow-sm shadow-emerald-900/30 border border-emerald-400/30',
                                    'RECHAZADO'  => 'bg-rose-600 text-white shadow-sm shadow-rose-900/30 border border-rose-400/30',
                                    'EN_PROCESO' => 'bg-amber-500 text-slate-900 shadow-sm shadow-amber-900/20 border border-amber-400/40',
                                    'PENDIENTE'  => 'bg-slate-600 text-white border border-slate-500/40',
                                ];
                                $chipClass = $classMap[$estadoFila] ?? $classMap['PENDIENTE'];
                            @endphp
                            <tr class="hover:bg-slate-900/30">
                                <td class="px-4 py-3">#{{ $p->id }}</td>
                                <td class="px-4 py-3">{{ optional($p->created_at)->format('Y-m-d H:i') }}</td>
                                <td class="px-4 py-3">{{ $p->estudiante->matricula ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    {{ trim(($p->estudiante->nombre ?? '').' '.($p->estudiante->apellido_paterno ?? '').' '.($p->estudiante->apellido_materno ?? '')) }}
                                </td>
                                <td class="px-4 py-3 font-mono text-slate-200">{{ $p->referencia }}</td>
                                <td class="px-4 py-3">
                                    @if(!empty($p->comprobante_path))
                                        <a href="{{ Storage::disk('public')->url($p->comprobante_path) }}"
                                           target="_blank"
                                           class="inline-flex items-center gap-1 text-emerald-300 hover:text-emerald-200 underline decoration-emerald-600/60">
                                            ver
                                        </a>
                                    @else
                                        <span class="text-slate-500">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-[11px] font-semibold js-badge {{ $chipClass }}"
                                          data-ref="{{ $p->referencia }}" data-status="{{ $estadoFila }}">
                                        <span class="js-text">{{ $estadoFila }}</span>
                                    </span>
                                    @if(!empty($p->observaciones_caja))
                                        <div class="text-[12px] text-slate-400 mt-1 js-obs">{{ $p->observaciones_caja }}</div>
                                    @else
                                        <div class="text-[12px] text-slate-500 mt-1 js-obs"></div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-10 text-center text-slate-400">
                                    Sin resultados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-4 py-3">
                {{ $pagos->withQueryString()->links() }}
            </div>
        </div>

        <div class="text-xs text-slate-400 mt-3">
            Actualización automática cada <strong>15s</strong>. Última actualización: <span id="js-last-refresh">—</span>
        </div>
    </div>

    {{-- Auto-refresh de estado desde la API --}}
    <script>
        (function(){
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const CHIP = {
                'VALIDADO':   'bg-emerald-600 text-white shadow-sm shadow-emerald-900/30 border border-emerald-400/30',
                'RECHAZADO':  'bg-rose-600 text-white shadow-sm shadow-rose-900/30 border border-rose-400/30',
                'EN_PROCESO': 'bg-amber-500 text-slate-900 shadow-sm shadow-amber-900/20 border border-amber-400/40',
                'PENDIENTE':  'bg-slate-600 text-white border border-slate-500/40'
            };

            function timeStr(){ return new Date().toLocaleTimeString(); }

            function pickRefs(){
                const badges = Array.from(document.querySelectorAll('.js-badge'));
                const refs = [...new Set(badges.map(b => b.dataset.ref))];
                return {badges, refs};
            }

            async function refresh(){
                const {badges, refs} = pickRefs();
                if (!refs.length) return;

                try {
                    const res = await fetch('{{ route('caja.referencias.estado') }}', {
                        method: 'POST',
                        headers: {'Content-Type':'application/json','X-CSRF-TOKEN': token},
                        body: JSON.stringify({refs})
                    });
                    if(!res.ok) return;
                    const data = await res.json();
                    if(!data.ok || !data.map) return;

                    badges.forEach(b => {
                        const m = data.map[b.dataset.ref];
                        if(!m) return;

                        const status = (m.status || 'PENDIENTE').toUpperCase();
                        b.className = 'inline-flex items-center px-2 py-1 rounded-full text-[11px] font-semibold js-badge ' + (CHIP[status] || CHIP['PENDIENTE']);
                        b.dataset.status = status;

                        const t = b.querySelector('.js-text'); if (t) t.textContent = status;
                        const obsEl = b.parentElement.querySelector('.js-obs');
                        if (obsEl) obsEl.textContent = m.obs ? m.obs : '';
                    });

                    document.getElementById('js-last-refresh').textContent = timeStr();
                } catch (_) {}
            }

            refresh();
            setInterval(refresh, 15000);
        })();
    </script>
</x-app-layout>
