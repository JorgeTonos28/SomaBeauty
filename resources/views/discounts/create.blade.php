<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Nuevo Descuento') }}
        </h2>
    </x-slot>

    <div class="py-6 max-w-4xl mx-auto sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('discounts.store') }}" class="space-y-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block font-medium text-sm text-gray-700">Servicio</label>
                    <select name="service_id" class="form-select w-full">
                        <option value="">--</option>
                        @foreach($services as $s)
                            <option value="{{ $s->id }}" data-price="{{ optional($s->prices->first())->price }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-medium text-sm text-gray-700">Trago</label>
                    <select name="drink_id" class="form-select w-full">
                        <option value="">--</option>
                        @foreach($drinks as $d)
                            <option value="{{ $d->id }}" data-price="{{ $d->price }}">{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-medium text-sm text-gray-700">Producto</label>
                    <select name="product_id" class="form-select w-full">
                        <option value="">--</option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}" data-price="{{ $p->price }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block font-medium text-sm text-gray-700">Porcentaje</label>
                    <input type="number" step="0.01" name="amount_percentage" id="amount_percentage" class="form-input w-full" />
                </div>
                <div>
                    <label class="block font-medium text-sm text-gray-700">Cantidad (RD$)</label>
                    <input type="number" step="0.01" name="amount" id="amount_fixed" class="form-input w-full" />
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block font-medium text-sm text-gray-700">Fecha de inicio</label>
                    <input type="datetime-local" name="start_at" class="form-input w-full" />
                </div>
                <div>
                    <label class="block font-medium text-sm text-gray-700">Fecha de término</label>
                    <input type="datetime-local" name="end_at" class="form-input w-full" />
                </div>
            </div>
            <div class="flex justify-end">
                <x-secondary-button onclick="window.location='{{ route('discounts.index') }}'">Cancelar</x-secondary-button>
                <x-primary-button class="ml-3">Guardar</x-primary-button>
            </div>
        </form>
    </div>
    <script>
        const selects = document.querySelectorAll('select[name$="_id"]');
        const percent = document.getElementById('amount_percentage');
        const fixed = document.getElementById('amount_fixed');
        function getPrice() {
            for (const sel of selects) {
                if (sel.value) {
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
