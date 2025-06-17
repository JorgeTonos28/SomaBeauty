<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Agregar Trago') }}
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 bg-white p-6 shadow sm:rounded-lg">
        <form action="{{ route('drinks.store') }}" method="POST" class="space-y-6">
            @csrf

            <div>
                <label for="name" class="block font-medium text-sm text-gray-700">Nombre</label>
                <input type="text" name="name" required class="form-input w-full">
            </div>


            <div>
                <label for="ingredients" class="block font-medium text-sm text-gray-700">Ingredientes</label>
                <textarea name="ingredients" class="form-input w-full"></textarea>
            </div>

            <div>
                <label for="price" class="block font-medium text-sm text-gray-700">Precio (RD$)</label>
                <input type="number" step="0.01" name="price" required class="form-input w-full">
            </div>

            <div>
                <label class="inline-flex items-center">
                    <input type="checkbox" name="active" value="1" class="mr-2" checked>
                    <span>Activo</span>
                </label>
            </div>

            <div class="flex items-center gap-4">
                <x-primary-button>Guardar</x-primary-button>
                <x-secondary-button type="button" onclick="window.location='{{ route('drinks.index') }}'">Cancelar</x-secondary-button>
            </div>
        </form>
        </div>
    </div>
</x-app-layout>
