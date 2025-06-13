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
                <label class="block font-medium text-sm text-gray-700 mb-1">Precios por tipo de vehículo</label>
                @foreach ($vehicleTypes as $type)
                    <div class="flex items-center gap-2 mb-1">
                        <span class="w-32">{{ $type->name }}</span>
                        <input type="number" name="prices[{{ $type->id }}]" step="0.01" value="{{ $prices[$type->id] ?? '' }}" required class="form-input w-full">
                    </div>
                @endforeach
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
</x-app-layout>
