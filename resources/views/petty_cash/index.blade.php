<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Caja Chica') }}
        </h2>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">
        @if (session('success'))
            <div class="mb-4 font-medium text-sm text-green-600">{{ session('success') }}</div>
        @endif

        <div class="mb-4">
            <a href="{{ route('petty-cash.create') }}" class="px-4 py-2 text-white bg-blue-500 rounded hover:bg-blue-600">Nuevo Gasto</a>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <table class="min-w-full table-auto border">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="border px-4 py-2">Fecha</th>
                        <th class="border px-4 py-2">Descripción</th>
                        <th class="border px-4 py-2">Monto</th>
                        <th class="border px-4 py-2">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($expenses as $expense)
                        <tr class="border-t">
                            <td class="px-4 py-2">{{ $expense->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-2">{{ $expense->description }}</td>
                            <td class="px-4 py-2">RD$ {{ number_format($expense->amount, 2) }}</td>
                            <td class="px-4 py-2">
                                <form action="{{ route('petty-cash.destroy', $expense) }}" method="POST" onsubmit="return confirm('¿Eliminar este gasto?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
