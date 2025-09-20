<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Servicio') }}
        </h2>
    </x-slot>

    <div class="py-6 max-w-4xl mx-auto sm:px-6 lg:px-8">
        <form action="{{ route('services.update', $service) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="block font-medium text-sm text-gray-700">Nombre</label>
                <input type="text" name="name" value="{{ $service->name }}" required class="form-input w-full">
            </div>

            <div>
                <label for="description" class="block font-medium text-sm text-gray-700">Descripción</label>
                <textarea name="description" class="form-input w-full">{{ $service->description }}</textarea>
            </div>

            <div>
                <label class="block font-medium text-sm text-gray-700 mb-2">Tipos de precio</label>
                <div id="price-options" class="space-y-3">
                    @forelse ($prices as $index => $price)
                        <div class="price-row flex flex-wrap items-end gap-2">
                            <input type="hidden" name="price_options[{{ $index }}][id]" value="{{ $price->id }}">
                            <div class="flex-1 min-w-[200px]">
                                <label class="block text-xs text-gray-600 uppercase tracking-wide">Nombre</label>
                                <input type="text" name="price_options[{{ $index }}][label]" value="{{ $price->label }}" class="form-input w-full" required>
                            </div>
                            <div class="w-40">
                                <label class="block text-xs text-gray-600 uppercase tracking-wide">Precio</label>
                                <input type="number" name="price_options[{{ $index }}][price]" step="0.01" value="{{ $price->price }}" class="form-input w-full" required>
                            </div>
                            <button type="button" class="remove-price text-sm text-red-600 hover:underline {{ $loop->count === 1 ? 'hidden' : '' }}">Eliminar</button>
                        </div>
                    @empty
                        <div class="price-row flex flex-wrap items-end gap-2">
                            <input type="hidden" name="price_options[0][id]" value="">
                            <div class="flex-1 min-w-[200px]">
                                <label class="block text-xs text-gray-600 uppercase tracking-wide">Nombre</label>
                                <input type="text" name="price_options[0][label]" class="form-input w-full" required>
                            </div>
                            <div class="w-40">
                                <label class="block text-xs text-gray-600 uppercase tracking-wide">Precio</label>
                                <input type="number" name="price_options[0][price]" step="0.01" class="form-input w-full" required>
                            </div>
                            <button type="button" class="remove-price text-sm text-red-600 hover:underline hidden">Eliminar</button>
                        </div>
                    @endforelse
                </div>
                <button type="button" id="add-price" class="mt-3 text-sm text-blue-600 hover:underline">+ Agregar tipo de precio</button>
            </div>

            <div class="flex items-center">
                <label class="mr-2 text-sm">Activo</label>
                <input type="checkbox" name="active" value="1" @checked($service->active)>
            </div>

            <div class="flex items-center gap-4">
                <button class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    Actualizar
                </button>
                <a href="{{ route('services.index') }}" class="text-gray-600 hover:underline">Cancelar</a>
            </div>
        </form>
    </div>
    <template id="price-row-template">
        <div class="price-row flex flex-wrap items-end gap-2">
            <input type="hidden">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs text-gray-600 uppercase tracking-wide">Nombre</label>
                <input type="text" class="form-input w-full" required>
            </div>
            <div class="w-40">
                <label class="block text-xs text-gray-600 uppercase tracking-wide">Precio</label>
                <input type="number" step="0.01" class="form-input w-full" required>
            </div>
            <button type="button" class="remove-price text-sm text-red-600 hover:underline">Eliminar</button>
        </div>
</template>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const container = document.getElementById('price-options');
            const addBtn = document.getElementById('add-price');
            const template = document.getElementById('price-row-template');

            const refreshIndexes = () => {
                const rows = container.querySelectorAll('.price-row');
                rows.forEach((row, index) => {
                    const idInput = row.querySelector('input[type="hidden"]');
                    const labelInput = row.querySelector('input[type="text"]');
                    const priceInput = row.querySelector('input[type="number"]');

                    idInput.name = `price_options[${index}][id]`;
                    labelInput.name = `price_options[${index}][label]`;
                    priceInput.name = `price_options[${index}][price]`;

                    row.querySelector('.remove-price').classList.toggle('hidden', rows.length === 1);
                });
            };

            addBtn.addEventListener('click', () => {
                const fragment = template.content.cloneNode(true);
                const newRow = fragment.querySelector('.price-row');
                newRow.querySelector('input[type="hidden"]').value = '';
                container.appendChild(fragment);
                refreshIndexes();
            });

            container.addEventListener('click', (event) => {
                if (event.target.classList.contains('remove-price')) {
                    const rows = container.querySelectorAll('.price-row');
                    if (rows.length > 1) {
                        event.target.closest('.price-row').remove();
                        refreshIndexes();
                    }
                }
            });

            refreshIndexes();
        });
    </script>
</x-app-layout>
