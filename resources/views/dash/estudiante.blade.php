<x-app-layout>
@include('components.toast')
<h1>Dashboard Estudiante</h1>
<p>Bienvenido, {{ auth()->user()->name }}</p>

<div>
  <a href="{{ route('est.perfil.edit') }}">Validar/Editar mis datos</a> |
  <a href="{{ route('est.pago.create') }}">Registrar pago y comprobante</a>
</div>

@if($rein)
  <p>Estatus de reinscripción: <strong>{{ $rein->estatus_final }}</strong></p>
  @if($rein->constancia_pdf_path)
     <a href="{{ asset('storage/'.$rein->constancia_pdf_path) }}" target="_blank">Descargar Constancia</a>
  @endif
@endif
</x-app-layout>
