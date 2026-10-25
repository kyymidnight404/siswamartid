@props(['name', 'initials' => '?', 'src' => null])
<div class="ui-avatar-upload" x-data="{ preview: @js($src) }">
    <div class="ui-avatar-upload__preview" aria-live="polite"><template x-if="preview"><img :src="preview" alt="Pratinjau foto"></template><template x-if="!preview"><span>{{ $initials }}</span></template></div>
    <label class="ui-button ui-button--secondary" for="{{ $name }}">Ganti foto</label>
    <input class="ui-visually-hidden" type="file" id="{{ $name }}" name="{{ $name }}" accept="image/jpeg,image/png" @change="const file = $event.target.files[0]; if (file) preview = URL.createObjectURL(file)">
    <p>JPG atau PNG, maksimal 2 MB</p>
</div>
