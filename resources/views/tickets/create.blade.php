<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Nuevo Ticket de Facturación') }}
        </h2>
    </x-slot>

    <div class="py-6 max-w-4xl mx-auto sm:px-6 lg:px-8">
        @if ($errors->any())
            <div class="mb-4 text-sm text-red-600">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('tickets.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Tipo de Vehículo -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Tipo de Vehículo</label>
                <select name="vehicle_type_id" required class="form-select w-full mt-1">
                    <option value="">-- Seleccionar --</option>
                    @foreach ($vehicleTypes as $type)
                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Lavador -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Lavador</label>
                <select name="washer_id" required class="form-select w-full mt-1">
                    <option value="">-- Seleccionar --</option>
                    @foreach ($washers as $washer)
                        <option value="{{ $washer->id }}">{{ $washer->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Servicios -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Servicios Realizados</label>
                @foreach ($services as $service)
                    <div class="flex items-center space-x-2 mt-1">
                        <input type="checkbox" name="service_ids[]" value="{{ $service->id }}">
                        <label>{{ $service->name }}</label>
                    </div>
                @endforeach
            </div>

            <!-- Productos -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Productos Vendidos</label>

                <div id="product-list"></div>

                <button type="button" onclick="addProductRow()" class="mt-2 text-sm text-blue-600 hover:underline">
                    + Agregar otro producto
                </button>
            </div>

            <!-- Monto Pagado -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Monto Pagado (RD$)</label>
                <input type="number" name="paid_amount" id="paid_amount" required step="0.01" class="form-input w-full mt-1" oninput="updateChange()">
            </div>

            <!-- Método de Pago -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Método de Pago</label>
                <select name="payment_method" required class="form-select w-full mt-1">
                    <option value="efectivo">Efectivo</option>
                    <option value="tarjeta">Tarjeta</option>
                    <option value="transferencia">Transferencia</option>
                    <option value="mixto">Mixto</option>
                </select>
            </div>

            <!-- Botón y Resumen -->
            <div class="flex items-center gap-6 mt-4 sticky bottom-0 bg-white p-4 shadow">
                <div class="flex-1 space-x-4">
                    <span>Total: RD$ <span id="total_amount">0.00</span></span>
                    <span>Cambio: RD$ <span id="change_display">0.00</span></span>
                </div>
                <button type="submit" class="px-4 py-2 text-white bg-green-600 rounded hover:bg-green-700">
                    Guardar Ticket
                </button>
                <a href="{{ route('tickets.index') }}" class="text-gray-600 hover:underline">Cancelar</a>
            </div>
        </form>
    </div>

    <script>
        const servicePrices = @json($servicePrices);
        const productPrices = @json($productPrices);

        function updateTotal() {
            const vehicleTypeId = document.querySelector('select[name="vehicle_type_id"]').value;
            let total = 0;

            document.querySelectorAll('input[name="service_ids[]"]:checked').forEach(cb => {
                const serviceId = cb.value;
                const price = servicePrices[serviceId] && servicePrices[serviceId][vehicleTypeId] ? parseFloat(servicePrices[serviceId][vehicleTypeId]) : 0;
                total += price;
            });

            document.querySelectorAll('#product-list > div').forEach(row => {
                const productId = row.querySelector('select').value;
                const qty = parseFloat(row.querySelector('input[name="quantities[]"]').value) || 0;
                const price = productPrices[productId] ? parseFloat(productPrices[productId]) : 0;
                total += price * qty;
            });

            document.getElementById('total_amount').innerText = total.toFixed(2);
            updateChange();
        }

        function updateChange() {
            const total = parseFloat(document.getElementById('total_amount').innerText) || 0;
            const paid = parseFloat(document.getElementById('paid_amount').value) || 0;
            const change = paid - total;
            document.getElementById('change_display').innerText = change.toFixed(2);
        }

        function addProductRow() {
            const container = document.getElementById('product-list');
            const row = document.createElement('div');
            row.classList.add('flex', 'gap-4', 'mb-2', 'items-center');
            row.innerHTML = `
                <select name="product_ids[]" class="form-select w-full" onchange="updateTotal()">
                    <option value="">-- Seleccionar producto --</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }} (RD$ {{ number_format($product->price, 2) }})</option>
                    @endforeach
                </select>
                <input type="number" name="quantities[]" placeholder="Cantidad" min="1" class="form-input w-24" oninput="updateTotal()">
                <button type="button" class="text-red-600" onclick="this.parentElement.remove(); updateTotal();">x</button>
            `;
            container.appendChild(row);
        }

        document.querySelectorAll('input[name="service_ids[]"], select[name="vehicle_type_id"]').forEach(el => {
            el.addEventListener('change', updateTotal);
        });

        updateTotal();
    </script>
</x-app-layout>
