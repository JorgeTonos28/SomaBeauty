<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tragos Disponibles') }}
        </h2>
    </x-slot>

    <div x-data="filterTable('{{ route('drinks.index') }}', {selected: null})" x-on:click.away="selected = null" class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">


        @if (auth()->user()->role === 'admin')
            <div class="mb-4 flex items-center gap-4">
                <a href="{{ route('drinks.create') }}" class="btn-primary">
                    Nuevo Trago
                </a>
                <button x-show="selected" x-on:click="$dispatch('open-modal', 'edit-' + selected)" class="text-yellow-600" title="Editar">
                    <i class="fa-solid fa-pen fa-lg"></i>
                </button>
                <button x-show="selected" x-on:click="$dispatch('open-modal', 'delete-' + selected)" class="text-red-600" title="Eliminar">
                    <i class="fa-solid fa-trash fa-lg"></i>
                </button>
            </div>
        @endif

        <form method="GET" x-ref="form" class="mb-4">
            <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Buscar trago" class="form-input" @input.debounce.500ms="fetchTable()">
        </form>

        <div x-html="tableHtml"></div>
        @foreach ($drinks as $drink)
            <x-modal name="edit-{{ $drink->id }}" focusable>
                <form method="POST" action="{{ route('drinks.update', $drink) }}" class="p-6 space-y-6">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block font-medium text-sm text-gray-700">Nombre</label>
                        <input type="text" name="name" value="{{ $drink->name }}" required class="form-input w-full">
                    </div>
                    <div>
                        <label class="block font-medium text-sm text-gray-700">Ingredientes</label>
                        <textarea name="ingredients" class="form-input w-full">{{ $drink->ingredients }}</textarea>
                    </div>
                    <div>
                        <label class="block font-medium text-sm text-gray-700">Precio (RD$)</label>
                        <input type="number" step="0.01" name="price" value="{{ $drink->price }}" required class="form-input w-full">
                    </div>
                    <div>
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="active" value="1" class="mr-2" {{ $drink->active ? 'checked' : '' }}>
                            <span>Activo</span>
                        </label>
                    </div>
                    <div class="mt-6 flex justify-end">
                        <x-secondary-button x-on:click="$dispatch('close')">Cancelar</x-secondary-button>
                        <x-primary-button class="ms-3">Actualizar</x-primary-button>
                    </div>
                </form>
            </x-modal>

            <x-modal name="delete-{{ $drink->id }}" focusable>
                <form method="POST" action="{{ route('drinks.destroy', $drink) }}" class="p-6">
                    @csrf
                    @method('DELETE')
                    <h2 class="text-lg font-medium text-gray-900">¿Eliminar este producto?</h2>
                    <div class="mt-6 flex justify-end">
                        <x-secondary-button x-on:click="$dispatch('close')">Cancelar</x-secondary-button>
                        <x-danger-button class="ms-3">Eliminar</x-danger-button>
                    </div>
                </form>
            </x-modal>
        @endforeach
    </div>
</x-app-layout>
