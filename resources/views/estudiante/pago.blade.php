<x-app-layout>
<x-slot name="header">Registro de pago</x-slot>

<div class="space-y-6">
  <div class="card">
    <form method="POST" action="{{ route('est.pago.store') }}" enctype="multipart/form-data" class="space-y-4">
      @csrf

      <div>
        <label class="label">Referencia (20 dígitos)</label>
        <input
          type="text" name="referencia" inputmode="numeric" pattern="\d{20,30}" maxlength="30"
          autocomplete="off"
          class="input text-gray-900 dark:text-gray-100 placeholder-gray-400"
          placeholder="Ej. 12345678901234567890"
          value="{{ old('referencia') }}" required
        >
        <p class="text-xs text-gray-500 mt-1">Del portal de pagos del Estado.</p>
      </div>

      <div>
        <label class="label">Comprobante (PDF/JPG/PNG, máx 5MB)</label>
        <input type="file" name="comprobante" accept="application/pdf,image/*" class="input" required>
      </div>

      <div class="flex justify-end gap-3">
        <a href="{{ route('dash.estudiante') }}" class="btn btn-secondary">Cancelar</a>
        <button class="btn btn-primary">Enviar</button>
      </div>
    </form>
  </div>
</div>
</x-app-layout>
