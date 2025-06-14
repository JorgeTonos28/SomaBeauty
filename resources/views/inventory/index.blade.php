<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Movimientos de Inventario') }}
        </h2>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">
        @if (session('success'))
            <div class="mb-4 font-medium text-sm text-green-600">
                {{ session('success') }}
            </div>
        @endif

        <div class="mb-4">
            <a href="{{ route('inventory.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                Nueva Entrada
            </a>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <table class="min-w-full table-auto border">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="px-4 py-2 border">Fecha</th>
                        <th class="px-4 py-2 border">Producto</th>
                        <th class="px-4 py-2 border">Tipo</th>
                        <th class="px-4 py-2 border">Cantidad</th>
                        <th class="px-4 py-2 border">Descripción</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($movements as $move)
                        <tr class="border-t">
                            <td class="px-4 py-2">{{ $move->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-2">{{ $move->product->name }}</td>
                            <td class="px-4 py-2">{{ ucfirst($move->movement_type) }}</td>
                            <td class="px-4 py-2">{{ $move->quantity }}</td>
                            <td class="px-4 py-2">{{ $move->description }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $movements->links() }}
        </div>
    </div>
</x-app-layout>
