@props(['label', 'name', 'help' => null])
<div {{ $attributes->class(['ui-field']) }}>
    <label for="{{ $name }}">{{ $label }}</label>
    {{ $slot }}
    @if($help)<p class="ui-help">{{ $help }}</p>@endif
    @if($errors->has($name))<p class="ui-error" role="alert"><span aria-hidden="true">!</span> {{ $errors->first($name) }}</p>@endif
</div>
