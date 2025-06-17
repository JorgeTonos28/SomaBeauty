<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Servicios Disponibles') }}
        </h2>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">


        @if (auth()->user()->role === 'admin')
            <div class="mb-4">
                <a href="{{ route('services.create') }}" class="btn-primary">
                    Nuevo Servicio
                </a>
            </div>
        @endif

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg max-h-96 overflow-y-auto">
            <table class="min-w-full table-auto border">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="px-4 py-2 border">Nombre</th>
                        <th class="px-4 py-2 border">Descripción</th>
                        <th class="px-4 py-2 border">Estado</th>
                        <th class="px-4 py-2 border">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($services as $service)
                        <tr class="border-t">
                            <td class="px-4 py-2">{{ $service->name }}</td>
                            <td class="px-4 py-2">{{ $service->description }}</td>
                            <td class="px-4 py-2">{{ $service->active ? 'Activo' : 'Inactivo' }}</td>
                            <td class="px-4 py-2 flex gap-2">
                                @if (auth()->user()->role === 'admin')
                                    <a href="{{ route('services.edit', $service) }}"
                                       class="text-yellow-600 hover:underline">Editar</a>

                                    <form method="POST" action="{{ route('services.destroy', $service) }}"
                                          onsubmit="return confirm('Â¿Eliminar este servicio?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-red-600 hover:underline" type="submit">
                                            Eliminar
                                        </button>
                                    </form>
                                @else
                                    <span class="text-gray-400 italic">Sólo lectura</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>

