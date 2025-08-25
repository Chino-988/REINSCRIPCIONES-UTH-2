<!doctype html>
<html lang="es" class="h-full">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Módulo de Reinscripciones — UTH</title>
  @vite(['resources/css/app.css','resources/js/app.js'])
  <style>
    :root { --uth:#087d00; }
    .brand { color: var(--uth); }
    .btn-brand { background: var(--uth); color:#fff; }
    .btn-brand:hover { filter: brightness(.95); }
    .ring-brand { box-shadow: 0 0 0 3px rgba(8,125,0,.15); }
    .hero-fade { background: radial-gradient(80% 120% at 20% 10%, rgba(8,125,0,.20) 0%, rgba(8,125,0,0) 50%), radial-gradient(80% 120% at 100% 10%, rgba(8,125,0,.25) 0%, rgba(8,125,0,0) 50%); }
    /* Carousel */
    .carousel { position: relative; overflow: hidden; border-radius: 1rem; }
    .slides { display:flex; transition: transform .6s ease; }
    .slide { min-width:100%; height: 360px; position: relative; }
    .slide img { position:absolute; inset:0; width:100%; height:100%; object-fit: cover; }
    .slide::after{content:""; position:absolute; inset:0; background: linear-gradient(to top, rgba(0,0,0,.45), rgba(0,0,0,.1));}
    .ctrl { position:absolute; top:50%; transform: translateY(-50%); background: rgba(0,0,0,.45); color:#fff; border:none; width:42px; height:42px; border-radius:999px; display:grid; place-items:center; }
    .ctrl:hover{ background: rgba(0,0,0,.6);}
    .ctrl.prev{ left:10px;}
    .ctrl.next{ right:10px;}
    .bullets { position:absolute; left:0; right:0; bottom:10px; display:flex; gap:8px; justify-content:center; }
    .bullet { width:9px; height:9px; border-radius:999px; background: rgba(255,255,255,.5); border:1px solid rgba(0,0,0,.25); }
    .bullet.active { background: #fff; }
  </style>
</head>
<body class="h-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100">
  <div class="min-h-screen">

    <!-- Topbar -->
    <header class="backdrop-blur supports-backdrop-blur:bg-white/60 bg-white/80 dark:bg-gray-900/80 border-b border-gray-100 dark:border-gray-800">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        <div class="flex items-center gap-3">
          <img src="{{ asset('img/uth_logo.png') }}" alt="UTH" class="h-9 w-auto rounded-sm ring-brand" onerror="this.style.display='none'">
          <div class="font-semibold tracking-tight">Universidad Tecnológica de Huejotzingo</div>
        </div>
        <nav class="flex items-center gap-2">
          @if (Route::has('login'))
            <!-- Cambiado a verde usando tu clase .btn-brand -->
            <a href="{{ route('login') }}" class="px-3 py-2 rounded-md btn-brand">Ingresar</a>
          @endif
          <!-- Se quitó el botón Registrarme de la navbar -->
        </nav>
      </div>
    </header>

    <!-- Hero -->
    <section class="hero-fade">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 lg:py-14 grid lg:grid-cols-2 gap-8 items-center">

        <!-- Texto + acciones -->
        <div>
          <h1 class="text-3xl sm:text-4xl font-extrabold leading-tight">
            Módulo de <span class="brand">Reinscripciones</span> — UTH
          </h1>
          <p class="mt-3 text-gray-600 dark:text-gray-300 max-w-xl">
            Valida tus datos, carga tu pago y obtén tu constancia con verificación por QR.
          </p>

          <!-- Se removieron los botones duplicados (Ingresar / Registrarme) del hero -->

          <!-- Verificación por token -->
          <div class="mt-8 bg-white dark:bg-gray-800/70 border border-gray-200 dark:border-gray-700 rounded-xl p-4">
            <div class="font-semibold mb-1">Verificación de constancia</div>
            <p class="text-sm text-gray-500 dark:text-gray-400">
              Pega aquí el <b>token</b> que aparece en el QR o debajo de tu constancia para verificarla.
            </p>
            <form class="mt-3 flex gap-2" method="POST" action="{{ route('verificacion.redirect') }}">
              @csrf
              <input name="token" required maxlength="80"
                     placeholder="Ej. e2fc6ef8-7a03-46bd-9c98-5ada46a1cf1c"
                     class="flex-1 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 outline-none focus:ring-2 focus:ring-emerald-500"/>
              <button class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white">Verificar</button>
            </form>
            @if(session('err'))
              <div class="mt-2 text-sm text-rose-500">{{ session('err') }}</div>
            @endif
          </div>
        </div>

        <!-- Carrusel -->
        <div>
          <div class="carousel shadow-2xl ring-1 ring-black/5 dark:ring-white/10">
            <div class="slides" id="slides">
              {{-- Slide 1 (imagen local si existe, si no, degradado) --}}
              <div class="slide">
                <img src="{{ asset('img/welcome/ENTRADAUTH.png') }}" alt="Campus UTH" onerror="this.remove()">
              </div>
              {{-- Slide 2 --}}
              <div class="slide">
                <img src="{{ asset('img/welcome/huejotzingo2.png') }}" alt="Estudiantes UTH" onerror="this.remove()">
              </div>
              {{-- Slide 3 --}}
              <div class="slide">
                <img src="{{ asset('img/welcome/huejotzingo3.png') }}" alt="Instalaciones UTH" onerror="this.remove()">
              </div>
            </div>

            <button class="ctrl prev" aria-label="Anterior" id="prev">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M15.41 7.41 14 6l-6 6 6 6 1.41-1.41L10.83 12z"/></svg>
            </button>
            <button class="ctrl next" aria-label="Siguiente" id="next">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M8.59 16.59 10 18l6-6-6-6-1.41 1.41L13.17 12z"/></svg>
            </button>

            <div class="bullets" id="bullets" aria-hidden="true"></div>
          </div>
        </div>
      </div>
    </section>

    <!-- Módulos -->
    <section class="py-10 lg:py-12 border-t border-gray-100 dark:border-gray-800">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-xl font-bold mb-4">Módulos</h2>
        <div class="grid md:grid-cols-3 gap-5">
          <div class="p-5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
            <div class="font-semibold">Estudiante</div>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Validación de datos, carga de pago y descarga de constancia.</p>
          </div>
          <div class="p-5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
            <div class="font-semibold">Caja</div>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Validación de pagos, CSV de validados y análisis masivo de referencias.</p>
          </div>
          <div class="p-5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
            <div class="font-semibold">Administrador</div>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Revisión integral, emisión de constancia con QR y verificación pública.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="py-6 text-center text-sm text-gray-500 dark:text-gray-400">
      © {{ date('Y') }} Universidad Tecnológica de Huejotzingo
    </footer>
  </div>

  <script>
    // Carousel simple (sin dependencias)
    (function(){
      const slides = document.getElementById('slides');
      const prev   = document.getElementById('prev');
      const next   = document.getElementById('next');
      const bullets= document.getElementById('bullets');

      // Cuenta de slides (solo los que tienen imagen o, si no hay imágenes, deja 1 slide "vacío")
      let total = slides.children.length;
      if (total === 0) {
        const fallback = document.createElement('div');
        fallback.className='slide';
        fallback.style.background='linear-gradient(135deg, rgba(8,125,0,.35), rgba(8,125,0,.15))';
        slides.appendChild(fallback);
        total = 1;
      }

      let i = 0, timer;
      function go(n){
        i = (n + total) % total;
        slides.style.transform = `translateX(-${i*100}%)`;
        [...bullets.children].forEach((b,idx)=>b.classList.toggle('active', idx===i));
      }
      function auto(){ clearInterval(timer); timer = setInterval(()=>go(i+1), 6000); }
      function buildBullets(){
        bullets.innerHTML='';
        for(let k=0;k<total;k++){
          const b = document.createElement('span');
          b.className='bullet'+(k===0?' active':'');
          b.addEventListener('click', ()=>{ go(k); auto(); });
          bullets.appendChild(b);
        }
      }
      prev.addEventListener('click', ()=>{ go(i-1); auto(); });
      next.addEventListener('click', ()=>{ go(i+1); auto(); });
      slides.addEventListener('mouseenter', ()=>clearInterval(timer));
      slides.addEventListener('mouseleave', auto);

      buildBullets();
      go(0);
      auto();
    })();
  </script>
</body>
</html>
