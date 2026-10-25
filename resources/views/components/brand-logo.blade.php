@props([
    'href' => null,
    'label' => 'SiswaMart',
    'static' => false,
])

@if ($static || ! $href)
    <span role="img" {{ $attributes->merge(['class' => 'sm-brand-logo', 'aria-label' => $label]) }}><span>Siswa</span><span>Mart</span></span>
@else
    <a href="{{ $href }}" {{ $attributes->merge(['class' => 'sm-brand-logo', 'aria-label' => $label]) }}><span>Siswa</span><span>Mart</span></a>
@endif
