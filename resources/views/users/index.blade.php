<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Usuarios') }}
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="flex justify-end mb-4">
                <a href="{{ route('users.create') }}" class="btn-primary">
                    Nuevo Usuario
                </a>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg max-h-96 overflow-y-auto">
                <table class="min-w-full table-auto border">
                    <thead class="bg-gray-200">
                        <tr>
                            <th class="px-4 py-2 border">Nombre</th>
                            <th class="px-4 py-2 border">Email</th>
                            <th class="px-4 py-2 border">Rol</th>
                            <th class="px-4 py-2 border">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr class="border-b">
                                <td class="px-4 py-2">{{ $user->name }}</td>
                                <td class="px-4 py-2">{{ $user->email }}</td>
                                <td class="px-4 py-2">{{ $user->role }}</td>
                                <td class="px-4 py-2">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('users.edit', $user) }}" class="text-yellow-600 hover:underline">Editar</a>
                                        <form action="{{ route('users.destroy', $user) }}" method="POST" onsubmit="return confirm('¿Eliminar este usuario?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:underline">Eliminar</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
