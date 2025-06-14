<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Descuento') }}
        </h2>
    </x-slot>

    <div class="py-6 max-w-4xl mx-auto sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('discounts.update', $discount) }}" class="space-y-6">
            @csrf
            @method('PUT')
            <div>
                <label class="block font-medium text-sm text-gray-700">Tipo</label>
                <select name="type" class="form-select w-full" required>
                    <option value="service" @selected($discount->discountable_type == App\Models\Service::class)>Servicio</option>
                    <option value="product" @selected($discount->discountable_type == App\Models\Product::class)>Producto</option>
                </select>
            </div>
            <div>
                <label class="block font-medium text-sm text-gray-700">Elemento</label>
                <select name="discountable_id" class="form-select w-full" required>
                    <optgroup label="Servicios">
                        @foreach($services as $s)
                            <option value="{{ $s->id }}" @selected($discount->discountable_type == App\Models\Service::class && $discount->discountable_id == $s->id)>{{ $s->name }}</option>
                        @endforeach
                    </optgroup>
                    <optgroup label="Productos">
                        @foreach($products as $p)
                            <option value="{{ $p->id }}" @selected($discount->discountable_type == App\Models\Product::class && $discount->discountable_id == $p->id)>{{ $p->name }}</option>
                        @endforeach
                    </optgroup>
                </select>
            </div>
            <div>
                <label class="block font-medium text-sm text-gray-700">Tipo de descuento</label>
                <select name="amount_type" class="form-select w-full" required>
                    <option value="fixed" @selected($discount->amount_type == 'fixed')>Monto fijo</option>
                    <option value="percentage" @selected($discount->amount_type == 'percentage')>Porcentaje</option>
                </select>
            </div>
            <div>
                <label class="block font-medium text-sm text-gray-700">Cantidad</label>
                <input type="number" step="0.01" name="amount" value="{{ $discount->amount }}" required class="form-input w-full" />
            </div>
            <div>
                <label class="block font-medium text-sm text-gray-700">Fin del descuento</label>
                <input type="datetime-local" name="end_at" value="{{ optional($discount->end_at)->format('Y-m-d\TH:i') }}" class="form-input w-full" />
            </div>
            <div class="flex justify-end space-x-2">
                <x-secondary-button onclick="window.location='{{ route('discounts.index') }}'">Cancelar</x-secondary-button>
                <x-primary-button class="ml-3">Actualizar</x-primary-button>
            </div>
        </form>

        <div class="flex justify-end mt-4 space-x-2">
            @if($discount->active)
                <form method="POST" action="{{ route('discounts.deactivate', $discount) }}">
                    @csrf
                    @method('PUT')
                    <x-secondary-button onclick="event.preventDefault(); this.closest('form').submit();">Desactivar</x-secondary-button>
                </form>
            @else
                <form method="POST" action="{{ route('discounts.activate', $discount) }}">
                    @csrf
                    @method('PUT')
                    <x-secondary-button onclick="event.preventDefault(); this.closest('form').submit();">Activar</x-secondary-button>
                </form>
            @endif
            <form method="POST" action="{{ route('discounts.destroy', $discount) }}" onsubmit="return confirm('¿Eliminar descuento?');">
                @csrf
                @method('DELETE')
                <x-danger-button>Eliminar</x-danger-button>
            </form>
        </div>
    </div>
</x-app-layout>
