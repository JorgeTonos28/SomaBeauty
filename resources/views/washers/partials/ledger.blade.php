<div id="pending-meta" data-pending="{{ number_format($pending, 2, '.', '') }}" class="hidden"></div>
<div class="overflow-y-auto max-h-80">
    <table class="min-w-full table-auto border">
        <thead class="bg-gray-200">
            <tr>
                <th class="px-4 py-2"></th>
                <th class="px-4 py-2">Fecha</th>
                <th class="px-4 py-2">Ticket</th>
                <th class="px-4 py-2">Cliente</th>
                <th class="px-4 py-2">Detalle</th>
                <th class="px-4 py-2">Ganancia</th>
                <th class="px-4 py-2">Pago</th>
            </tr>
        </thead>
        <tbody>
            @foreach($events as $e)
                <tr class="border-b">
                    <td class="px-4 py-2 text-center">
                        @if(!is_null($e['gain']) && $e['gain'] > 0 && !($e['paid_to_washer'] ?? false))
                            <input type="checkbox" class="gain-check" data-amount="{{ $e['gain'] }}"
                                @if($e['wash_id'] ?? false) data-wash="{{ $e['wash_id'] }}" @endif
                                @if($e['movement_id'] ?? false) data-movement="{{ $e['movement_id'] }}" @endif>
                        @endif
                    </td>
                    <td class="px-4 py-2">{{ \Carbon\Carbon::parse($e['date'])->format('d/m/Y h:i A') }}</td>
                    <td class="px-4 py-2">{{ $e['ticket_id'] ?? '' }}</td>
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
