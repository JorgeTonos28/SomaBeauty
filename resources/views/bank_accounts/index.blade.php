<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Cuentas Bancarias') }}
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 font-medium text-sm text-green-600">
                    {{ session('success') }}
                </div>
            @endif

            <div class="flex justify-end mb-4">
                <a href="{{ route('bank-accounts.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Nueva Cuenta
                </a>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <table class="min-w-full table-auto border">
                    <thead class="bg-gray-200">
                        <tr>
                            <th class="px-4 py-2">Banco</th>
                            <th class="px-4 py-2">Cuenta</th>
                            <th class="px-4 py-2">Tipo</th>
                            <th class="px-4 py-2">Titular</th>
                            <th class="px-4 py-2">Cédula</th>
                            <th class="px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($accounts as $account)
                            <tr class="border-b">
                                <td class="px-4 py-2">{{ $account->bank }}</td>
                                <td class="px-4 py-2">{{ $account->account }}</td>
                                <td class="px-4 py-2">{{ $account->type }}</td>
                                <td class="px-4 py-2">{{ $account->holder }}</td>
                                <td class="px-4 py-2">{{ $account->holder_cedula }}</td>
                                <td class="px-4 py-2 text-right space-x-2">
                                    <a href="{{ route('bank-accounts.edit', $account) }}" class="text-blue-600 hover:underline">Editar</a>
                                    <form action="{{ route('bank-accounts.destroy', $account) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline" onclick="return confirm('¿Eliminar cuenta?')">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
