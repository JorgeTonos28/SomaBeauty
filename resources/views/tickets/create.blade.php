<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Nuevo Ticket de Facturación') }}
        </h2>
    </x-slot>

    <div x-data="ticketForm()" class="py-6 max-w-4xl mx-auto sm:px-6 lg:px-8">

        <form x-ref="form" action="{{ route('tickets.store') }}" method="POST" @submit.prevent="submitForm($event)" class="space-y-6 pb-32">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700">Nombre del Cliente</label>
                <input type="text" name="customer_name" required class="form-input w-full mt-1">
            </div>

            <!-- Servicios -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Servicios</label>
                <div id="wash-fields" style="display:none" class="space-y-4">
                    <!-- Placa -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Placa</label>
                        <input type="text" name="plate" id="plate" list="plate-options" class="form-input w-full mt-1">
                        <datalist id="plate-options"></datalist>
                    </div>

                    <!-- Marca -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Marca</label>
                        <input type="text" name="brand" class="form-input w-full mt-1">
                    </div>

                    <!-- Modelo -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Modelo</label>
                        <input type="text" name="model" class="form-input w-full mt-1">
                    </div>

                    <!-- Color -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Color</label>
                        <input type="text" name="color" class="form-input w-full mt-1">
                    </div>

                    <!-- Año -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Año</label>
                        <input type="number" name="year" class="form-input w-full mt-1">
                    </div>

                    <!-- Tipo de Vehículo -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tipo de Vehículo</label>
                        <select name="vehicle_type_id" class="form-select w-full mt-1">
                            <option value="">-- Seleccionar --</option>
                            @foreach ($vehicleTypes as $type)
                                <option value="{{ $type->id }}" data-name="{{ $type->name }}">{{ $type->name }}</option>
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
                        <div id="service-list">
                        @foreach ($services as $service)
                            <div class="flex items-center space-x-2 mt-1">
                                <input type="checkbox" name="service_ids[]" value="{{ $service->id }}">
                                <label data-service-id="{{ $service->id }}" data-name="{{ $service->name }}">{{ $service->name }}</label>
                            </div>
                        @endforeach
                        </div>
                        <div class="mt-2 text-sm">
                            <span>Total lavado: RD$ <span id="wash_total">0.00</span></span>
                            <span class="ml-4">Descuento: RD$ <span id="wash_discount">0.00</span></span>
                        </div>
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
                <input type="number" name="paid_amount" id="paid_amount" step="0.01" class="form-input w-full mt-1" oninput="updateChange()">
            </div>

            <!-- Método de Pago -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Método de Pago</label>
                <select name="payment_method" id="payment_method" class="form-select w-full mt-1" onchange="toggleBank()">
                    <option value="efectivo">Efectivo</option>
                    <option value="tarjeta">Tarjeta</option>
                    <option value="transferencia">Transferencia</option>
                    <option value="mixto">Mixto</option>
                </select>
            </div>

            <div id="bank-field" style="display:none">
                <label class="block text-sm font-medium text-gray-700">Cuenta Bancaria</label>
                <select name="bank_account_id" class="form-select w-full mt-1">
                    <option value="">-- Seleccionar --</option>
                    @foreach($bankAccounts as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->bank }} - {{ $acc->account }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Botón y Resumen -->
            <div class="flex items-center gap-6 mt-4 fixed bottom-0 inset-x-0 mx-auto max-w-4xl bg-white p-4 shadow z-10 sm:px-6 lg:px-8">
                <div class="flex-1 space-x-4">
                    <span>Descuento: RD$ <span id="discount_total">0.00</span></span>
                    <span>Total: RD$ <span id="total_amount">0.00</span></span>
                    <span>Cambio: RD$ <span id="change_display">0.00</span></span>
                </div>
                <button type="submit" name="ticket_action" value="pending" class="px-4 py-2 text-white bg-blue-600 rounded hover:bg-blue-700">
                    Crear
                </button>
                <button type="submit" name="ticket_action" value="pay" class="px-4 py-2 text-white bg-green-600 rounded hover:bg-green-700">
                    Pagar
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
        const productStocks = @json($productStocks);
        const drinkPrices = @json($drinkPrices);
        const serviceDiscounts = @json($serviceDiscounts);
        const productDiscounts = @json($productDiscounts);
        const drinkDiscounts = @json($drinkDiscounts);

        let currentTotal = 0;
        let currentDiscount = 0;

        function formatCurrency(value) {
            return value.toLocaleString('es-DO', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function updateServiceLabels() {
            const vehicleTypeId = document.querySelector('select[name="vehicle_type_id"]').value;
            document.querySelectorAll('#service-list label[data-service-id]').forEach(label => {
                const id = label.dataset.serviceId;
                const name = label.dataset.name;
                let price = servicePrices[id] && servicePrices[id][vehicleTypeId] ? parseFloat(servicePrices[id][vehicleTypeId]) : 0;
                const disc = serviceDiscounts[id];
                let final = price;
                if(disc){
                    const d = disc.type === 'fixed' ? parseFloat(disc.amount) : price * parseFloat(disc.amount)/100;
                    final = Math.max(0, price - d);
                }
                let text = name + ' (RD$ ' + price.toFixed(2) + ')';
                if(final !== price) text += ' -> (' + final.toFixed(2) + ')';
                label.textContent = text;
            });
        }

        function updateVehicleOptions() {
            document.querySelectorAll('select[name="vehicle_type_id"] option[data-name]').forEach(opt => {
                const vtId = opt.value;
                const name = opt.dataset.name;
                if(!vtId){
                    opt.textContent = '-- Seleccionar --';
                    return;
                }
                let price = 0;
                let discTotal = 0;
                document.querySelectorAll('input[name="service_ids[]"]:checked').forEach(cb => {
                    const sid = cb.value;
                    const p = servicePrices[sid] && servicePrices[sid][vtId] ? parseFloat(servicePrices[sid][vtId]) : 0;
                    price += p;
                    const disc = serviceDiscounts[sid];
                    if(disc){
                        const d = disc.type === 'fixed' ? parseFloat(disc.amount) : p * parseFloat(disc.amount)/100;
                        discTotal += d;
                    }
                });
                const final = Math.max(0, price - discTotal);
                let text = name + ' (RD$ ' + price.toFixed(2) + ')';
                if(discTotal > 0) text += ' -> (' + final.toFixed(2) + ')';
                opt.textContent = text;
            });
        }

        function updateTotal() {
            const vehicleTypeId = document.querySelector('select[name="vehicle_type_id"]').value;
            let total = 0;
            let discount = 0;
            let serviceTotal = 0;
            let serviceDisc = 0;

            document.querySelectorAll('input[name="service_ids[]"]:checked').forEach(cb => {
                const serviceId = cb.value;
                let price = servicePrices[serviceId] && servicePrices[serviceId][vehicleTypeId] ? parseFloat(servicePrices[serviceId][vehicleTypeId]) : 0;
                const disc = serviceDiscounts[serviceId];
                if(disc){
                    const d = disc.type === 'fixed' ? parseFloat(disc.amount) : price * parseFloat(disc.amount) / 100;
                    discount += d;
                    serviceDisc += d;
                    price = Math.max(0, price - d);
                }
                total += price;
                serviceTotal += price;
            });

            document.querySelectorAll('#product-list > div').forEach(row => {
                const productId = row.querySelector('select').value;
                const qty = parseFloat(row.querySelector('input[name="quantities[]"]').value) || 0;
                let price = productPrices[productId] ? parseFloat(productPrices[productId]) : 0;
                const disc = productDiscounts[productId];
                if(disc){
                    const d = disc.type === 'fixed' ? parseFloat(disc.amount) : price * parseFloat(disc.amount) / 100;
                    discount += d * qty;
                    price = Math.max(0, price - d);
                }
                total += price * qty;
            });

            document.querySelectorAll('#drink-list > div').forEach(row => {
                const drinkId = row.querySelector('select').value;
                const qty = parseFloat(row.querySelector('input[name="drink_quantities[]"]').value) || 0;
                let price = drinkPrices[drinkId] ? parseFloat(drinkPrices[drinkId]) : 0;
                const disc = drinkDiscounts[drinkId];
                if(disc){
                    const d = disc.type === 'fixed' ? parseFloat(disc.amount) : price * parseFloat(disc.amount) / 100;
                    discount += d * qty;
                    price = Math.max(0, price - d);
                }
                total += price * qty;
            });

            document.getElementById('wash_total').innerText = formatCurrency(serviceTotal);
            document.getElementById('wash_discount').innerText = formatCurrency(serviceDisc);
            currentTotal = total;
            currentDiscount = discount;
            document.getElementById('total_amount').innerText = formatCurrency(total);
            document.getElementById('discount_total').innerText = formatCurrency(discount);
            updateServiceLabels();
            updateVehicleOptions();
            updateChange();
        }

        function updateChange() {
            const total = currentTotal;
            const paidField = document.getElementById('paid_amount');
            const paid = paidField.value === '' ? null : parseFloat(paidField.value);
            const change = paid === null ? 0 : paid - total;
            document.getElementById('change_display').innerText = formatCurrency(change);
        }

        function addProductRow() {
            const container = document.getElementById('product-list');
            const row = document.createElement('div');
            row.classList.add('flex', 'gap-4', 'mb-2', 'items-center');
            row.innerHTML = `
                <select name="product_ids[]" class="form-select w-full" onchange="updateTotal(); checkStock(this.parentElement)">
                    <option value="">-- Seleccionar producto --</option>
                    @foreach ($products as $product)
                        @php
                            $disc = $productDiscounts->get($product->id);
                            $new = null;
                            if($disc){
                                $new = $disc['type'] === 'fixed'
                                    ? max(0, $product->price - $disc['amount'])
                                    : max(0, $product->price - $product->price * $disc['amount']/100);
                            }
                        @endphp
                        <option value="{{ $product->id }}">
                            {{ $product->name }} (RD$ {{ number_format($product->price, 2) }})
                            @if($new !== null)
                                <span class="text-red-600"> -> ({{ number_format($new, 2) }})</span>
                            @endif
                        </option>
                    @endforeach
                </select>
                <input type="number" name="quantities[]" placeholder="Cantidad" min="1" class="form-input w-24" oninput="checkStock(this.parentElement); updateTotal()">
                <button type="button" class="text-red-600" onclick="this.parentElement.remove(); updateTotal();">x</button>
            `;
            container.appendChild(row);
            checkStock(row);
        }

        function checkStock(row) {
            const select = row.querySelector('select');
            const qtyInput = row.querySelector('input[name="quantities[]"]');
            const pid = select.value;
            if (!pid) return;
            const stock = parseInt(productStocks[pid] ?? 0);
            const qty = parseInt(qtyInput.value || 0);
            if (qty > stock) {
                qtyInput.value = stock;
                const list = document.getElementById('error-list');
                list.innerHTML = `<li>Stock insuficiente para el producto seleccionado</li>`;
                window.dispatchEvent(new CustomEvent('open-modal', { detail: 'error-modal' }));
            }
        }

        function addDrinkRow() {
            const container = document.getElementById('drink-list');
            const row = document.createElement('div');
            row.classList.add('flex', 'gap-4', 'mb-2', 'items-center');
            row.innerHTML = `
                <select name="drink_ids[]" class="form-select w-full" onchange="updateTotal()">
                    <option value="">-- Seleccionar trago --</option>
                    @foreach ($drinks as $drink)
                        @php
                            $disc = $drinkDiscounts->get($drink->id);
                            $new = null;
                            if($disc){
                                $new = $disc['type'] === 'fixed'
                                    ? max(0, $drink->price - $disc['amount'])
                                    : max(0, $drink->price - $drink->price * $disc['amount']/100);
                            }
                        @endphp
                        <option value="{{ $drink->id }}">
                            {{ $drink->name }} (RD$ {{ number_format($drink->price, 2) }})
                            @if($new !== null)
                                <span class="text-red-600"> -> ({{ number_format($new, 2) }})</span>
                            @endif
                        </option>
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

        function toggleBank() {
            const field = document.getElementById('bank-field');
            const method = document.getElementById('payment_method').value;
            field.style.display = method === 'transferencia' ? '' : 'none';
        }

        const plateInput = document.getElementById('plate');
        const plateList = document.getElementById('plate-options');
        let plateData = [];

        plateInput.addEventListener('input', async () => {
            const q = plateInput.value;
            if (!q) { plateList.innerHTML = ''; return; }
            try {
                const res = await fetch(`{{ route('vehicles.search') }}?plate=${encodeURIComponent(q)}`, {headers:{'Accept':'application/json'}});
                if(res.ok){
                    plateData = await res.json();
                    plateList.innerHTML = '';
                    plateData.forEach(v => {
                        const opt = document.createElement('option');
                        opt.value = v.plate;
                        opt.label = `${v.model} | ${v.color} | ${v.year ?? ''} | ${v.plate} | ${v.type}`;
                        plateList.appendChild(opt);
                    });
                }
            } catch(e) {}
        });

        plateInput.addEventListener('change', () => {
            const plate = plateInput.value;
            const found = plateData.find(v => v.plate === plate);
            if(found){
                document.querySelector('input[name="brand"]').value = found.brand;
                document.querySelector('input[name="model"]').value = found.model;
                document.querySelector('input[name="color"]').value = found.color;
                document.querySelector('input[name="year"]').value = found.year || '';
                document.querySelector('select[name="vehicle_type_id"]').value = found.vehicle_type_id;
                updateTotal();
            }
        });


        document.querySelectorAll('input[name="service_ids[]"], select[name="vehicle_type_id"]').forEach(el => {
            el.addEventListener('change', updateTotal);
        });

        updateTotal();
        toggleBank();

        function ticketForm() {
            return {
                errors: [],
                async submitForm(e) {
                    const form = this.$refs.form;
                    const submitter = e?.submitter;
                    this.errors = [];

                    if (submitter?.value === 'pending') {
                        const paid = form.querySelector('[name=paid_amount]').value;
                        if (paid && parseFloat(paid) > 0) {
                            this.errors.push('Si desea pagar el ticket use el botón "Pagar".');
                            this.showErrors();
                            return;
                        }
                    }

                    try {
                        const res = await fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value
                            },
                            body: new FormData(form, submitter)
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
                    this.showErrors();
                },
                closeError() {
                    window.dispatchEvent(new CustomEvent('close-modal', { detail: 'error-modal' }));
                }
                ,
                showErrors() {
                    const list = document.getElementById('error-list');
                    list.innerHTML = '';
                    this.errors.forEach(msg => {
                        const li = document.createElement('li');
                        li.textContent = msg;
                        list.appendChild(li);
                    });
                    window.dispatchEvent(new CustomEvent('open-modal', { detail: 'error-modal' }));
                }
            }
        }
    </script>
</x-app-layout>
