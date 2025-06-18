<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Apariencia') }}
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 bg-white p-6 shadow sm:rounded-lg">
            <form method="POST" action="{{ route('appearance.store') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf
                <div>
                    <label class="block font-medium text-sm text-gray-700">Logo</label>
                    <input type="file" name="logo" class="form-input mt-1">
                    <p class="text-xs text-gray-500 mt-1">Tamaño recomendado: 200x50 px.</p>
                </div>
                <div>
                    <label class="block font-medium text-sm text-gray-700">Favicon</label>
                    <input type="file" name="favicon" class="form-input mt-1">
                    <p class="text-xs text-gray-500 mt-1">Tamaño recomendado: 32x32 px.</p>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
