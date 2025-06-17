<div class="overflow-y-auto max-h-80">
    <table class="min-w-full table-auto border">
        <thead class="bg-gray-200">
            <tr>
                <th class="px-4 py-2">Fecha</th>
                <th class="px-4 py-2">Cliente</th>
                <th class="px-4 py-2">Detalle</th>
                <th class="px-4 py-2">Ganancia</th>
                <th class="px-4 py-2">Pago</th>
            </tr>
        </thead>
        <tbody>
            @foreach($events as $e)
                <tr class="border-b">
                    <td class="px-4 py-2">{{ \Carbon\Carbon::parse($e['date'])->format('d/m/Y') }}</td>
                    <td class="px-4 py-2">{{ $e['customer'] ?? '' }}</td>
                    <td class="px-4 py-2">{{ $e['description'] }}</td>
                    <td class="px-4 py-2">
                        @if(!is_null($e['gain']))
                            RD$ {{ number_format($e['gain'], 2) }}
                        @endif
                    </td>
                    <td class="px-4 py-2">
                        @if(!is_null($e['payment']))
                            RD$ {{ number_format($e['payment'], 2) }}
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
