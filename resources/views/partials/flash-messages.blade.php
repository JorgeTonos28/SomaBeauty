@if (session('success'))
    <x-modal name="flash-success" :show="true">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Éxito</h2>
            <p class="text-green-600">{{ session('success') }}</p>
            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'flash-success' }))">Cerrar</x-secondary-button>
            </div>
        </div>
    </x-modal>
@endif

@if (session('error'))
    <x-modal name="flash-error" :show="true">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Error</h2>
            <p class="text-red-600">{{ session('error') }}</p>
            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'flash-error' }))">Cerrar</x-secondary-button>
            </div>
        </div>
    </x-modal>
@endif
