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
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block font-medium text-sm text-gray-700">Servicio</label>
                    <select name="service_id" class="form-select w-full">
                        <option value="">--</option>
                        @foreach($services as $s)
                            <option value="{{ $s->id }}" data-price="{{ optional($s->prices->first())->price }}" @selected($discount->discountable_type == App\Models\Service::class && $discount->discountable_id == $s->id)>{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-medium text-sm text-gray-700">Trago</label>
                    <select name="drink_id" class="form-select w-full">
                        <option value="">--</option>
                        @foreach($drinks as $d)
                            <option value="{{ $d->id }}" data-price="{{ $d->price }}" @selected($discount->discountable_type == App\Models\Drink::class && $discount->discountable_id == $d->id)>{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-medium text-sm text-gray-700">Producto</label>
                    <select name="product_id" class="form-select w-full">
                        <option value="">--</option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}" data-price="{{ $p->price }}" @selected($discount->discountable_type == App\Models\Product::class && $discount->discountable_id == $p->id)>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block font-medium text-sm text-gray-700">Porcentaje</label>
                    <input type="number" step="0.01" name="amount_percentage" id="amount_percentage" value="{{ $discount->amount_type=='percentage' ? $discount->amount : '' }}" class="form-input w-full" />
                </div>
                <div>
                    <label class="block font-medium text-sm text-gray-700">Cantidad (RD$)</label>
                    <input type="number" step="0.01" name="amount" id="amount_fixed" value="{{ $discount->amount_type=='fixed' ? $discount->amount : '' }}" class="form-input w-full" />
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block font-medium text-sm text-gray-700">Fecha de inicio</label>
                    <input type="datetime-local" name="start_at" value="{{ optional($discount->start_at)->format('Y-m-d\TH:i') }}" class="form-input w-full" />
                </div>
                <div>
                    <label class="block font-medium text-sm text-gray-700">Fecha de término</label>
                    <input type="datetime-local" name="end_at" value="{{ optional($discount->end_at)->format('Y-m-d\TH:i') }}" class="form-input w-full" />
                </div>
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
    <script>
        const selects = document.querySelectorAll('select[name$="_id"]');
        const percent = document.getElementById('amount_percentage');
        const fixed = document.getElementById('amount_fixed');
        function getPrice(){
            for(const sel of selects){
                if(sel.value){
                    return parseFloat(sel.options[sel.selectedIndex].dataset.price || 0);
                }
            }
            return 0;
        }
        function syncFromPercent(){
            const price = getPrice();
            if(price){
                fixed.value = (price * (percent.value||0)/100).toFixed(2);
            }
        }
        function syncFromFixed(){
            const price = getPrice();
            if(price){
                percent.value = ((fixed.value||0)/price*100).toFixed(2);
            }
        }
        percent.addEventListener('input', syncFromPercent);
        fixed.addEventListener('input', syncFromFixed);
    </script>
</x-app-layout>
