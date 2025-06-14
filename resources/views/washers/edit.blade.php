<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Lavador') }}
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 bg-white p-6 shadow sm:rounded-lg">
            @if ($errors->any())
                <div class="mb-4 text-sm text-red-600">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('washers.update', $washer) }}">
                @csrf @method('PUT')

                <div class="mb-4">
                    <label for="name" class="block font-medium text-sm text-gray-700">Nombre</label>
                    <input type="text" name="name" id="name" value="{{ $washer->name }}" required class="form-input w-full rounded border-gray-300 shadow-sm mt-1">
                </div>

                <div class="flex justify-end">
                    <a href="{{ route('washers.index') }}" class="mr-3 px-4 py-2 bg-gray-300 text-gray-700 rounded">Cancelar</a>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
