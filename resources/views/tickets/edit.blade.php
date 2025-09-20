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
                <summary class="cursor-pointer font-medium text-gray-700">Servicios</summary>
                <div id="wash-list" class="space-y-4 mt-4">
                    @foreach($ticketWashes as $i => $wData)
                        @php
                            $w = $wData['wash'];
                            $summary = $w->vehicle->brand.' | '.$w->vehicle->model.' | '.$w->vehicle->color.' | '.$w->vehicle->year.' | '.$w->vehicleType->name;
                            $servicesText = $w->details->where('type','service')->map(fn($d)=>$d->service->name)->implode(', ');
                        @endphp
                        <details class="border rounded p-2 wash-item" data-total="{{ $wData['total'] }}" data-discount="{{ $wData['discount'] }}" ontoggle="if(this.open) editWash(this); else cancelWashForm();">
                            <summary class="cursor-pointer font-medium text-gray-700">{{ $summary }}<button type="button" class="ml-2 text-red-600" onclick="removeWash(this); event.stopPropagation();">Eliminar</button></summary>
                            @foreach($wData['service_ids'] as $sid)
                                <input type="hidden" name="washes[{{ $i }}][service_ids][]" value="{{ $sid }}">
                            @endforeach
                            <input type="hidden" name="washes[{{ $i }}][plate]" value="{{ $w->vehicle->plate }}">
                            <input type="hidden" name="washes[{{ $i }}][brand]" value="{{ $w->vehicle->brand }}">
                            <input type="hidden" name="washes[{{ $i }}][model]" value="{{ $w->vehicle->model }}">
                            <input type="hidden" name="washes[{{ $i }}][color]" value="{{ $w->vehicle->color }}">
                            <input type="hidden" name="washes[{{ $i }}][year]" value="{{ $w->vehicle->year }}">
                            <input type="hidden" name="washes[{{ $i }}][vehicle_type_id]" value="{{ $w->vehicle_type_id }}">
                            <input type="hidden" name="washes[{{ $i }}][washer_id]" value="{{ $w->washer_id }}">
                            <input type="hidden" name="washes[{{ $i }}][tip]" value="{{ $wData['tip'] }}">
                            <div class="mt-2 space-y-1 text-sm">
                                <p>Placa: {{ $w->vehicle->plate }}</p>
                                <p>Estilista: {{ optional($w->washer)->name ?? 'N/A' }}</p>
                                <p>Servicios: {{ $servicesText }}</p>
                                @if($w->tip > 0)
                                    <p>Propina: RD$ {{ number_format($w->tip,2) }}</p>
                                @endif
                            </div>
                        </details>
                    @endforeach
                </div>

                <div id="wash-form" class="space-y-4 mt-4 hidden">
                    <!-- Placa -->
                    <div class="relative">
                        <label class="block text-sm font-medium text-gray-700">Placa</label>
                        <input type="text" name="temp_plate" id="plate" autocomplete="off" pattern="[A-Za-z0-9]+" class="form-input w-full mt-1">
                        <ul id="plate-options" class="absolute z-10 bg-white border border-gray-300 w-full mt-1 max-h-40 overflow-auto hidden"></ul>
                    </div>

                    <!-- Marca -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Marca</label>
                        <input type="text" name="temp_brand" pattern="[A-Za-z0-9 ]+" class="form-input w-full mt-1">
                    </div>

                    <!-- Modelo -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Modelo</label>
                        <input type="text" name="temp_model" pattern="[A-Za-z0-9 ]+" class="form-input w-full mt-1">
                    </div>

                    <!-- Color -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Color</label>
                        <input type="text" name="temp_color" pattern="[A-Za-zÁÉÍÓÚáéíóúñÑ ]+" class="form-input w-full mt-1">
                    </div>

                    <!-- Año -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Año</label>
                        <input type="number" name="temp_year" min="1890" max="{{ date('Y') }}" class="form-input w-full mt-1">
                    </div>

                    <!-- Tipo de Vehículo -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tipo de Vehículo</label>
                        <select name="temp_vehicle_type_id" class="form-select w-full mt-1">
                            <option value="">-- Seleccionar --</option>
                            @foreach ($vehicleTypes as $type)
                                <option value="{{ $type->id }}" data-name="{{ $type->name }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
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

                    <!-- Lista Servicios -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Servicios Realizados</label>
                        <div id="service-list">
                        @foreach ($services as $service)
                            <div class="flex items-center space-x-2 mt-1">
                                <input type="checkbox" name="temp_service_ids[]" value="{{ $service->id }}">
                                <label data-service-id="{{ $service->id }}" data-name="{{ $service->name }}">{{ $service->name }}</label>
                            </div>
                        @endforeach
                        </div>
                    </div>
                    <div class="mt-2 space-x-2">
                        <button type="button" id="save-wash-btn" class="px-3 py-1 text-sm text-white bg-blue-600 rounded hover:bg-blue-700" onclick="saveWash()">Agregar lavado</button>
                        <button type="button" id="cancel-wash-btn" class="px-3 py-1 text-sm text-gray-700 bg-gray-200 rounded hover:bg-gray-300 hidden" onclick="cancelWashForm()">Cancelar</button>
                    </div>
                </div>

                <div class="mt-2">
                    <button type="button" id="show-wash-form-btn" class="text-sm text-blue-600" onclick="showWashForm()">Agregar lavado</button>
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
            document.getElementById('save-wash-btn').textContent = 'Agregar lavado';
            form.classList.remove('hidden');
            form.dataset.editIndex = '';
        }

        function cancelWashForm() {
            const form = document.getElementById('wash-form');
            document.getElementById('wash-list').after(form);
            form.classList.add('hidden');
            document.getElementById('show-wash-form-btn').classList.remove('hidden');
            document.getElementById('cancel-wash-btn').classList.add('hidden');
            document.getElementById('save-wash-btn').textContent = 'Agregar lavado';
            delete form.dataset.editIndex;
            form.querySelectorAll('input[type=text], input[type=number]').forEach(el=>el.value='');
            form.querySelectorAll('select').forEach(sel=>{sel.value=''; if(sel._searchInput) sel._searchInput.value='';});
            form.querySelectorAll('input[type=checkbox]').forEach(cb=>cb.checked=false);
            document.querySelectorAll('.wash-item').forEach(w=>w.removeAttribute('open'));
        }

        function clearWasher(){
            const sel = document.querySelector('#wash-form select[name="temp_washer_id"]');
            sel.value='';
            if(sel._searchInput) sel._searchInput.value='';
        }

        function saveWash() {
            const form = document.getElementById('wash-form');
            const editing = form.dataset.editIndex !== undefined && form.dataset.editIndex !== '';
            const index = editing ? parseInt(form.dataset.editIndex) : document.querySelectorAll('.wash-item').length;
            const plate = form.querySelector('input[name="temp_plate"]').value.trim();
            const brand = form.querySelector('input[name="temp_brand"]').value.trim();
            const model = form.querySelector('input[name="temp_model"]').value.trim();
            const color = form.querySelector('input[name="temp_color"]').value.trim();
            const year = form.querySelector('input[name="temp_year"]').value.trim();
            const vtSelect = form.querySelector('select[name="temp_vehicle_type_id"]');
            const vehicleTypeId = vtSelect.value;
            const vehicleTypeName = vtSelect.options[vtSelect.selectedIndex]?.dataset.name || '';
            const washerSelect = form.querySelector('select[name="temp_washer_id"]');
            const washerId = washerSelect.value;
            const washerName = washerId ? washerSelect.options[washerSelect.selectedIndex].text : '';
            const services = Array.from(form.querySelectorAll('input[name="temp_service_ids[]"]:checked')).map(cb => ({id: cb.value, name: cb.nextElementSibling.dataset.name}));
            if (services.length === 0) { return; }

            let washTotal = 0, washDiscount = 0;
            services.forEach(s => {
                let price = servicePrices[s.id] && servicePrices[s.id][vehicleTypeId] ? parseFloat(servicePrices[s.id][vehicleTypeId]) : 0;
                const disc = serviceDiscounts[s.id];
                if (disc) {
                    const d = disc.type === 'fixed' ? parseFloat(disc.amount) : price * parseFloat(disc.amount) / 100;
                    washDiscount += d;
                    price = Math.max(0, price - d);
                }
                washTotal += price;
            });
            const tip = parseFloat(form.querySelector('input[name="temp_tip"]').value) || 0;
            washTotal += tip;

            const summary = `${brand} | ${model} | ${color} | ${year} | ${vehicleTypeName}`;
            let wrapper;
            if (editing) {
                wrapper = document.querySelectorAll('.wash-item')[index];
                wrapper.innerHTML = '';
            } else {
                wrapper = document.createElement('details');
                wrapper.className = 'border rounded p-2 wash-item';
                wrapper.addEventListener('toggle', function(){ if(this.open) editWash(this); else cancelWashForm(); });
                document.getElementById('wash-list').appendChild(wrapper);
            }
            wrapper.dataset.total = washTotal;
            wrapper.dataset.discount = washDiscount;
            const servicesText = services.map(s=>s.name).join(', ');
            wrapper.innerHTML = `<summary class=\"cursor-pointer font-medium text-gray-700\">${summary}<button type=\"button\" class=\"ml-2 text-red-600\" onclick=\"removeWash(this); event.stopPropagation();\">Eliminar</button></summary>` +
                services.map(s=>`<input type=\"hidden\" name=\"washes[${index}][service_ids][]\" value=\"${s.id}\">`).join('') +
                `<input type=\"hidden\" name=\"washes[${index}][plate]\" value=\"${plate}\">` +
                `<input type=\"hidden\" name=\"washes[${index}][brand]\" value=\"${brand}\">` +
                `<input type=\"hidden\" name=\"washes[${index}][model]\" value=\"${model}\">` +
                `<input type=\"hidden\" name=\"washes[${index}][color]\" value=\"${color}\">` +
                `<input type=\"hidden\" name=\"washes[${index}][year]\" value=\"${year}\">` +
                `<input type=\"hidden\" name=\"washes[${index}][vehicle_type_id]\" value=\"${vehicleTypeId}\">` +
                `<input type=\"hidden\" name=\"washes[${index}][washer_id]\" value=\"${washerId}\">` +
                `<input type=\"hidden\" name=\"washes[${index}][tip]\" value=\"${tip.toFixed(2)}\">` +
                `<div class=\"mt-2 space-y-1 text-sm\"><p>Placa: ${plate}</p><p>Estilista: ${washerName || 'N/A'}</p><p>Servicios: ${servicesText}</p><p>Propina: RD$ ${tip.toFixed(2)}</p></div>`;

            updateWashIndexes();

            form.querySelectorAll('input[type=text], input[type=number]').forEach(el=>el.value='');
            form.querySelectorAll('select').forEach(sel=>{sel.value=''; if(sel._searchInput) sel._searchInput.value='';});
            form.querySelectorAll('input[type=checkbox]').forEach(cb=>cb.checked=false);
            delete form.dataset.editIndex;
            document.getElementById('wash-list').after(form);
            form.classList.add('hidden');
            document.getElementById('show-wash-form-btn').classList.remove('hidden');
            document.getElementById('cancel-wash-btn').classList.add('hidden');
            document.getElementById('save-wash-btn').textContent = 'Agregar lavado';
            wrapper.removeAttribute('open');
            updateTotal();
        }

        function editWash(wrapper) {
            const index = Array.from(document.querySelectorAll('.wash-item')).indexOf(wrapper);
            const form = document.getElementById('wash-form');
            form.dataset.editIndex = index;
            document.getElementById('show-wash-form-btn').classList.add('hidden');
            document.getElementById('cancel-wash-btn').classList.add('hidden');
            document.getElementById('save-wash-btn').textContent = 'Guardar cambios';
            form.classList.remove('hidden');
            wrapper.appendChild(form);
            form.querySelector('input[name="temp_plate"]').value = wrapper.querySelector(`input[name="washes[${index}][plate]"]`).value;
            form.querySelector('input[name="temp_brand"]').value = wrapper.querySelector(`input[name="washes[${index}][brand]"]`).value;
            form.querySelector('input[name="temp_model"]').value = wrapper.querySelector(`input[name="washes[${index}][model]"]`).value;
            form.querySelector('input[name="temp_color"]').value = wrapper.querySelector(`input[name="washes[${index}][color]"]`).value;
            form.querySelector('input[name="temp_year"]').value = wrapper.querySelector(`input[name="washes[${index}][year]"]`).value;
            form.querySelector('select[name="temp_vehicle_type_id"]').value = wrapper.querySelector(`input[name="washes[${index}][vehicle_type_id]"]`).value;
            const washerSelect = form.querySelector('select[name="temp_washer_id"]');
            washerSelect.value = wrapper.querySelector(`input[name="washes[${index}][washer_id]"]`).value;
            if(washerSelect._searchInput){ washerSelect._searchInput.value = washerSelect.options[washerSelect.selectedIndex]?.text || ''; }
            form.querySelectorAll('input[name="temp_service_ids[]"]').forEach(cb=>{
                const val = cb.value;
                cb.checked = wrapper.querySelector(`input[name="washes[${index}][service_ids][]"][value="${val}"]`) !== null;
            });
            form.querySelector('input[name="temp_tip"]').value = wrapper.querySelector(`input[name="washes[${index}][tip]"]`).value;

            document.querySelectorAll('.wash-item').forEach(w=>{ if(w!==wrapper) w.removeAttribute('open'); });
        }

        function removeWash(btn) {
            const wrapper = btn.closest('.wash-item');
            wrapper.remove();
            updateWashIndexes();
            updateTotal();
        }

        function updateWashIndexes(){
            document.querySelectorAll('#wash-list .wash-item').forEach((item,i)=>{
                item.querySelectorAll('input[name^="washes["]').forEach(input=>{
                    const field = input.name.replace(/washes\[\d+\]\[(.*)\]/,'$1');
                    if(field.startsWith('service_ids')){
                        input.name = `washes[${i}][service_ids][]`;
                    }else{
                        input.name = `washes[${i}][${field}]`;
                    }
                });
            });
        }

        function toggleBank() {
            const field = document.getElementById('bank-field');
            const method = document.getElementById('payment_method').value;
            field.style.display = method === 'transferencia' ? '' : 'none';
        }

        const plateInput = document.getElementById('plate');
        const nameInput = document.querySelector('input[name="customer_name"]');
        const phoneInput = document.querySelector('input[name="customer_phone"]');
        const colorInput = document.querySelector('input[name="temp_color"]');
        const yearInput = document.querySelector('input[name="temp_year"]');
        const plateList = document.getElementById('plate-options');
        let plateData = [];
        let selectedIndex = -1;

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
        restrictInput(plateInput, /^[A-Za-z0-9]$/, /^[A-Za-z0-9]+$/, 'La placa solo puede contener letras y números');
        restrictInput(colorInput, /^[A-Za-zÁÉÍÓÚáéíóúñÑ ]$/, /^[A-Za-zÁÉÍÓÚáéíóúñÑ ]+$/, 'El color solo puede contener letras');
        restrictInput(yearInput, /^\d$/, /^\d+$/, 'El año solo puede contener números');
        restrictInput(phoneInput, /^[0-9+() -]$/, /^[0-9+() -]+$/, 'El teléfono solo puede contener números');

        function convertSelectToSearchable(select){
            select.classList.add('hidden');
            const wrapper = document.createElement('div');
            wrapper.className = 'relative';
            const input = document.createElement('input');
            input.type = 'text';
            input.className = 'form-input w-full mt-1';
            input.value = select.options[select.selectedIndex]?.text || '';
            select._searchInput = input;
            wrapper.appendChild(input);
            const list = document.createElement('ul');
            list.className = 'absolute z-10 bg-white border border-gray-300 w-full mt-1 max-h-40 overflow-auto hidden';
            wrapper.appendChild(list);
            select.parentNode.insertBefore(wrapper, select);

            const options = Array.from(select.options);
            function show(filter=''){list.innerHTML=''; const f=filter.toLowerCase(); options.forEach(o=>{if(!o.value) return; if(o.text.toLowerCase().includes(f)){const li=document.createElement('li');li.textContent=o.text; li.dataset.val=o.value; li.className='px-2 py-1 cursor-pointer hover:bg-gray-200'; list.appendChild(li);}}); list.classList.toggle('hidden', list.children.length===0);}
            input.addEventListener('focus', ()=>show());
            input.addEventListener('input', ()=>show(input.value));
            list.addEventListener('mousedown', e=>{const li=e.target.closest('li'); if(!li) return; e.preventDefault(); input.value=li.textContent; select.value=li.dataset.val; select.dispatchEvent(new Event('change')); list.classList.add('hidden');});
            input.addEventListener('blur', ()=>setTimeout(()=>list.classList.add('hidden'),200));
        }

        const maxYear = {{ date('Y') }};
        const minYear = 1890;
        yearInput.addEventListener('input', () => {
            let val = yearInput.value.replace(/\D/g, '').slice(0, 4);
            yearInput.value = val;
            if (val.length === 4) {
                const num = parseInt(val, 10);
                if (num < minYear || num > maxYear) {
                    yearInput.value = '';
                    const list = document.getElementById('error-list');
                    list.innerHTML = `<li>El año debe estar entre ${minYear} y ${maxYear}</li>`;
                    window.dispatchEvent(new CustomEvent('open-modal', { detail: 'error-modal' }));
                }
            }
        });

        plateInput.addEventListener('input', async () => {
            const q = plateInput.value.trim();
            if (!q) { plateList.innerHTML = ''; plateList.classList.add('hidden'); return; }
            try {
                const res = await fetch(`{{ route('vehicles.search') }}?plate=${encodeURIComponent(q)}`, {headers:{'Accept':'application/json'}});
                if(res.ok){
                    plateData = (await res.json()).slice(0,10);
                    plateList.innerHTML = '';
                    selectedIndex = -1;
                    if(plateData.length === 0){
                        plateList.classList.add('hidden');
                        return;
                    }
                    plateList.classList.remove('hidden');
                    plateData.forEach(v => {
                        const li = document.createElement('li');
                        li.textContent = `${v.brand} | ${v.model} | ${v.color} | ${v.year ?? ''} | ${v.plate} | ${v.type}`;
                        li.dataset.plate = v.plate;
                        li.className = 'px-2 py-1 cursor-pointer hover:bg-gray-200';
                        plateList.appendChild(li);
                    });
                }
            } catch(e) {}
        });

        plateInput.addEventListener('keydown', e => {
            const items = plateList.querySelectorAll('li');
            if(plateList.classList.contains('hidden') || items.length === 0) return;
            if(e.key === 'ArrowDown'){
                e.preventDefault();
                selectedIndex = (selectedIndex + 1) % items.length;
                updateActive(items);
            } else if(e.key === 'ArrowUp'){
                e.preventDefault();
                selectedIndex = (selectedIndex - 1 + items.length) % items.length;
                updateActive(items);
            } else if(e.key === 'Enter' && selectedIndex >= 0){
                e.preventDefault();
                const li = items[selectedIndex];
                plateInput.value = li.dataset.plate;
                fillVehicleFields(li.dataset.plate);
                plateList.classList.add('hidden');
            }
        });

        function updateActive(items){
            items.forEach((li,i)=>{
                li.classList.toggle('bg-gray-200', i===selectedIndex);
            });
        }

        plateList.addEventListener('mousedown', e => {
            const li = e.target.closest('li[data-plate]');
            if(!li) return;
            e.preventDefault();
            plateInput.value = li.dataset.plate;
            fillVehicleFields(li.dataset.plate);
            plateList.classList.add('hidden');
        });

        plateInput.addEventListener('blur', () => {
            setTimeout(() => plateList.classList.add('hidden'), 200);
        });

        plateInput.addEventListener('change', () => {
            fillVehicleFields(plateInput.value);
        });

        function fillVehicleFields(plate){
            const found = plateData.find(v => v.plate === plate);
            if(found){
                document.querySelector('input[name="temp_brand"]').value = found.brand;
                document.querySelector('input[name="temp_model"]').value = found.model;
                document.querySelector('input[name="temp_color"]').value = found.color;
                document.querySelector('input[name="temp_year"]').value = found.year || '';
                document.querySelector('select[name="temp_vehicle_type_id"]').value = found.vehicle_type_id;
            }
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
