@props([
    'name',
    'id' => null,
    'label' => null,
    'placeholder' => '',
    'required' => false,
    'autofocus' => false,
    'autocomplete' => null,
    'errorKey' => null,
    'icon' => false,
    'minlength' => null,
    'readonly' => false,
    'value' => null,
    'inputClass' => '',
    'labelClass' => 'block text-xs font-semibold text-[#6B7280] mb-1.5 uppercase tracking-wide',
])

@php
    $inputId = $id ?? $name;
    $errorField = $errorKey ?? $name;
    $hasError = $errors->has($errorField);
    $padLeft = $icon ? 'pl-10' : 'px-3';
@endphp

<div {{ $attributes->only('class')->merge(['class' => '']) }}>
    @if($label)
    <label for="{{ $inputId }}" class="{{ $labelClass }}">
        {{ $label }}
    </label>
    @endif

    <div class="relative" x-data="{ show: false }">
        @if($icon)
        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[#6B7280] text-lg pointer-events-none">lock</span>
        @endif

        <input
            id="{{ $inputId }}"
            name="{{ $name }}"
            :type="show ? 'text' : 'password'"
            @if($required) required @endif
            @if($autofocus) autofocus @endif
            @if($autocomplete) autocomplete="{{ $autocomplete }}" @endif
            @if($minlength) minlength="{{ $minlength }}" @endif
            @if($readonly) readonly @endif
            @if($value !== null) value="{{ $value }}" @endif
            placeholder="{{ $placeholder }}"
            class="{{ $padLeft }} pr-10 py-2.5 w-full border rounded-lg text-sm text-[#1F2937] bg-white focus:outline-none focus:border-[#002452] focus:ring-2 focus:ring-[#002452]/10 transition-all {{ $hasError ? 'border-[#EF4444]' : 'border-[#E5E7EB]' }} {{ $inputClass }}"
        />

        <button
            type="button"
            @click="show = !show"
            class="absolute right-3 top-1/2 -translate-y-1/2 text-[#6B7280] hover:text-[#002452] transition-colors"
            :aria-label="show ? 'Masquer le mot de passe' : 'Afficher le mot de passe'"
        >
            <span class="material-symbols-outlined text-lg" x-text="show ? 'visibility_off' : 'visibility'">visibility</span>
        </button>
    </div>

    @error($errorField)
    <p class="text-[#EF4444] text-xs mt-1 flex items-center gap-1">
        <span class="material-symbols-outlined text-sm">error</span>{{ $message }}
    </p>
    @enderror
</div>
