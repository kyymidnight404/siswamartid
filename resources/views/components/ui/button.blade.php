@props(['variant' => 'primary'])
<button {{ $attributes->class(['ui-button', 'ui-button--'.$variant]) }}>{{ $slot }}</button>
