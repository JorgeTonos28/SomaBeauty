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
                    <label class="block font-medium text-sm text-gray-700">Nombre del negocio</label>
                    <input type="text" name="business_name" value="{{ old('business_name', optional($settings)->business_name) }}" class="form-input mt-1 w-full" placeholder="Ej. Salon Soma">
                    <p class="text-xs text-gray-500 mt-1">Este nombre se mostrará en el pie de página y en la factura.</p>
                </div>
                <div>
                    <label class="block font-medium text-sm text-gray-700">Dirección</label>
                    <textarea name="business_address" rows="3" class="form-textarea mt-1 w-full" placeholder="Ej. Calle Principal #123, Santo Domingo">{{ old('business_address', optional($settings)->business_address) }}</textarea>
                    <p class="text-xs text-gray-500 mt-1">Se imprimirá debajo del nombre del negocio en la factura.</p>
                </div>
                <div>
                    <label class="block font-medium text-sm text-gray-700">Número de identificación fiscal</label>
                    <input type="text" name="tax_id" value="{{ old('tax_id', optional($settings)->tax_id) }}" class="form-input mt-1 w-full" placeholder="Ej. RNC 1-11-11111-1">
                    <p class="text-xs text-gray-500 mt-1">Aparecerá en la cabecera de la factura.</p>
                </div>
                <div>
                    <label class="block font-medium text-sm text-gray-700">Logo</label>
                    <input type="file" name="logo" class="form-input mt-1">
                    <p class="text-xs text-gray-500 mt-1">Tamaño recomendado: 200x50 px.</p>
                </div>
                <div>
                    <label class="block font-medium text-sm text-gray-700">Logo para el login</label>
                    <input type="file" name="login_logo" class="form-input mt-1">
                    <p class="text-xs text-gray-500 mt-1">Tamaño recomendado: 320x160 px.</p>
                </div>
                <div>
                    <label class="block font-medium text-sm text-gray-700">Favicon</label>
                    <input type="file" name="favicon" class="form-input mt-1">
                    <p class="text-xs text-gray-500 mt-1">Tamaño recomendado: 32x32 px.</p>
                </div>
                <div>
                    <label class="block font-medium text-sm text-gray-700">QR en factura</label>
                    <input type="file" name="qr_image" class="form-input mt-1">
                    <p class="text-xs text-gray-500 mt-1">Tamaño recomendado: 300x300 px.</p>
                </div>
                <div>
                    <label class="block font-medium text-sm text-gray-700">Descripción QR</label>
                    <input type="text" name="qr_description" value="{{ old('qr_description', optional($settings)->qr_description) }}" class="form-input mt-1 w-full" placeholder="Ej. Síguenos en Instagram">
                    <p class="text-xs text-gray-500 mt-1">Se mostrará debajo del código QR en la factura.</p>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
