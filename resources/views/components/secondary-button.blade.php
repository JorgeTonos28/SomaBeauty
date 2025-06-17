<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center btn-secondary font-semibold text-xs uppercase shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
