<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar sesión — Reinscripciones UTH</title>

    <style>
        :root{
            --bg:#0c1420;
            --panel:#111b27;
            --panel-2:#0d1722;
            --border:#203246;
            --txt:#e9f0f8;
            --muted:#a6b9cf;
            --primary:#00c389;    /* verde UTH */
            --primary-ink:#052b22;
            --danger:#ec5b6c;
            --focus:#8ddad7;
        }
        *{box-sizing:border-box}
        html,body{height:100%}
        body{
            margin:0; color:var(--txt); font-family:ui-sans-serif,system-ui,-apple-system,"Segoe UI",Roboto,"Helvetica Neue",Arial;
            background:var(--bg);
            overflow-x:hidden;
        }

        /* ===== Fondo animado (sin JS) ===== */
        .bg{
            position:fixed; inset:0; z-index:-2; overflow:hidden;
            background:
                radial-gradient(1000px 600px at 10% -10%, #152437 0%, rgba(21,36,55,0) 60%),
                radial-gradient(900px 500px at 110% 110%, #132236 0%, rgba(19,34,54,0) 60%),
                linear-gradient(140deg, #0b1320, #0f1a2a 40%, #0b1220 100%);
            animation: bg-move 28s linear infinite alternate;
        }
        @keyframes bg-move{
            0%  { filter:hue-rotate(0deg) brightness(1); }
            100%{ filter:hue-rotate(10deg) brightness(1.05); }
        }
        .blob{ position:fixed; width:520px; height:520px; border-radius:50%;
               filter:blur(60px) saturate(1.2); opacity:.18; z-index:-1; }
        .blob.a{ background:#00c389; top:-120px; left:-120px; animation: float-a 18s ease-in-out infinite; }
        .blob.b{ background:#4bb0ff; bottom:-160px; right:-120px; animation: float-b 22s ease-in-out infinite; }
        .blob.c{ background:#a36bff; top:40%; left:-120px; animation: float-c 26s ease-in-out infinite; }
        @keyframes float-a{ 0%{transform:translate(0,0)} 50%{transform:translate(60px,40px)} 100%{transform:translate(0,0)}}
        @keyframes float-b{ 0%{transform:translate(0,0)} 50%{transform:translate(-60px,-40px)} 100%{transform:translate(0,0)}}
        @keyframes float-c{ 0%{transform:translate(0,0)} 50%{transform:translate(40px,-60px)} 100%{transform:translate(0,0)}}

        /* ===== Layout ===== */
        .wrap{
            min-height:100vh; display:grid; place-items:center; padding:4rem 1.25rem;
        }
        .card{
            width:min(92vw, 560px);
            background:linear-gradient(180deg, rgba(255,255,255,.04), transparent 35%), var(--panel);
            border:1px solid var(--border);
            border-radius:20px;
            box-shadow:
                0 25px 60px rgba(0,0,0,.45),
                0 0 0 1px rgba(255,255,255,.03) inset;
            padding:1.5rem 1.5rem 1.25rem;
        }
        .brand{
            display:flex; flex-direction:column; align-items:center; gap:.85rem; margin-bottom:1rem;
        }
        .brand a{display:inline-flex; align-items:center; gap:.85rem; color:var(--txt); text-decoration:none}
        .brand img{
            width:74px; height:74px; border-radius:14px; background:var(--panel-2);
            padding:.6rem; box-shadow:0 0 0 1px rgba(255,255,255,.06) inset, 0 10px 25px rgba(0,0,0,.4);
            transition:transform .12s ease;
        }
        .brand img:hover{ transform:translateY(-1px) scale(1.01) }
        .brand .title{ font-weight:800; letter-spacing:.2px; font-size:1.15rem; opacity:.95 }

        h1{ margin:.25rem 0 0; font-size:1.5rem; font-weight:800; text-align:center }

        .field{ margin:1rem 0 }
        label{ display:block; font-size:.95rem; color:var(--muted); margin:0 0 .45rem .25rem }
        .input{
            width:100%; border-radius:12px; padding:1rem .95rem;
            background:var(--panel-2); color:var(--txt); border:1px solid var(--border);
            outline:none; font-size:1rem;
            transition: border-color .15s, box-shadow .15s, background .15s;
        }
        .input::placeholder{ color:#6c879f }
        .input:focus{ border-color:var(--focus); box-shadow:0 0 0 3px rgba(141,218,215,.20) }
        .input.is-invalid{ border-color:var(--danger) }

        .pwd-wrap{ position:relative }
        .pwd-toggle{
            position:absolute; top:50%; right:.65rem; transform:translateY(-50%);
            background:transparent; color:#86a3c2; border:none; cursor:pointer; padding:.3rem .45rem;
            border-radius:8px;
        }
        .pwd-toggle:hover{ color:var(--txt) }

        .row{ display:flex; justify-content:space-between; align-items:center; gap:.75rem; margin-top:.25rem }
        .checkbox{ display:flex; align-items:center; gap:.55rem; color:var(--muted); font-size:.95rem }
        .links a{ color:var(--muted); text-decoration:none; border-bottom:1px dashed transparent; }
        .links a:hover{ color:var(--txt); border-color:var(--muted) }

        .actions{ display:flex; justify-content:flex-end; gap:.6rem; margin-top:1rem }
        .btn{
            display:inline-flex; align-items:center; justify-content:center; gap:.5rem;
            border:1px solid transparent; border-radius:12px; padding:.9rem 1.1rem;
            font-weight:700; cursor:pointer; text-decoration:none; transition:.15s; font-size:1rem;
        }
        .btn-primary{ background:var(--primary); color:var(--primary-ink) }
        .btn-primary:hover{ filter:brightness(1.02) saturate(1.05) }
        .btn-ghost{ background:transparent; color:var(--muted); border-color:var(--border) }
        .btn-ghost:hover{ color:var(--txt); border-color:#3b516d }

        .alert{
            border-radius:12px; padding:.9rem 1rem; border:1px solid; margin-top:.6rem; font-size:.96rem;
        }
        .alert-danger{ background:rgba(236,91,108,.08); border-color:rgba(236,91,108,.35); color:#ffc2c9 }
        .alert-info{ background:rgba(141,218,215,.08); border-color:rgba(141,218,215,.35); color:#bdebea }

        @media (max-width:520px){
            .actions{ flex-direction:column-reverse; align-items:stretch }
            .brand img{ width:62px; height:62px }
        }
    </style>
</head>
<body>

<!-- Fondo animado -->
<div class="bg"></div>
<div class="blob a"></div>
<div class="blob b"></div>
<div class="blob c"></div>

<div class="wrap">
    <form class="card" method="POST" action="{{ route('login') }}" novalidate>
        @csrf

        <!-- Logo: botón que regresa al home -->
        <div class="brand">
            <a href="{{ route('home') }}" title="Volver a la página principal">
                <img src="{{ asset('images/logo-uth.png') }}" alt="UTH">
                <span class="title">Reinscripciones UTH</span>
            </a>
            <h1>Iniciar sesión</h1>
        </div>

        {{-- Mensaje de estado --}}
        @if (session('status'))
            <div class="alert alert-info">{{ session('status') }}</div>
        @endif

        {{-- Errores --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                @foreach ($errors->all() as $err) • {{ $err }}<br> @endforeach
            </div>
        @endif

        <div class="field">
            <label for="email">Correo institucional</label>
            <input id="email" name="email" type="email" required autofocus
                   class="input @error('email') is-invalid @enderror"
                   placeholder="usuario@uth.edu.mx"
                   value="{{ old('email') }}">
        </div>

        <div class="field pwd-wrap">
            <label for="password">Contraseña</label>
            <input id="password" name="password" type="password" required
                   class="input @error('password') is-invalid @enderror"
                   placeholder="********">
            <button type="button" class="pwd-toggle" onclick="togglePwd()" aria-label="Mostrar u ocultar contraseña">👁</button>
        </div>

        <div class="row">
            <label class="checkbox">
                <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                Recordarme
            </label>
            <div class="links">
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}">¿Olvidaste tu contraseña?</a>
                @endif
            </div>
        </div>

        <div class="actions">
            <a class="btn btn-ghost" href="{{ route('home') }}">Volver al inicio</a>
            <button type="submit" class="btn btn-primary">Entrar</button>
        </div>
    </form>
</div>

<script>
    function togglePwd(){
        const i = document.getElementById('password');
        i.type = (i.type === 'password') ? 'text' : 'password';
    }
</script>
</body>
</html>
