@props(['active'])

@php
$classes = ($active ?? false)
    ? 'relative inline-flex items-center px-1 pt-1 pb-1 text-sm font-semibold leading-5 text-white after:absolute after:left-0 after:-bottom-0.5 after:h-0.5 after:w-full after:bg-white after:rounded after:content-[""] transition-all duration-200'
    : 'relative inline-flex items-center px-1 pt-1 pb-1 text-sm font-medium leading-5 text-white/85 hover:text-white focus:text-white after:absolute after:left-0 after:-bottom-0.5 after:h-0.5 after:w-0 hover:after:w-full focus:after:w-full after:bg-white/80 after:rounded after:transition-all after:duration-300 after:content-[""]';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
