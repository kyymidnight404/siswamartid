@props(['cancelUrl', 'submitLabel' => 'Simpan perubahan'])
<div {{ $attributes->class(['ui-form-actions']) }}>
    <a href="{{ $cancelUrl }}" class="ui-button ui-button--secondary">Batal</a>
    <button type="submit" class="ui-button ui-button--primary">{{ $submitLabel }}</button>
</div>
