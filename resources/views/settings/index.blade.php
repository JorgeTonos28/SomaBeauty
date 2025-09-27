<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Ajustes Generales') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-6 space-y-6">
                @if (session('success'))
                    <div class="rounded-md bg-green-50 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.5 12.75l6 6 9-13.5" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-green-700">{{ session('success') }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('settings.update') }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Acceso a la plataforma</h3>
                        <p class="mt-1 text-sm text-gray-500">Define si el sistema debe bloquear el acceso cuando se detecta un dispositivo móvil.</p>

                        <div class="mt-4 flex items-center justify-between rounded-lg border border-gray-200 px-4 py-3">
                            <div>
                                <p class="text-sm font-semibold text-gray-800">Bloquear acceso desde móviles</p>
                                <p class="text-xs text-gray-500">Cuando está activo, los teléfonos y tabletas verán una advertencia al intentar ingresar.</p>
                            </div>
                            <div class="flex items-center" x-data="{ enabled: {{ old('block_mobile_devices', $blockMobile) ? 'true' : 'false' }} }">
                                <input type="hidden" name="block_mobile_devices" value="0">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="block_mobile_devices" value="1" class="sr-only peer" x-model="enabled" @checked(old('block_mobile_devices', $blockMobile))>
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-300 rounded-full peer peer-checked:bg-blue-600 transition-all relative after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-5 peer-checked:after:border-white"></div>
                                    <span class="ml-2 text-xs text-gray-600" x-text="enabled ? 'Activo' : 'Inactivo'">
                                        {{ old('block_mobile_devices', $blockMobile) ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </label>
                            </div>
                        </div>
                        @error('block_mobile_devices')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Inventario</h3>
                        <p class="mt-1 text-sm text-gray-500">Establece el mínimo de unidades que se considerarán como stock bajo cuando un producto no tenga su propio límite.</p>

                        <div class="mt-4">
                            <label for="default_minimum_stock" class="block text-sm font-medium text-gray-700">Stock mínimo predeterminado</label>
                            <input type="number" name="default_minimum_stock" id="default_minimum_stock" min="0" class="mt-1 form-input w-full max-w-xs" value="{{ old('default_minimum_stock', $defaultMinimumStock) }}">
                            <p class="mt-2 text-xs text-gray-500">Aplicaremos este valor a todos los productos sin un aviso de escasez individual configurado.</p>
                            @error('default_minimum_stock')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <x-primary-button>Guardar cambios</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
