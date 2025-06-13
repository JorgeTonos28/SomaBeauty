<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Usuario') }}
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 bg-white p-6 shadow sm:rounded-lg">
            @if ($errors->any())
                <div class="mb-4 text-sm text-red-600">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('users.update', $user) }}">
                @csrf
                @method('PUT')
                <div class="mb-4">
                    <label for="name" class="block font-medium text-sm text-gray-700">Nombre</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required class="form-input w-full rounded border-gray-300 shadow-sm mt-1">
                </div>
                <div class="mb-4">
                    <label for="email" class="block font-medium text-sm text-gray-700">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required class="form-input w-full rounded border-gray-300 shadow-sm mt-1">
                </div>
                <div class="mb-4">
                    <label for="password" class="block font-medium text-sm text-gray-700">Contraseña (dejar vacía para no cambiar)</label>
                    <input type="password" name="password" id="password" class="form-input w-full rounded border-gray-300 shadow-sm mt-1">
                </div>
                <div class="mb-4">
                    <label for="password_confirmation" class="block font-medium text-sm text-gray-700">Confirmar Contraseña</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-input w-full rounded border-gray-300 shadow-sm mt-1">
                </div>
                <div class="mb-4">
                    <label for="role" class="block font-medium text-sm text-gray-700">Rol</label>
                    <select name="role" id="role" class="form-select w-full rounded border-gray-300 shadow-sm mt-1">
                        <option value="cajero" @selected(old('role', $user->role) == 'cajero')>Cajero</option>
                        <option value="admin" @selected(old('role', $user->role) == 'admin')>Administrador</option>
                    </select>
                </div>
                <div class="flex justify-end">
                    <a href="{{ route('users.index') }}" class="mr-3 px-4 py-2 bg-gray-300 text-gray-700 rounded">Cancelar</a>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
