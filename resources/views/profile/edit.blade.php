<x-app-layout>
@include('components.toast')
<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        {{ __('Mi perfil') }}
    </h2>
</x-slot>

<div class="py-6">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
            @if (session('status') === 'profile-updated')
                <div class="mb-4 p-3 rounded bg-green-600 text-white">Perfil actualizado.</div>
            @endif

            <form method="POST" action="{{ route('profile.update') }}">
                @csrf
                @method('patch')

                <div class="mb-4">
                    <label class="block text-sm mb-1">Nombre</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}"
                        class="w-full border rounded p-2" required>
                    @error('name')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}"
                        class="w-full border rounded p-2" required>
                    @error('email')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
                </div>

                <button class="px-4 py-2 bg-indigo-600 text-white rounded">Guardar</button>
            </form>

            <hr class="my-6">

            <form method="POST" action="{{ route('profile.destroy') }}"
                  onsubmit="return confirm('¿Seguro que quieres eliminar tu cuenta?');">
                @csrf
                @method('delete')

                <div class="mb-4">
                    <label class="block text-sm mb-1">Confirma tu contraseña</label>
                    <input type="password" name="password" class="w-full border rounded p-2" required>
                    @error('userDeletion.password')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
                </div>

                <button class="px-4 py-2 bg-red-600 text-white rounded">Eliminar cuenta</button>
            </form>
        </div>
    </div>
</div>
</x-app-layout>
