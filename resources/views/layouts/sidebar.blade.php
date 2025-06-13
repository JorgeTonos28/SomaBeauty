<aside class="w-48 bg-white border-r border-gray-200">
    <div class="p-4">
        <nav class="space-y-2">
            <a href="{{ route('dashboard') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('dashboard') ? 'bg-gray-200' : '' }}">
                Dashboard
            </a>
            <a href="{{ route('services.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('services.*') ? 'bg-gray-200' : '' }}">
                Servicios
            </a>
            <a href="{{ route('products.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('products.*') ? 'bg-gray-200' : '' }}">
                Productos
            </a>
            <a href="{{ route('washers.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('washers.*') ? 'bg-gray-200' : '' }}">
                Lavadores
            </a>
            <a href="{{ route('tickets.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('tickets.*') ? 'bg-gray-200' : '' }}">
                Tickets
            </a>
            <a href="{{ route('petty-cash.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('petty-cash.*') ? 'bg-gray-200' : '' }}">
                Caja Chica
            </a>
            <a href="{{ route('profile.edit') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('profile.*') ? 'bg-gray-200' : '' }}">
                Perfil
            </a>
        </nav>
    </div>
</aside>
