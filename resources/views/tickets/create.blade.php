<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Nuevo Ticket de Facturación') }}
        </h2>
    </x-slot>

    <div x-data="ticketForm()" class="py-6 max-w-4xl mx-auto sm:px-6 lg:px-8">

        <form x-ref="form" action="{{ route('tickets.store') }}" method="POST" @submit.prevent="submitForm" class="space-y-6 pb-32">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700">Nombre del Cliente</label>
                <input type="text" name="customer_name" required class="form-input w-full mt-1">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Cédula</label>
                <input type="text" name="customer_cedula" class="form-input w-full mt-1">
            </div>

            <!-- Servicios -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Servicios</label>
                <div id="wash-fields" style="display:none" class="space-y-4">
                    <!-- Tipo de Vehículo -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tipo de Vehículo</label>
                        <select name="vehicle_type_id" class="form-select w-full mt-1">
                            <option value="">-- Seleccionar --</option>
                            @foreach ($vehicleTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Lavador -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Lavador</label>
                        <select name="washer_id" class="form-select w-full mt-1">
                            <option value="">-- Seleccionar --</option>
                            @foreach ($washers as $washer)
                                <option value="{{ $washer->id }}">{{ $washer->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Lista Servicios -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Servicios Realizados</label>
                        @foreach ($services as $service)
                            <div class="flex items-center space-x-2 mt-1">
                                <input type="checkbox" name="service_ids[]" value="{{ $service->id }}">
                                <label>{{ $service->name }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div id="drink-fields" class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tragos Vendidos</label>
                    <div id="drink-list"></div>
                    <button type="button" onclick="addDrinkRow()" class="mt-2 text-sm text-blue-600 hover:underline">+ Agregar trago</button>
                </div>

                <div class="mt-2 space-x-4">
                    <button type="button" id="wash-toggle" onclick="toggleWash()" class="text-sm text-blue-600 hover:underline">Agregar Lavado</button>
                </div>
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
            <div class="flex items-center gap-6 mt-4 fixed bottom-0 inset-x-0 mx-auto max-w-4xl bg-white p-4 shadow z-10 sm:px-6 lg:px-8">
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

        <x-modal name="error-modal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Se encontraron errores</h2>
                <ul class="list-disc list-inside text-sm text-red-600" id="error-list"></ul>
                <div class="mt-6 flex justify-end">
                    <x-secondary-button x-on:click="closeError()">Cerrar</x-secondary-button>
                </div>
            </div>
        </x-modal>
    </div>

    <script>
        const servicePrices = @json($servicePrices);
        const productPrices = @json($productPrices);
        const drinkPrices = @json($drinkPrices);

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

            document.querySelectorAll('#drink-list > div').forEach(row => {
                const drinkId = row.querySelector('select').value;
                const qty = parseFloat(row.querySelector('input[name="drink_quantities[]"]').value) || 0;
                const price = drinkPrices[drinkId] ? parseFloat(drinkPrices[drinkId]) : 0;
                total += price * qty;
            });

            document.getElementById('total_amount').innerText = total.toFixed(2);
            updateChange();
        }

        function updateChange() {
            const total = parseFloat(document.getElementById('total_amount').innerText) || 0;
            const paidField = document.getElementById('paid_amount');
            const paid = paidField.value === '' ? null : parseFloat(paidField.value);
            const change = paid === null ? 0 : paid - total;
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

        function addDrinkRow() {
            const container = document.getElementById('drink-list');
            const row = document.createElement('div');
            row.classList.add('flex', 'gap-4', 'mb-2', 'items-center');
            row.innerHTML = `
                <select name="drink_ids[]" class="form-select w-full" onchange="updateTotal()">
                    <option value="">-- Seleccionar trago --</option>
                    @foreach ($drinks as $drink)
                        <option value="{{ $drink->id }}">{{ $drink->name }} (RD$ {{ number_format($drink->price, 2) }})</option>
                    @endforeach
                </select>
                <input type="number" name="drink_quantities[]" placeholder="Cantidad" min="1" class="form-input w-24" oninput="updateTotal()">
                <button type="button" class="text-red-600" onclick="this.parentElement.remove(); updateTotal();">x</button>
            `;
            container.appendChild(row);
        }

        function toggleWash() {
            const wash = document.getElementById('wash-fields');
            const btn = document.getElementById('wash-toggle');
            if (wash.style.display === 'none') {
                wash.style.display = '';
                btn.textContent = 'Quitar lavado';
            } else {
                wash.style.display = 'none';
                btn.textContent = 'Agregar Lavado';
                wash.querySelectorAll('select, input[type=checkbox]').forEach(el => {
                    if (el.tagName === 'SELECT') el.value = '';
                    if (el.type === 'checkbox') el.checked = false;
                });
                updateTotal();
            }
        }


        document.querySelectorAll('input[name="service_ids[]"], select[name="vehicle_type_id"]').forEach(el => {
            el.addEventListener('change', updateTotal);
        });

        updateTotal();

        function ticketForm() {
            return {
                errors: [],
                async submitForm() {
                    const form = this.$refs.form;
                    this.errors = [];
                    try {
                        const res = await fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value
                            },
                            body: new FormData(form)
                        });
                        if (res.ok) {
                            window.location = '{{ route('tickets.index') }}';
                            return;
                        }
                        if (res.status === 422) {
                            const data = await res.json();
                            this.errors = Object.values(data.errors).flat();
                        } else {
                            const data = await res.json().catch(() => ({ message: 'Error inesperado' }));
                            this.errors = [data.message || 'Error inesperado'];
                        }
                    } catch (e) {
                        this.errors = ['Error de red'];
                    }
                    const list = document.getElementById('error-list');
                    list.innerHTML = '';
                    this.errors.forEach(msg => {
                        const li = document.createElement('li');
                        li.textContent = msg;
                        list.appendChild(li);
                    });
                    window.dispatchEvent(new CustomEvent('open-modal', { detail: 'error-modal' }));
                },
                closeError() {
                    window.dispatchEvent(new CustomEvent('close-modal', { detail: 'error-modal' }));
                }
            }
        }
    </script>
</x-app-layout>
