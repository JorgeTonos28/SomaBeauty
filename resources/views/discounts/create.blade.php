<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Nuevo Descuento') }}
        </h2>
    </x-slot>

    <div class="py-6 max-w-4xl mx-auto sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('discounts.store') }}" class="space-y-6">
            @csrf
            <div>
                <label class="block font-medium text-sm text-gray-700">Tipo</label>
                <select name="type" class="form-select w-full" required>
                    <option value="service">Servicio</option>
                    <option value="product">Producto</option>
                </select>
            </div>
            <div>
                <label class="block font-medium text-sm text-gray-700">Elemento</label>
                <select name="discountable_id" class="form-select w-full" required>
                    <optgroup label="Servicios">
                        @foreach($services as $s)
                            <option value="{{ $s->id }}" data-type="service">{{ $s->name }}</option>
                        @endforeach
                    </optgroup>
                    <optgroup label="Productos">
                        @foreach($products as $p)
                            <option value="{{ $p->id }}" data-type="product">{{ $p->name }}</option>
                        @endforeach
                    </optgroup>
                </select>
            </div>
            <div>
                <label class="block font-medium text-sm text-gray-700">Tipo de descuento</label>
                <select name="amount_type" class="form-select w-full" required>
                    <option value="fixed">Monto fijo</option>
                    <option value="percentage">Porcentaje</option>
                </select>
            </div>
            <div>
                <label class="block font-medium text-sm text-gray-700">Cantidad</label>
                <input type="number" step="0.01" name="amount" required class="form-input w-full" />
            </div>
            <div>
                <label class="block font-medium text-sm text-gray-700">Fin del descuento</label>
                <input type="datetime-local" name="end_at" class="form-input w-full" />
            </div>
            <div class="flex justify-end">
                <x-secondary-button onclick="window.location='{{ route('discounts.index') }}'">Cancelar</x-secondary-button>
                <x-primary-button class="ml-3">Guardar</x-primary-button>
            </div>
        </form>
    </div>
</x-app-layout>
