<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <table class="min-w-full table-auto border">
        <thead class="bg-gray-200">
            <tr>
                <th class="px-4 py-2 border">Nombre</th>
                <th class="px-4 py-2 border">Precio</th>
                <th class="px-4 py-2 border">Stock</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($products as $product)
                <tr class="border-t cursor-pointer" x-on:click="selected = {{ $product->id }}" :class="selected === {{ $product->id }} ? 'bg-blue-100' : ''">
                    <td class="px-4 py-2">{{ $product->name }}</td>
                    <td class="px-4 py-2">RD$ {{ number_format($product->price, 2) }}</td>
                    <td class="px-4 py-2">{{ $product->stock }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
