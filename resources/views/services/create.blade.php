<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Agregar Servicio') }}
        </h2>
    </x-slot>

    <div class="py-6 max-w-4xl mx-auto sm:px-6 lg:px-8">
        <form action="{{ route('services.store') }}" method="POST" class="space-y-6">
            @csrf

            <div>
                <label for="name" class="block font-medium text-sm text-gray-700">Nombre</label>
                <input type="text" name="name" required class="form-input w-full">
            </div>

           <div>
               <label for="description" class="block font-medium text-sm text-gray-700">Descripción</label>
               <textarea name="description" class="form-input w-full"></textarea>
           </div>

            <div>
                <label class="block font-medium text-sm text-gray-700 mb-1">Precios por tipo de vehículo</label>
                @foreach ($vehicleTypes as $type)
                    <div class="flex items-center gap-2 mb-1">
                        <span class="w-32">{{ $type->name }}</span>
                        <input type="number" name="prices[{{ $type->id }}]" step="0.01" required class="form-input w-full">
                    </div>
                @endforeach
            </div>

            <div class="flex items-center">
                <label class="mr-2 text-sm">Activo</label>
                <input type="checkbox" name="active" value="1" checked>
            </div>


            <div class="flex items-center gap-4">
                <x-primary-button>Guardar</x-primary-button>
                <x-secondary-button type="button" onclick="window.location='{{ route('services.index') }}'">Cancelar</x-secondary-button>
            </div>
        </form>
    </div>
</x-app-layout>
