<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Trago') }}
        </h2>
    </x-slot>

    <div class="py-6 max-w-4xl mx-auto sm:px-6 lg:px-8">
        <form action="{{ route('drinks.update', $drink) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="block font-medium text-sm text-gray-700">Nombre</label>
                <input type="text" name="name" value="{{ $drink->name }}" required class="form-input w-full">
            </div>
            <div>
                <label for="ingredients" class="block font-medium text-sm text-gray-700">Ingredientes</label>
                <textarea name="ingredients" class="form-input w-full">{{ $drink->ingredients }}</textarea>
            </div>

            <div>
                <label for="price" class="block font-medium text-sm text-gray-700">Precio (RD$)</label>
                <input type="number" step="0.01" name="price" value="{{ $drink->price }}" required class="form-input w-full">
            </div>

            <div>
                <label class="inline-flex items-center">
                    <input type="checkbox" name="active" value="1" class="mr-2" {{ $drink->active ? 'checked' : '' }}>
                    <span>Activo</span>
                </label>
            </div>

            <div class="flex items-center gap-4">
                <button class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    Actualizar
                </button>
                <a href="{{ route('drinks.index') }}" class="text-gray-600 hover:underline">Cancelar</a>
            </div>
        </form>
    </div>
</x-app-layout>
