@props([
    'name',
    'label',
    'accept' => null,
    'help' => 'PNG, JPG, WEBP up to 5 MB.',
    'preview' => null,
    'multiple' => false,
])

@php($fieldId = $attributes->get('id', $name))

<div {{ $attributes->except('id')->merge(['class' => 'page-field page-file-field']) }}>
    <label for="{{ $fieldId }}">{{ $label }}</label>
    <label class="page-file" for="{{ $fieldId }}">
        <span class="page-file__icon" aria-hidden="true"><i class="fas fa-cloud-arrow-up"></i></span>
        <span>
            <strong>Choose {{ $multiple ? 'files' : 'a file' }}</strong>
            <span class="page-file__name">No file selected</span>
        </span>
        <input
            id="{{ $fieldId }}"
            name="{{ $multiple ? $name . '[]' : $name }}"
            type="file"
            @if($accept) accept="{{ $accept }}" @endif
            @if($multiple) multiple @endif
            data-file-input
        >
    </label>
    @if($preview)
        <img class="page-file__preview" src="{{ $preview }}" alt="Current {{ strtolower($label) }}">
    @endif
    @if($help)
        <p class="page-field__help">{{ $help }}</p>
    @endif
    @error($name)
        <p class="page-field__error">{{ $message }}</p>
    @enderror
</div>
