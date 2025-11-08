<a {{ $attributes->merge(['class' => 'group relative block w-full px-4 py-2 text-start text-sm font-medium leading-5 text-gray-700 dark:text-gray-200 hover:text-gray-900 dark:hover:text-white focus:text-gray-900 dark:focus:text-white transition-colors duration-150 ease-in-out']) }}>
	<span class="relative z-10">
		{{ $slot }}
	</span>
	<span class="pointer-events-none absolute inset-0 rounded-md opacity-0 group-hover:opacity-100 group-focus:opacity-100 transition-opacity duration-200 bg-gray-100 dark:bg-gray-700/70"></span>
</a>
