<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Editar Ticket') }}
        </h2>
    </x-slot>

    <div x-data="ticketForm()" class="py-6 max-w-4xl mx-auto sm:px-6 lg:px-8">

        <form x-ref="form" action="{{ route('tickets.update', $ticket) }}" method="POST" @submit.prevent="submitForm($event)" class="space-y-6 pb-32">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nombre del Cliente</label>
                    <input type="text" name="customer_name" value="{{ $ticket->customer_name }}" pattern="[A-Za-zÁÉÍÓÚáéíóúñÑ ]+" required class="form-input w-full mt-1">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Teléfono</label>
                    <input type="text" name="customer_phone" value="{{ $ticket->customer_phone }}" pattern="[0-9+() -]*" class="form-input w-full mt-1">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Fecha del Ticket</label>
                    <input type="date" name="ticket_date" value="{{ $ticket->created_at->format('Y-m-d') }}" max="{{ date('Y-m-d') }}" class="form-input w-full mt-1" onclick="this.showPicker()" onfocus="this.showPicker()">
                </div>
            </div>

            <!-- Servicios -->
            <details class="border rounded p-4" id="wash-section">
                <summary class="cursor-pointer font-medium text-gray-700">Agregar o quitar servicio</summary>
                <div id="wash-list" class="space-y-4 mt-4">
                    @foreach($ticketWashes as $i => $wData)
                        <div class="border rounded p-3 wash-item" data-total="{{ $wData['total'] }}" data-discount="{{ $wData['discount'] ?? 0 }}" data-commission-percentage="{{ $wData['commission_percentage'] !== null ? number_format($wData['commission_percentage'], 2, '.', '') : '' }}">
                            <div class="flex justify-between items-start gap-4">
                                <div>
                                    <p class="font-semibold text-gray-800">{{ $wData['service_name'] }}</p>
                                    @if(!empty($wData['price_label']))
                                        <p class="text-sm text-gray-600">{{ $wData['price_label'] }}</p>
                                    @endif
                                    <p class="text-sm text-gray-600">Estilista: {{ optional($wData['wash']->washer)->name ?? 'N/A' }}</p>
                                    @if(($wData['tip'] ?? 0) > 0)
                                        <p class="text-sm text-gray-600">Propina: RD$ {{ number_format($wData['tip'], 2) }}</p>
                                    @endif
                                    <p class="text-sm font-medium text-gray-800">Subtotal: RD$ {{ number_format($wData['total'], 2) }}</p>
                                </div>
                                <div class="flex gap-2 text-sm">
                                    <button type="button" class="text-blue-600 hover:underline" onclick="editWash(this)">Editar</button>
                                    <button type="button" class="text-red-600 hover:underline" onclick="removeWash(this)">Eliminar</button>
                                </div>
                            </div>
                            <input type="hidden" data-field="service_id" name="washes[{{ $i }}][service_id]" value="{{ $wData['service_id'] }}">
                            <input type="hidden" data-field="service_price_id" name="washes[{{ $i }}][service_price_id]" value="{{ $wData['service_price_id'] }}">
                            <input type="hidden" data-field="washer_id" name="washes[{{ $i }}][washer_id]" value="{{ $wData['wash']->washer_id }}">
                            <input type="hidden" data-field="tip" name="washes[{{ $i }}][tip]" value="{{ number_format($wData['tip'], 2, '.', '') }}">
                            <input type="hidden" data-field="commission_percentage" name="washes[{{ $i }}][commission_percentage]" value="{{ $wData['commission_percentage'] !== null ? number_format($wData['commission_percentage'], 2, '.', '') : '' }}">
                        </div>
                    @endforeach
                </div>

                <div id="wash-form" class="space-y-4 mt-4 hidden">
                    <!-- Servicio -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Servicio</label>
                        <select name="temp_service_id" class="form-select w-full mt-1" data-searchable onchange="handleTempServiceChange(this)">
                            <option value="">-- Seleccionar --</option>
                            @foreach ($services as $service)
                                <option value="{{ $service->id }}">{{ $service->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Opción de precio -->
                    <div id="price-option-wrapper" class="hidden">
                        <label class="block text-sm font-medium text-gray-700">Opción de precio</label>
                        <select name="temp_service_price_id" class="form-select w-full mt-1" onchange="updateTempPriceDisplay()">
                            <option value="">-- Seleccionar --</option>
                        </select>
                        <p class="text-sm text-gray-600 mt-1">Precio: RD$ <span id="temp_service_price">0.00</span></p>
                    </div>

                    <!-- Estilista -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Estilista</label>
                        <div class="flex space-x-2 mt-1">
                            <select name="temp_washer_id" class="form-select w-full" data-searchable>
                                <option value="">-- Seleccionar --</option>
                                @foreach ($washers as $washer)
                                    <option value="{{ $washer->id }}">{{ $washer->name }}</option>
                                @endforeach
                            </select>
                            <button type="button" onclick="clearWasher()" class="px-2 py-1 text-xs text-red-600 border border-red-600 rounded hover:bg-red-50">Quitar</button>
                        </div>
                    </div>

                    <!-- Propina -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Propina</label>
                        <input type="number" name="temp_tip" min="0" step="0.01" class="form-input w-full mt-1">
                    </div>
                    <div class="mt-2 space-x-2">
                        <button type="button" id="save-wash-btn" class="px-3 py-1 text-sm text-white bg-blue-600 rounded hover:bg-blue-700" onclick="saveWash()">Agregar servicio</button>
                        <button type="button" id="cancel-wash-btn" class="px-3 py-1 text-sm text-gray-700 bg-gray-200 rounded hover:bg-gray-300 hidden" onclick="cancelWashForm()">Cancelar</button>
                    </div>
                </div>

                <div class="mt-2">
                    <button type="button" id="show-wash-form-btn" class="text-sm text-blue-600" onclick="showWashForm()">Agregar servicio</button>
                </div>
            </details>

            <!-- Tragos -->
            <details class="border rounded p-4" id="drink-section">
                <summary class="cursor-pointer font-medium text-gray-700">Tragos Vendidos</summary>
                <div class="mt-4">
                    <div id="drink-list">
                        @foreach ($ticketDrinks as $td)
                        <div class="flex gap-4 mb-2 items-center">
                            <select name="drink_ids[]" class="form-select w-full" data-searchable onchange="updateTotal()">
                                <option value="">-- Seleccionar trago --</option>
                                @foreach ($drinks as $drink)
                                    @php $disc = $drinkDiscounts->get($drink->id); $new = null; if($disc){ $new = $disc['type'] === 'fixed' ? max(0,$drink->price-$disc['amount']) : max(0,$drink->price-$drink->price*$disc['amount']/100); } @endphp
                                    <option value="{{ $drink->id }}" {{ $drink->id == $td['id'] ? 'selected' : '' }}>
                                        {{ $drink->name }} (RD$ {{ number_format($drink->price,2) }})
                                        @if($new !== null)
                                            <span class="text-red-600"> -> ({{ number_format($new,2) }})</span>
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            <input type="number" name="drink_quantities[]" placeholder="Cantidad" min="1" class="form-input w-24" oninput="updateTotal()" value="{{ $td['qty'] }}">
                            <button type="button" class="text-red-600" onclick="this.parentElement.remove(); updateTotal();">x</button>
                        </div>
                        @endforeach
                    </div>
                    <button type="button" onclick="addDrinkRow()" class="mt-2 text-sm text-blue-600 hover:underline">+ Agregar trago</button>
                </div>
            </details>

            <!-- Productos -->
            <details class="border rounded p-4" id="product-section">
                <summary class="cursor-pointer font-medium text-gray-700">Productos Vendidos</summary>
                <div class="mt-4">
                    <div id="product-list">
                    @foreach ($ticketProducts as $tp)
                    <div class="flex gap-4 mb-2 items-center">
                        <select name="product_ids[]" class="form-select w-full" data-searchable onchange="updateTotal(); checkStock(this.parentElement)">
                            <option value="">-- Seleccionar producto --</option>
                            @foreach ($products as $product)
                                @php $disc = $productDiscounts->get($product->id); $new = null; if($disc){ $new = $disc['type'] === 'fixed' ? max(0,$product->price-$disc['amount']) : max(0,$product->price-$product->price*$disc['amount']/100); } @endphp
                                <option value="{{ $product->id }}" {{ $product->id == $tp['id'] ? 'selected' : '' }}>
                                    {{ $product->name }} (RD$ {{ number_format($product->price,2) }})
                                    @if($new !== null)
                                        <span class="text-red-600"> -> ({{ number_format($new,2) }})</span>
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        <input type="number" name="quantities[]" placeholder="Cantidad" min="1" class="form-input w-24" oninput="checkStock(this.parentElement); updateTotal()" value="{{ $tp['qty'] }}">
                        <button type="button" class="text-red-600" onclick="this.parentElement.remove(); updateTotal();">x</button>
                    </div>
                    @endforeach
                    </div>
                    <button type="button" onclick="addProductRow()" class="mt-2 text-sm text-blue-600 hover:underline">+ Agregar otro producto</button>
                </div>
            </details>

            <!-- Cargos adicionales -->
            <details class="border rounded p-4" id="charge-section">
                <summary class="cursor-pointer font-medium text-gray-700">Cargos Adicionales</summary>
                <div class="mt-4">
                    <div id="charge-list">
                        @foreach ($ticketExtras as $extra)
                        <div class="flex gap-4 mb-2 items-center">
                            <input type="text" name="charge_descriptions[]" placeholder="Descripción" class="form-input w-full" value="{{ $extra['description'] }}" />
                            <input type="number" name="charge_amounts[]" placeholder="Monto" step="0.01" class="form-input w-32" oninput="updateTotal()" value="{{ $extra['amount'] }}" />
                            <button type="button" class="text-red-600" onclick="this.parentElement.remove(); updateTotal();">x</button>
                        </div>
                        @endforeach
                    </div>
                    <button type="button" onclick="addChargeRow()" class="mt-2 text-sm text-blue-600 hover:underline">+ Agregar cargo</button>
                </div>
            </details>

            <!-- Monto Pagado -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Monto Pagado (RD$)</label>
                <div class="flex gap-2 items-center">
                    <input type="number" name="paid_amount" id="paid_amount" step="0.01" class="form-input w-full mt-1" oninput="updateChange()">
                    <button type="button" id="fill-paid" class="text-sm text-blue-600" onclick="setPaidFull()">Total</button>
                </div>
                <p id="paid_warning" class="text-sm text-red-600"></p>
            </div>

            <!-- Método de Pago -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Método de Pago</label>
                <select name="payment_method" id="payment_method" class="form-select w-full mt-1" onchange="toggleBank()">
                    <option value="efectivo" {{ $ticket->payment_method === 'efectivo' ? 'selected' : '' }}>Efectivo</option>
                    <option value="tarjeta" {{ $ticket->payment_method === 'tarjeta' ? 'selected' : '' }}>Tarjeta</option>
                    <option value="transferencia" {{ $ticket->payment_method === 'transferencia' ? 'selected' : '' }}>Transferencia</option>
                    <option value="mixto" {{ $ticket->payment_method === 'mixto' ? 'selected' : '' }}>Mixto</option>
                </select>
            </div>

            <div id="bank-field" style="{{ $ticket->payment_method === 'transferencia' ? '' : 'display:none' }}">
                <label class="block text-sm font-medium text-gray-700">Cuenta Bancaria</label>
                <select name="bank_account_id" class="form-select w-full mt-1">
                    <option value="">-- Seleccionar --</option>
                    @foreach($bankAccounts as $acc)
                        <option value="{{ $acc->id }}" {{ $acc->id == $ticket->bank_account_id ? 'selected' : '' }}>{{ $acc->bank }} - {{ $acc->account }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Resumen y Botones -->
            <div class="mt-4 sticky bottom-0 bg-white p-4 shadow z-10 sm:px-6 lg:px-8">
                <div class="flex flex-wrap gap-6 text-lg font-bold">
                    <span>Descuento: RD$ <span id="discount_total">0.00</span></span>
                    <span>Total: RD$ <span id="total_amount">0.00</span></span>
                    <span>Cambio: RD$ <span id="change_display">0.00</span></span>
                </div>
                <div class="flex items-center gap-6 mt-4">
                    <button type="submit" name="ticket_action" value="pending" class="px-4 py-2 text-white bg-blue-600 rounded hover:bg-blue-700">
                        Guardar
                    </button>
                    <button type="submit" name="ticket_action" value="pay" class="px-4 py-2 text-white bg-green-600 rounded hover:bg-green-700">
                        Pagar
                    </button>
                    <a href="{{ route('tickets.index') }}" class="px-4 py-2 text-white bg-red-600 rounded hover:bg-red-700">Cancelar</a>
                </div>
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
        const servicesCatalog = @json($services->mapWithKeys(function ($service) {
            return [$service->id => ['name' => $service->name]];
        }));
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

        function updateTotal() {
            let total = 0;
            let discount = 0;

            document.querySelectorAll('.wash-item').forEach(item => {
                total += parseFloat(item.dataset.total);
                discount += parseFloat(item.dataset.discount);
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

            document.querySelectorAll('#charge-list > div').forEach(row => {
                const amount = parseFloat(row.querySelector('input[name="charge_amounts[]"]').value) || 0;
                total += amount;
            });

            currentTotal = total;
            currentDiscount = discount;
            document.getElementById('total_amount').innerText = formatCurrency(total);
            document.getElementById('discount_total').innerText = formatCurrency(discount);
            document.getElementById('paid_amount').value = currentTotal.toFixed(2);
            updateChange();
        }

        function updateChange() {
            const total = currentTotal;
            const paidField = document.getElementById('paid_amount');
            const paid = paidField.value === '' ? null : parseFloat(paidField.value);
            const change = paid === null ? 0 : paid - total;
            document.getElementById('change_display').innerText = formatCurrency(change);
            const warn = document.getElementById('paid_warning');
            if (paid !== null && paid < total) {
                warn.textContent = 'Monto insuficiente';
            } else {
                warn.textContent = '';
            }
        }

        function setPaidFull(){
            document.getElementById('paid_amount').value = currentTotal.toFixed(2);
            updateChange();
        }

        function addProductRow() {
            const container = document.getElementById('product-list');
            const row = document.createElement('div');
            row.classList.add('flex', 'gap-4', 'mb-2', 'items-center');
            row.innerHTML = `
                <select name="product_ids[]" class="form-select w-full" data-searchable onchange="updateTotal(); checkStock(this.parentElement)">
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
            convertSelectToSearchable(row.querySelector('select'));
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
                <select name="drink_ids[]" class="form-select w-full" data-searchable onchange="updateTotal()">
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
            convertSelectToSearchable(row.querySelector('select'));
        }

        function addChargeRow() {
            const container = document.getElementById('charge-list');
            const row = document.createElement('div');
            row.classList.add('flex', 'gap-4', 'mb-2', 'items-center');
            row.innerHTML = `
                <input type="text" name="charge_descriptions[]" placeholder="Descripción" class="form-input w-full" />
                <input type="number" name="charge_amounts[]" placeholder="Monto" step="0.01" class="form-input w-32" oninput="updateTotal()" />
                <button type="button" class="text-red-600" onclick="this.parentElement.remove(); updateTotal();">x</button>
            `;
            container.appendChild(row);
        }

        function showWashForm() {
            const form = document.getElementById('wash-form');
            document.getElementById('show-wash-form-btn').classList.add('hidden');
            document.getElementById('cancel-wash-btn').classList.remove('hidden');
            document.getElementById('save-wash-btn').textContent = form.dataset.editIndex ? 'Guardar cambios' : 'Agregar servicio';
            form.classList.remove('hidden');
        }

        function resetWashForm() {
            const form = document.getElementById('wash-form');
            form.dataset.editIndex = '';
            const serviceSelect = form.querySelector('select[name="temp_service_id"]');
            serviceSelect.value = '';
            if (serviceSelect._syncSearchInput) {
                serviceSelect._syncSearchInput();
            }
            handleTempServiceChange(serviceSelect);
            const priceSelect = form.querySelector('select[name="temp_service_price_id"]');
            priceSelect.value = '';
            document.getElementById('temp_service_price').textContent = '0.00';
            const washerSelect = form.querySelector('select[name="temp_washer_id"]');
            washerSelect.value = '';
            if (washerSelect._syncSearchInput) {
                washerSelect._syncSearchInput();
            }
            form.querySelector('input[name="temp_tip"]').value = '';
        }

        function cancelWashForm() {
            const form = document.getElementById('wash-form');
            resetWashForm();
            form.classList.add('hidden');
            document.getElementById('show-wash-form-btn').classList.remove('hidden');
            document.getElementById('cancel-wash-btn').classList.add('hidden');
            document.getElementById('save-wash-btn').textContent = 'Agregar servicio';
        }

        function clearWasher(){
            const sel = document.querySelector('#wash-form select[name="temp_washer_id"]');
            sel.value='';
            if(sel._syncSearchInput) sel._syncSearchInput();
        }

        function showFormError(message) {
            const list = document.getElementById('error-list');
            list.innerHTML = `<li>${message}</li>`;
            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'error-modal' }));
        }

        function handleTempServiceChange(select) {
            const serviceId = select.value;
            const wrapper = document.getElementById('price-option-wrapper');
            const priceSelect = wrapper.querySelector('select');
            priceSelect.innerHTML = '<option value="">-- Seleccionar --</option>';
            priceSelect.value = '';
            document.getElementById('temp_service_price').textContent = '0.00';

            if (!serviceId) {
                wrapper.classList.add('hidden');
                return;
            }

            const options = servicePrices[serviceId] || [];
            if (!options.length) {
                wrapper.classList.add('hidden');
                return;
            }

            wrapper.classList.remove('hidden');
            options.forEach(opt => {
                const option = document.createElement('option');
                option.value = opt.id;
                option.textContent = `${opt.label} (RD$ ${parseFloat(opt.price).toFixed(2)})`;
                priceSelect.appendChild(option);
            });

            if (options.length === 1) {
                priceSelect.value = options[0].id;
                document.getElementById('temp_service_price').textContent = parseFloat(options[0].price).toFixed(2);
            }

            if (priceSelect._searchInput) {
                priceSelect._searchInput.value = priceSelect.options[priceSelect.selectedIndex]?.text || '';
            }
        }

        function updateTempPriceDisplay() {
            const form = document.getElementById('wash-form');
            const serviceId = form.querySelector('select[name="temp_service_id"]').value;
            const priceId = form.querySelector('select[name="temp_service_price_id"]').value;
            const options = servicePrices[serviceId] || [];
            const selected = options.find(opt => String(opt.id) === priceId);
            const price = selected ? parseFloat(selected.price) : 0;
            document.getElementById('temp_service_price').textContent = price.toFixed(2);
        }

        function createHiddenInput(field, value) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.dataset.field = field;
            input.value = value;
            return input;
        }

        function showFormError(message) {
            const list = document.getElementById('error-list');
            list.innerHTML = `<li>${message}</li>`;
            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'error-modal' }));
        }

        function handleTempServiceChange(select) {
            const serviceId = select.value;
            const wrapper = document.getElementById('price-option-wrapper');
            const priceSelect = wrapper.querySelector('select');
            priceSelect.innerHTML = '<option value="">-- Seleccionar --</option>';
            priceSelect.value = '';
            document.getElementById('temp_service_price').textContent = '0.00';

            if (!serviceId) {
                wrapper.classList.add('hidden');
                return;
            }

            const options = servicePrices[serviceId] || [];
            if (!options.length) {
                wrapper.classList.add('hidden');
                return;
            }

            wrapper.classList.remove('hidden');
            options.forEach(opt => {
                const option = document.createElement('option');
                option.value = opt.id;
                option.textContent = `${opt.label} (RD$ ${parseFloat(opt.price).toFixed(2)})`;
                priceSelect.appendChild(option);
            });

            if (options.length === 1) {
                priceSelect.value = options[0].id;
                document.getElementById('temp_service_price').textContent = parseFloat(options[0].price).toFixed(2);
            }

            if (priceSelect._searchInput) {
                priceSelect._searchInput.value = priceSelect.options[priceSelect.selectedIndex]?.text || '';
            }
        }

        function updateTempPriceDisplay() {
            const form = document.getElementById('wash-form');
            const serviceId = form.querySelector('select[name="temp_service_id"]').value;
            const priceId = form.querySelector('select[name="temp_service_price_id"]').value;
            const options = servicePrices[serviceId] || [];
            const selected = options.find(opt => String(opt.id) === priceId);
            const price = selected ? parseFloat(selected.price) : 0;
            document.getElementById('temp_service_price').textContent = price.toFixed(2);
        }

        function createHiddenInput(field, value) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.dataset.field = field;
            input.value = value;
            return input;
        }

        function saveWash() {
            const form = document.getElementById('wash-form');
            const serviceSelect = form.querySelector('select[name="temp_service_id"]');
            const serviceId = serviceSelect.value;
            if (!serviceId) {
                showFormError('Debe seleccionar un servicio.');
                return;
            }

            const options = servicePrices[serviceId] || [];
            if (!options.length) {
                showFormError('El servicio seleccionado no tiene opciones de precio configuradas.');
                return;
            }
            let priceOptionId = form.querySelector('select[name="temp_service_price_id"]').value;
            if (options.length === 1) {
                priceOptionId = options[0].id;
            }
            const priceOption = options.find(opt => String(opt.id) === String(priceOptionId));
            if (options.length > 1 && !priceOption) {
                showFormError('Seleccione una opción de precio.');
                return;
            }

            const washerSelect = form.querySelector('select[name="temp_washer_id"]');
            const washerId = washerSelect.value;
            const washerName = washerId ? washerSelect.options[washerSelect.selectedIndex]?.text : '';
            const tip = parseFloat(form.querySelector('input[name="temp_tip"]').value) || 0;

            const basePrice = priceOption ? parseFloat(priceOption.price) : 0;
            const discountInfo = serviceDiscounts[serviceId];
            let discount = 0;
            let finalPrice = basePrice;
            if (discountInfo) {
                const amount = parseFloat(discountInfo.amount);
                discount = discountInfo.type === 'fixed' ? amount : (basePrice * amount / 100);
                finalPrice = Math.max(0, basePrice - discount);
            }

            const total = finalPrice + tip;
            const serviceName = servicesCatalog[serviceId]?.name || serviceSelect.options[serviceSelect.selectedIndex]?.text || '';
            const priceLabel = priceOption ? priceOption.label : '';

            const editing = form.dataset.editIndex !== undefined && form.dataset.editIndex !== '';
            const index = editing ? parseInt(form.dataset.editIndex, 10) : document.querySelectorAll('#wash-list .wash-item').length;

            let wrapper;
            let existingCommission = '';
            if (editing) {
                wrapper = document.querySelectorAll('#wash-list .wash-item')[index];
                existingCommission = wrapper.querySelector('input[data-field="commission_percentage"]')?.value || '';
                wrapper.innerHTML = '';
            } else {
                wrapper = document.createElement('div');
                wrapper.className = 'border rounded p-3 wash-item';
                document.getElementById('wash-list').appendChild(wrapper);
            }

            wrapper.dataset.total = total;
            wrapper.dataset.discount = discount;
            wrapper.dataset.serviceId = serviceId;
            wrapper.dataset.priceLabel = priceLabel || '';
            wrapper.dataset.commissionPercentage = existingCommission || '';

            const info = document.createElement('div');
            info.className = 'flex justify-between items-start gap-4';
            info.innerHTML = `
                <div>
                    <p class="font-semibold text-gray-800">${serviceName}</p>
                    ${priceLabel ? `<p class="text-sm text-gray-600">${priceLabel}</p>` : ''}
                    ${washerName ? `<p class="text-sm text-gray-600">Estilista: ${washerName}</p>` : ''}
                    ${tip > 0 ? `<p class="text-sm text-gray-600">Propina: RD$ ${tip.toFixed(2)}</p>` : ''}
                    <p class="text-sm font-medium text-gray-800">Subtotal: RD$ ${total.toFixed(2)}</p>
                </div>
                <div class="flex gap-2 text-sm">
                    <button type="button" class="text-blue-600 hover:underline" onclick="editWash(this)">Editar</button>
                    <button type="button" class="text-red-600 hover:underline" onclick="removeWash(this)">Eliminar</button>
                </div>
            `;
            wrapper.appendChild(info);

            const hiddenFields = [
                createHiddenInput('service_id', serviceId),
                createHiddenInput('service_price_id', priceOption ? priceOption.id : ''),
                createHiddenInput('washer_id', washerId || ''),
                createHiddenInput('tip', tip.toFixed(2))
            ];
            if (existingCommission) {
                hiddenFields.push(createHiddenInput('commission_percentage', existingCommission));
            }
            hiddenFields.forEach(input => wrapper.appendChild(input));

            updateWashIndexes();
            resetWashForm();
            cancelWashForm();
            updateTotal();
        }

        function editWash(btn) {
            const wrapper = btn.closest('.wash-item');
            const index = Array.from(document.querySelectorAll('#wash-list .wash-item')).indexOf(wrapper);
            const form = document.getElementById('wash-form');
            form.dataset.editIndex = index;
            document.getElementById('save-wash-btn').textContent = 'Guardar cambios';

            const serviceId = wrapper.querySelector('input[data-field="service_id"]').value;
            const priceId = wrapper.querySelector('input[data-field="service_price_id"]').value;
            const washerId = wrapper.querySelector('input[data-field="washer_id"]').value;
            const tip = wrapper.querySelector('input[data-field="tip"]').value;

            const serviceSelect = form.querySelector('select[name="temp_service_id"]');
            serviceSelect.value = serviceId;
            if (serviceSelect._syncSearchInput) {
                serviceSelect._syncSearchInput();
            }
            handleTempServiceChange(serviceSelect);

            const priceSelect = form.querySelector('select[name="temp_service_price_id"]');
            if (priceId) {
                priceSelect.value = priceId;
                updateTempPriceDisplay();
            }

            const washerSelect = form.querySelector('select[name="temp_washer_id"]');
            washerSelect.value = washerId;
            if (washerSelect._syncSearchInput) {
                washerSelect._syncSearchInput();
            }
            form.querySelector('input[name="temp_tip"]').value = tip;

            showWashForm();
        }

        function removeWash(btn) {
            const wrapper = btn.closest('.wash-item');
            wrapper.remove();
            updateWashIndexes();
            updateTotal();
        }

        function updateWashIndexes(){
            document.querySelectorAll('#wash-list .wash-item').forEach((item,i)=>{
                item.dataset.index = i;
                item.querySelectorAll('input[data-field]').forEach(input=>{
                    input.name = `washes[${i}][${input.dataset.field}]`;
                });
            });
        }

        function toggleBank() {
            const field = document.getElementById('bank-field');
            const method = document.getElementById('payment_method').value;
            field.style.display = method === 'transferencia' ? '' : 'none';
        }

        const nameInput = document.querySelector('input[name="customer_name"]');
        const phoneInput = document.querySelector('input[name="customer_phone"]');

        function restrictInput(el, keyRegex, pasteRegex, msg){
            el.addEventListener('keydown', e => {
                if(e.ctrlKey || e.metaKey || e.altKey || e.key.length !== 1) return;
                if(!keyRegex.test(e.key)){
                    e.preventDefault();
                    const list = document.getElementById('error-list');
                    list.innerHTML = `<li>${msg}</li>`;
                    window.dispatchEvent(new CustomEvent('open-modal', { detail: 'error-modal' }));
                }
            });
            el.addEventListener('paste', e => {
                const text = (e.clipboardData || window.clipboardData).getData('text');
                if(!pasteRegex.test(text)){
                    e.preventDefault();
                    const list = document.getElementById('error-list');
                    list.innerHTML = `<li>${msg}</li>`;
                    window.dispatchEvent(new CustomEvent('open-modal', { detail: 'error-modal' }));
                }
            });
        }

        restrictInput(nameInput, /^[A-Za-zÁÉÍÓÚáéíóúñÑ ]$/, /^[A-Za-zÁÉÍÓÚáéíóúñÑ ]+$/, 'El nombre solo puede contener letras');
        restrictInput(phoneInput, /^[0-9+() -]$/, /^[0-9+() -]+$/, 'El teléfono solo puede contener números');

        function convertSelectToSearchable(select){
            select.classList.add('hidden');
            const wrapper = document.createElement('div');
            wrapper.className = 'relative';
            const input = document.createElement('input');
            input.type = 'text';
            input.className = 'form-input w-full mt-1';
            const placeholderText = select.dataset.placeholder || '-- Seleccionar --';
            input.placeholder = placeholderText;
            select._searchInput = input;
            wrapper.appendChild(input);
            const list = document.createElement('ul');
            list.className = 'absolute z-10 bg-white border border-gray-300 w-full mt-1 max-h-40 overflow-auto hidden';
            wrapper.appendChild(list);
            select.parentNode.insertBefore(wrapper, select);

            function syncFromSelect(){
                const selectedOption = select.options[select.selectedIndex];
                if (selectedOption && selectedOption.value) {
                    input.value = selectedOption.text;
                } else {
                    input.value = '';
                }
                input.placeholder = placeholderText;
            }
            select._syncSearchInput = syncFromSelect;
            syncFromSelect();

            function show(filter=''){
                list.innerHTML='';
                const f=filter.toLowerCase();
                Array.from(select.options).forEach(o=>{
                    if(!o.value) return;
                    if(o.text.toLowerCase().includes(f)){
                        const li=document.createElement('li');
                        li.textContent=o.text;
                        li.dataset.val=o.value;
                        li.className='px-2 py-1 cursor-pointer hover:bg-gray-200';
                        list.appendChild(li);
                    }
                });
                list.classList.toggle('hidden', list.children.length===0);
            }
            input.addEventListener('focus', ()=>{ if(!select.value){ input.value=''; } show(); });
            input.addEventListener('input', ()=>show(input.value));
            list.addEventListener('mousedown', e=>{const li=e.target.closest('li'); if(!li) return; e.preventDefault(); input.value=li.textContent; select.value=li.dataset.val; select.dispatchEvent(new Event('change')); list.classList.add('hidden');});
            input.addEventListener('blur', ()=>setTimeout(()=>list.classList.add('hidden'),200));
            select.addEventListener('change', syncFromSelect);
        }

        document.querySelectorAll('select[data-searchable]').forEach(convertSelectToSearchable);

        updateTotal();
        toggleBank();

        function ticketForm() {
            return {
                errors: [],
                async submitForm(e) {
                    const form = this.$refs.form;
                    const submitter = e?.submitter;
                    this.errors = [];

                    const shouldPrint = submitter?.value === 'pay';
                    const openPrint = window.openTicketPrintTab ?? ((url) => window.open(url, '_blank'));

                    if (submitter?.value === 'pending') {
                        const paid = form.querySelector('[name=paid_amount]').value;
                        if (paid && parseFloat(paid) > 0) {
                            this.errors.push('Si desea pagar el ticket use el botón "Pagar".');
                            this.showErrors();
                            return;
                        }
                    }

                    const hasWash = form.querySelectorAll('.wash-item').length > 0;
                    const hasProduct = Array.from(form.querySelectorAll('#product-list select[name="product_ids[]"]')).some(s => s.value);
                    const hasDrink = Array.from(form.querySelectorAll('#drink-list select[name="drink_ids[]"]')).some(s => s.value);
                    const hasCharge = Array.from(form.querySelectorAll('#charge-list input[name="charge_descriptions[]"]')).some((input,idx) => {
                        const amount = parseFloat(form.querySelectorAll('#charge-list input[name="charge_amounts[]"]')[idx].value || 0);
                        return input.value.trim() !== '' && amount > 0;
                    });
                    if (!hasWash && !hasProduct && !hasDrink && !hasCharge) {
                        this.errors.push('Debe agregar al menos un servicio, producto, trago o cargo adicional');
                        this.showErrors();
                        return;
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
                        let data = null;
                        try {
                            data = await res.clone().json();
                        } catch (error) {
                            data = null;
                        }

                        if (res.ok) {
                            const redirectUrl = data?.redirect ?? '{{ route('tickets.index') }}';
                            if (shouldPrint && data?.print_url) {
                                sessionStorage.setItem('skip_print_ticket', '1');
                                openPrint(data.print_url);
                            }
                            window.location = redirectUrl;
                            return;
                        }
                        if (res.status === 422 && data?.errors) {
                            this.errors = Object.values(data.errors).flat();
                        } else {
                            this.errors = [data?.message || 'Error inesperado'];
                        }
                    } catch (e) {
                        this.errors = ['Error de red'];
                    }
                    this.showErrors();
                },
                closeError() {
                    window.dispatchEvent(new CustomEvent('close-modal', { detail: 'error-modal' }));
                },
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
