<div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
    <table class="min-w-full table-auto border">
        <thead class="bg-gray-200">
            <tr>
                <th class="border px-4 py-2">Concepto</th>
                <th class="border px-4 py-2">Fecha</th>
                <th class="border px-4 py-2">Monto</th>
            </tr>
        </thead>
        <tbody>
            @foreach($movements as $m)
                <tr class="border-t">
                    <td class="px-4 py-2">{{ $m['description'] }}</td>
                    <td class="px-4 py-2">{{ $m['date'] }}</td>
                    <td class="px-4 py-2">RD$ {{ number_format($m['amount'],2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
