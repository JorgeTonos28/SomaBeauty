<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center btn-primary font-semibold text-xs uppercase focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
