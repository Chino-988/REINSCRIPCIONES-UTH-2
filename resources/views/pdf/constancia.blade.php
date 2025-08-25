<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Constancia de Reinscripción</title>
<style>
  @page { margin: 60px 50px; }
  body { font-family: DejaVu Sans, sans-serif; color:#111; font-size:12px; }
  .head { border-left: 6px solid #087d00; padding-left:10px; margin-bottom: 12px; }
  .title { font-size: 18px; font-weight:700; margin:0; color:#222; }
  .sub { font-size: 12px; color:#666; }
  .grid2 { width:100%; border-collapse: collapse; }
  .grid2 td { padding:8px 10px; border:1px solid #e5e5e5; vertical-align: top; }
  .label { color:#555; font-weight:600; width:34%; }
  .muted { color:#666; font-size: 11px; }
  .box { border:1px dashed #bbb; padding:10px; border-radius:6px; }
  .footer { position: fixed; bottom: -20px; left: 0; right: 0; text-align:center; color:#777; font-size:10px; }
</style>
</head>
<body>
  <div class="head">
    <div class="title">Constancia de Reinscripción</div>
    <div class="sub">Universidad Tecnológica de Huejotzingo</div>
  </div>

  <table class="grid2">
    <tr>
      <td class="label">Matrícula</td>
      <td>{{ $estu->matricula }}</td>
    </tr>
    <tr>
      <td class="label">Nombre del estudiante</td>
      <td>{{ $estu->nombre }} {{ $estu->apellido_paterno }} {{ $estu->apellido_materno }}</td>
    </tr>
    <tr>
      <td class="label">Estatus</td>
      <td><b>APROBADA</b> (proceso de reinscripción validado)</td>
    </tr>
    <tr>
      <td class="label">Fecha de emisión</td>
      <td>{{ optional($rein->created_at)->format('d/m/Y H:i') }}</td>
    </tr>
    <tr>
      <td class="label">Token de verificación</td>
      <td>{{ $rein->token_verificacion }}</td>
    </tr>
    <tr>
      <td class="label">Verificación pública</td>
      <td>
        <div class="box">
          @if(!empty($qrSvg))
            <div style="display:flex; align-items:center; gap:12px;">
              <div style="width:110px; height:110px;">
                {!! $qrSvg !!}
              </div>
              <div>
                <div class="muted">Escanea el código QR o visita:</div>
                <div style="font-size:11px;">{{ $urlVerif }}</div>
              </div>
            </div>
          @else
            <div class="muted">Visita la siguiente URL para validar esta constancia:</div>
            <div style="font-size:11px;">{{ $urlVerif }}</div>
          @endif
        </div>
      </td>
    </tr>
  </table>

  <div style="margin-top:26px;">
    <table class="grid2">
      <tr>
        <td style="height:80px;">
          <div class="muted">Observaciones:</div>
          <div style="min-height:52px;"></div>
        </td>
        <td style="width:40%; text-align:center;">
          <div style="margin-top:28px; border-top:1px solid #999; display:inline-block; padding-top:6px; min-width:220px;">
            Firma y sello de control escolar
          </div>
        </td>
      </tr>
    </table>
  </div>

  <div class="footer">
    Documento generado electrónicamente. Color institucional: #087d00
  </div>
</body>
</html>
