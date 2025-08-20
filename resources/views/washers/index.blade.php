<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Lavadores') }}
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">


            <div class="flex justify-between mb-4">
                <a href="{{ route('washers.create') }}" class="btn-primary">
                    Nuevo Lavador
                </a>
                <button type="button" x-on:click="$dispatch('open-modal', 'pay-all')" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Pagar Todos</button>
            </div>

            <div class="mb-4 bg-white p-4 shadow sm:rounded-lg">
                <p>Total adeudado: <strong>RD$ {{ number_format($pendingTotal, 2) }}</strong></p>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg max-h-96 overflow-y-auto">
                <table class="min-w-full table-auto border">
                    <thead class="bg-gray-200">
                        <tr>
                            <th class="px-4 py-2">Nombre</th>
                            <th class="px-4 py-2">Pendiente</th>
                            <th class="px-4 py-2">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($washers as $washer)
                        <tr class="border-b cursor-pointer" ondblclick="window.location='{{ route('washers.show', $washer) }}'">
                            <td class="px-4 py-2">{{ $washer->name }}</td>
                            <td class="px-4 py-2">RD$ {{ number_format($washer->pending_amount, 2) }}</td>
                            <td class="px-4 py-2">{{ $washer->active ? 'Activo' : 'Inactivo' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <x-modal name="pay-all" focusable>
        <form method="POST" action="{{ route('washers.payAll') }}" class="p-6 space-y-4" x-data="{ paymentDate: '{{ now()->toDateString() }}' }">
            @csrf
            <h2 class="text-lg font-medium text-gray-900">Confirmar pago</h2>
            <p class="text-sm text-gray-600">Se pagarán RD$ {{ number_format($pendingTotal, 2) }} a todos los lavadores en la fecha <span x-text="paymentDate"></span>.</p>
            <div>
                <label class="block text-sm font-medium text-gray-700 mt-2">Fecha del pago</label>
                <input type="date" name="payment_date" x-model="paymentDate" class="form-input w-full" max="{{ now()->toDateString() }}" required>
            </div>
            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">Cancelar</x-secondary-button>
                <x-primary-button class="ml-3">Confirmar</x-primary-button>
            </div>
        </form>
    </x-modal>
</x-app-layout>
