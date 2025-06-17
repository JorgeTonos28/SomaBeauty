<aside class="w-48 bg-white border-r border-gray-200">
    <div class="p-4">
        <nav class="space-y-2">
            <a href="{{ route('dashboard') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('dashboard') ? 'bg-gray-200' : '' }}">
                Panel de Control
            </a>
            <a href="{{ route('petty-cash.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('petty-cash.*') ? 'bg-gray-200' : '' }}">
                Caja Chica
            </a>
            <a href="{{ route('washers.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('washers.*') ? 'bg-gray-200' : '' }}">
                Lavadores
            </a>
            <div x-data="{ open: {{ request()->routeIs('services.*','products.*','drinks.*') ? 'true' : 'false' }} }">
                <button type="button" @click="open=!open" class="w-full text-left px-3 py-2 font-semibold rounded hover:bg-gray-100 {{ request()->routeIs('services.*','products.*','drinks.*') ? 'bg-gray-200' : '' }}">
                    Servicios, Productos y Tragos
                </button>
                <div x-show="open" x-cloak class="pl-6 space-y-1">
                    <a href="{{ route('services.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('services.*') ? 'bg-gray-200' : '' }}">Servicios</a>
                    <a href="{{ route('products.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('products.*') ? 'bg-gray-200' : '' }}">Productos</a>
                    <a href="{{ route('drinks.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('drinks.*') ? 'bg-gray-200' : '' }}">Tragos</a>
                </div>
            </div>
            <div x-data="{ open: {{ request()->routeIs('tickets.*') ? 'true' : 'false' }} }">
                <button type="button" @click="open=!open" class="w-full text-left px-3 py-2 font-semibold rounded hover:bg-gray-100 {{ request()->routeIs('tickets.*') ? 'bg-gray-200' : '' }}">
                    Tickets
                </button>
                <div x-show="open" x-cloak class="pl-6 space-y-1">
                    <a href="{{ route('tickets.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('tickets.index') ? 'bg-gray-200' : '' }}">Activos</a>
                    <a href="{{ route('tickets.canceled') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('tickets.canceled') ? 'bg-gray-200' : '' }}">Cancelados</a>
                </div>
            </div>
            @if(auth()->user()->role === 'admin')
            <div x-data="{ open: {{ request()->routeIs('discounts.*','users.*','bank-accounts.*') ? 'true' : 'false' }} }">
                <button type="button" @click="open=!open" class="w-full text-left px-3 py-2 font-semibold rounded hover:bg-gray-100 {{ request()->routeIs('discounts.*','users.*','bank-accounts.*') ? 'bg-gray-200' : '' }}">
                    Configuración
                </button>
                <div x-show="open" x-cloak class="pl-6 space-y-1">
                    <a href="{{ route('discounts.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('discounts.*') ? 'bg-gray-200' : '' }}">Descuentos</a>
                    <a href="{{ route('bank-accounts.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('bank-accounts.*') ? 'bg-gray-200' : '' }}">Cuentas Bancarias</a>
                    <a href="{{ route('users.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('users.*') ? 'bg-gray-200' : '' }}">Usuarios</a>
                </div>
            </div>
            @endif
            <a href="{{ route('profile.edit') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('profile.*') ? 'bg-gray-200' : '' }}">
                Perfil
            </a>
        </nav>
    </div>
</aside>
