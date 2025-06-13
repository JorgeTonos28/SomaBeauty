<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <a href="{{ route('tickets.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Nuevo Ticket</a>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-2">Reportes (próximamente)</h3>
                <div class="flex gap-4">
                    <button class="px-4 py-2 bg-gray-300 text-gray-700 rounded" disabled>Reporte Diario</button>
                    <button class="px-4 py-2 bg-gray-300 text-gray-700 rounded" disabled>Reporte Mensual</button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
