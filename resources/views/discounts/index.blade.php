<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Descuentos') }}
        </h2>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">
        @if (session('success'))
            <div class="mb-4 font-medium text-sm text-green-600">{{ session('success') }}</div>
        @endif

        <div class="mb-4">
            <a href="{{ route('discounts.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">Nuevo Descuento</a>
        </div>

        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Producto/Servicio</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Descuento</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Fin</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                        <th class="px-3 py-2"></th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($discounts as $d)
                        <tr ondblclick="window.location='{{ route('discounts.show', $d) }}'" class="cursor-pointer">
                            <td class="px-3 py-2">{{ $d->discountable->name ?? '' }}</td>
                            <td class="px-3 py-2">
                                @if($d->amount_type === 'fixed')
                                    RD${{ number_format($d->amount,2) }}
                                @else
                                    {{ $d->amount }}%
                                @endif
                            </td>
                            <td class="px-3 py-2">{{ optional($d->end_at)->format('d/m/Y H:i') }}</td>
                            <td class="px-3 py-2">{{ $d->active ? 'Activo' : 'Inactivo' }}</td>
                            <td class="px-3 py-2 text-right">
                                <a href="{{ route('discounts.edit', $d) }}" class="text-blue-600">Editar</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
