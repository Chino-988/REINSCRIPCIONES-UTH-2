{{-- resources/views/offline.blade.php --}}
<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Modo sin conexión | Reinscripciones UTH</title>

  {{-- No metas CSS remoto aquí; mantenlo auto-contenido --}}
  <meta name="theme-color" content="#0c7a1c">
  <link rel="icon" href="/img/icon-192.png" type="image/png">
  <style>
    :root { --brand:#0c7a1c; --ink:#123; --bg:#f6faf7; }
    *{box-sizing:border-box}
    html,body{height:100%}
    body{margin:0;display:flex;align-items:center;justify-content:center;background:var(--bg);color:var(--ink);font:16px/1.5 system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif}
    .card{background:#fff;max-width:560px;width:92%;padding:2rem;border-radius:16px;box-shadow:0 10px 30px rgba(0,0,0,.08);text-align:center}
    h1{margin:0 0 .5rem;color:var(--brand)}
    p{margin:.5rem 0}
    .muted{color:#456;font-size:.95rem}
    .btn{display:inline-block;margin-top:1rem;padding:.6rem 1rem;border-radius:10px;border:1px solid var(--brand);color:var(--brand);background:#fff;text-decoration:none}
  </style>
</head>
<body>
  <main class="card" role="main" aria-label="Modo sin conexión">
    <img src="/img/icon-192.png" alt="Reinscripciones UTH" width="64" height="64" style="border-radius:12px;margin-bottom:8px">
    <h1>Estás sin conexión</h1>
    <p>Algunas funciones requieren internet. Cuando recuperes la señal, recarga la página.</p>
    <p class="muted">Puedes navegar las secciones que el dispositivo tenga en caché.</p>
    <a href="/" class="btn">Reintentar</a>
  </main>
</body>
</html>
