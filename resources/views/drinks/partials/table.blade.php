<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg max-h-96 overflow-y-auto">
    <table class="min-w-full table-auto border">
        <thead class="bg-gray-200">
            <tr>
                <th class="px-4 py-2 border">Nombre</th>
                <th class="px-4 py-2 border">Ingredientes</th>
                <th class="px-4 py-2 border">Precio</th>
                <th class="px-4 py-2 border">Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($drinks as $drink)
                <tr class="border-t cursor-pointer"
                    x-on:click="selected = selected === {{ $drink->id }} ? null : {{ $drink->id }}"
                    :class="selected === {{ $drink->id }} ? 'bg-blue-100' : ''">
                    <td class="px-4 py-2">{{ $drink->name }}</td>
                    <td class="px-4 py-2">{{ $drink->ingredients }}</td>
                    <td class="px-4 py-2">RD$ {{ number_format($drink->price, 2) }}</td>
                    <td class="px-4 py-2">{{ $drink->active ? 'Activo' : 'Inactivo' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
