<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Verificación de constancia</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    :root { --verde:#087d00; }
    body { font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial; margin:0; background:#f7f7f7; color:#111; }
    .wrap { max-width: 840px; margin: 40px auto; background:#fff; border-radius:12px; padding:24px 28px; box-shadow:0 8px 24px rgba(0,0,0,.08);}
    .tag { display:inline-block; padding:6px 10px; border-radius:8px; font-weight:600; font-size:14px;}
    .ok { background: #e6f6ea; color:#0a5c0a; border:1px solid #bfe7c8;}
    .bad{ background: #fde8e8; color:#991b1b; border:1px solid #f9caca;}
    h1 { margin: 0 0 10px; font-size: 22px; color:#222;}
    .muted { color:#666; font-size:14px; }
    .grid { display:grid; grid-template-columns: 1fr 1fr; gap:14px; margin:14px 0 6px;}
    .box { background:#fafafa; border:1px solid #eee; border-radius:8px; padding:12px;}
    .foot { margin-top:16px; font-size:12px; color:#666;}
    a { color:#0b5; text-decoration: none; }
    a:hover{ text-decoration: underline; }
  </style>
</head>
<body>
  <div class="wrap">
    <div style="display:flex; align-items:center; gap:14px; margin-bottom:12px;">
      <div style="width:10px; height:32px; background:var(--verde); border-radius:4px;"></div>
      <div>
        <h1>Verificación de constancia de reinscripción</h1>
        <div class="muted">Universidad Tecnológica de Huejotzingo</div>
      </div>
    </div>

    @if($vigente)
      <div class="tag ok">Reinscripción APROBADA y verificada</div>
    @else
      <div class="tag bad">No vigente / Rechazada</div>
    @endif

    <div class="grid" style="margin-top:16px;">
      <div class="box">
        <b>Matrícula:</b><br>{{ $rein->estudiante->matricula }}
      </div>
      <div class="box">
        <b>Nombre:</b><br>
        {{ $rein->estudiante->nombre }}
        {{ $rein->estudiante->apellido_paterno }}
        {{ $rein->estudiante->apellido_materno }}
      </div>
      <div class="box">
        <b>Emitida el:</b><br>{{ optional($rein->created_at)->format('d/m/Y H:i') }}
      </div>
      <div class="box">
        <b>Estatus final:</b><br>{{ $rein->estatus_final }}
      </div>
    </div>

    @if($rein->constancia_pdf_path)
      <p class="muted" style="margin-top:8px;">
        Documento asociado:
        <a href="{{ asset('storage/'.$rein->constancia_pdf_path) }}" target="_blank">Constancia (PDF)</a>
      </p>
    @endif

    <div class="foot">
      Token: <code>{{ $rein->token_verificacion }}</code>
      <br>Este comprobante se valida contra el registro institucional en tiempo real.
    </div>
  </div>
</body>
</html>
