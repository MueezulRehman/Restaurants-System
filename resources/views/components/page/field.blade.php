@props([
    'name',
    'label',
    'type' => 'text',
    'help' => null,
    'required' => false,
])

@php($fieldId = $attributes->get('id', $name))

<div {{ $attributes->except('id')->merge(['class' => 'page-field']) }}>
    <label for="{{ $fieldId }}">
        {{ $label }}@if($required) <span class="text-red-600" aria-hidden="true">*</span>@endif
    </label>
    {{ $slot ?? null }}
    @if($type !== 'slot')
        <input
            id="{{ $fieldId }}"
            name="{{ $name }}"
            type="{{ $type }}"
            value="{{ old($name) }}"
            @required($required)
            {{ $attributes->only(['placeholder', 'min', 'max', 'step', 'autocomplete', 'rows', 'disabled', 'readonly']) }}
        >
    @endif
    @if($help)
        <p class="page-field__help">{{ $help }}</p>
    @endif
    @error($name)
        <p class="page-field__error">{{ $message }}</p>
    @enderror
</div>
