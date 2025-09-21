@php
    $logoUrl = asset('images/logo.png');
    $cacheBuster = optional($appearanceSettings)->logo_updated_at?->timestamp;

    if ($cacheBuster) {
        $logoUrl .= '?v=' . $cacheBuster;
    }
@endphp
<img src="{{ $logoUrl }}" alt="Logo" {{ $attributes }}>
