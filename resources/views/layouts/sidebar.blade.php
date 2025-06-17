<aside class="w-48 bg-white border-r border-gray-200">
    <div class="p-4">
        <nav class="space-y-2">
            <a href="{{ route('dashboard') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('dashboard') ? 'bg-gray-200' : '' }}">
                <svg class="inline-block w-4 h-4 mr-1 text-current" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/>
                </svg>
                Panel de Control
            </a>
            <a href="{{ route('petty-cash.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('petty-cash.*') ? 'bg-gray-200' : '' }}">
                <svg class="inline-block w-4 h-4 mr-1 text-current" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4.5a2.25 2.25 0 0 0-4.5 0V6M9 16.5v3m0 0a2.25 2.25 0 1 0 4.5 0v-3m-4.5 0H3.75A2.25 2.25 0 0 1 1.5 14.25V9.75A2.25 2.25 0 0 1 3.75 7.5H20.25A2.25 2.25 0 0 1 22.5 9.75v4.5a2.25 2.25 0 0 1-2.25 2.25H15" />
                </svg>
                Caja Chica
            </a>
            <a href="{{ route('washers.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('washers.*') ? 'bg-gray-200' : '' }}">
                <svg class="inline-block w-4 h-4 mr-1 text-current" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5V4H2v16h5m0 0v-2.5A2.5 2.5 0 0 1 9.5 15h5a2.5 2.5 0 0 1 2.5 2.5V20m-10 0h10" />
                </svg>
                Lavadores
            </a>
            <div x-data="{ open: {{ request()->routeIs('services.*','products.*','drinks.*') ? 'true' : 'false' }} }">
                <button type="button" @click="open=!open" class="w-full text-left px-3 py-2 font-semibold rounded hover:bg-gray-100 {{ request()->routeIs('services.*','products.*','drinks.*') ? 'bg-gray-200' : '' }}">
                    <svg class="inline-block w-4 h-4 mr-1 text-current" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3v18h18V3H3zm4.5 4.5h9m-9 4.5h9m-9 4.5h9" />
                    </svg>
                    Catálogo
                </button>
                <div x-show="open" x-cloak class="pl-6 space-y-1">
                    <a href="{{ route('services.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('services.*') ? 'border-b-2 border-gray-500' : '' }}">
                        <svg class="inline-block w-4 h-4 mr-1 text-current" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3l1.61 14.49a1.125 1.125 0 0 0 1.12.99h11.04a1.125 1.125 0 0 0 1.12-.99L20.25 3H3.75zm9 18a1.5 1.5 0 0 1-3 0" />
                        </svg>
                        Servicios
                    </a>
                    <a href="{{ route('products.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('products.*') ? 'border-b-2 border-gray-500' : '' }}">
                        <svg class="inline-block w-4 h-4 mr-1 text-current" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 3.75A1.5 1.5 0 0 0 6 5.25V21l6-3 6 3V5.25a1.5 1.5 0 0 0-1.5-1.5h-9z" />
                        </svg>
                        Productos
                    </a>
                    <a href="{{ route('drinks.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('drinks.*') ? 'border-b-2 border-gray-500' : '' }}">
                        <svg class="inline-block w-4 h-4 mr-1 text-current" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3h10.5M9 3v12a3 3 0 1 0 6 0V3" />
                        </svg>
                        Tragos
                    </a>
                </div>
            </div>
            <div x-data="{ open: {{ request()->routeIs('tickets.*') ? 'true' : 'false' }} }">
                <button type="button" @click="open=!open" class="w-full text-left px-3 py-2 font-semibold rounded hover:bg-gray-100 {{ request()->routeIs('tickets.*') ? 'bg-gray-200' : '' }}">
                    <svg class="inline-block w-4 h-4 mr-1 text-current" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 9.75h15M5.25 6h13.5a.75.75 0 0 1 .75.75v10.5a.75.75 0 0 1-.75.75H5.25a.75.75 0 0 1-.75-.75V6.75a.75.75 0 0 1 .75-.75z" />
                    </svg>
                    Tickets
                </button>
                <div x-show="open" x-cloak class="pl-6 space-y-1">
                    <a href="{{ route('tickets.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('tickets.index') ? 'border-b-2 border-gray-500' : '' }}">
                        <svg class="inline-block w-4 h-4 mr-1 text-current" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Activos
                    </a>
                    <a href="{{ route('tickets.canceled') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('tickets.canceled') ? 'border-b-2 border-gray-500' : '' }}">
                        <svg class="inline-block w-4 h-4 mr-1 text-current" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75l10.5 10.5m0-10.5L6.75 17.25" />
                        </svg>
                        Cancelados
                    </a>
                </div>
            </div>
            @if(auth()->user()->role === 'admin')
            <div x-data="{ open: {{ request()->routeIs('discounts.*','users.*','bank-accounts.*') ? 'true' : 'false' }} }">
                <button type="button" @click="open=!open" class="w-full text-left px-3 py-2 font-semibold rounded hover:bg-gray-100 {{ request()->routeIs('discounts.*','users.*','bank-accounts.*') ? 'bg-gray-200' : '' }}">
                    <svg class="inline-block w-4 h-4 mr-1 text-current" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12a7.5 7.5 0 0 1 15 0 7.5 7.5 0 0 1-15 0z" />
                    </svg>
                    Configuración
                </button>
                <div x-show="open" x-cloak class="pl-6 space-y-1">
                    <a href="{{ route('discounts.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('discounts.*') ? 'border-b-2 border-gray-500' : '' }}">
                        <svg class="inline-block w-4 h-4 mr-1 text-current" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75v10.5M6.75 6.75v10.5m-1.5-9h14.5m-14.5 4.5h14.5" />
                        </svg>
                        Descuentos
                    </a>
                    <a href="{{ route('bank-accounts.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('bank-accounts.*') ? 'border-b-2 border-gray-500' : '' }}">
                        <svg class="inline-block w-4 h-4 mr-1 text-current" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l9.72-9.72a.75.75 0 0 1 1.06 0l9.72 9.72m-19.5 0h19.5M4.5 12v8.25a.75.75 0 0 0 .75.75h13.5a.75.75 0 0 0 .75-.75V12" />
                        </svg>
                        Cuentas Bancarias
                    </a>
                    <a href="{{ route('users.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('users.*') ? 'border-b-2 border-gray-500' : '' }}">
                        <svg class="inline-block w-4 h-4 mr-1 text-current" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-4.215A3 3 0 0 0 15.101 10.8L12 10.2l-3.101.6a3 3 0 0 0-3.494 1.985L4 17h5m6 0v2.25a3 3 0 1 1-6 0V17m6 0h-6" />
                        </svg>
                        Usuarios
                    </a>
                </div>
            </div>
            @endif
            <a href="{{ route('profile.edit') }}" class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('profile.*') ? 'bg-gray-200' : '' }}">
                <svg class="inline-block w-4 h-4 mr-1 text-current" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 7.5a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0zM4.5 20.25a8.25 8.25 0 0 1 15 0" />
                </svg>
                Perfil
            </a>
        </nav>
    </div>
</aside>
