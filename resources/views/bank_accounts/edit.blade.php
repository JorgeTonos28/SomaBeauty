<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Cuenta Bancaria') }}
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

            <form method="POST" action="{{ route('bank-accounts.update', $bankAccount) }}">
                @csrf
                @method('PUT')
                <div class="mb-4">
                    <label class="block font-medium text-sm text-gray-700">Banco</label>
                    <input type="text" name="bank" value="{{ old('bank', $bankAccount->bank) }}" required class="form-input w-full rounded border-gray-300 mt-1">
                </div>
                <div class="mb-4">
                    <label class="block font-medium text-sm text-gray-700">Cuenta</label>
                    <input type="text" name="account" value="{{ old('account', $bankAccount->account) }}" required class="form-input w-full rounded border-gray-300 mt-1">
                </div>
                <div class="mb-4">
                    <label class="block font-medium text-sm text-gray-700">Tipo</label>
                    <input type="text" name="type" value="{{ old('type', $bankAccount->type) }}" required class="form-input w-full rounded border-gray-300 mt-1">
                </div>
                <div class="mb-4">
                    <label class="block font-medium text-sm text-gray-700">Titular</label>
                    <input type="text" name="holder" value="{{ old('holder', $bankAccount->holder) }}" required class="form-input w-full rounded border-gray-300 mt-1">
                </div>
                <div class="mb-4">
                    <label class="block font-medium text-sm text-gray-700">Cédula Titular</label>
                    <input type="text" name="holder_cedula" value="{{ old('holder_cedula', $bankAccount->holder_cedula) }}" required class="form-input w-full rounded border-gray-300 mt-1">
                </div>
                <div class="flex justify-end">
                    <a href="{{ route('bank-accounts.index') }}" class="mr-3 btn-secondary">Cancelar</a>
                    <button type="submit" class="btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
