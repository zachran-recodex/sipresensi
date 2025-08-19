@props([
    'variant' => 'outline',
])

@php
    if ($variant === 'solid') {
        throw new \Exception('The "solid" variant is not supported in Lucide.');
    }

    $classes = Flux::classes('shrink-0')->add(
        match ($variant) {
            'outline' => '[:where(&)]:size-6',
            'solid' => '[:where(&)]:size-6',
            'mini' => '[:where(&)]:size-5',
            'micro' => '[:where(&)]:size-4',
        },
    );

    $strokeWidth = match ($variant) {
        'outline' => 1.5,
        'mini' => 1.75,
        'micro' => 2,
    };
@endphp

<svg
    {{ $attributes->class($classes) }}
    data-flux-icon
    xmlns="http://www.w3.org/2000/svg"
    viewBox="0 0 24 24"
    fill="none"
    stroke="currentColor"
    stroke-width="{{ $strokeWidth }}"
    stroke-linecap="round"
    stroke-linejoin="round"
    aria-hidden="true"
    data-slot="icon"
>
    <path d="M7 3H5C3.89543 3 3 3.89543 3 5V7" />
    <path d="M17 3H19C20.1046 3 21 3.89543 21 5V7" />
    <path d="M16 8L16 10" />
    <path d="M8 8L8 10" />
    <path d="M9 16C9 16 10 17 12 17C14 17 15 16 15 16" />
    <path d="M12 8L12 13L11 13" />
    <path d="M7 21H5C3.89543 21 3 20.1046 3 19V17" />
    <path d="M17 21H19C20.1046 21 21 20.1046 21 19V17" />
</svg>
