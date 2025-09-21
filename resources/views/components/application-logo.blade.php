@props(['variant' => 'default'])

@php
    $variant = $variant === 'login' ? 'login' : 'default';
    $filename = $variant === 'login' ? 'login-logo.png' : 'logo.png';
    $timestampField = $variant === 'login' ? 'login_logo_updated_at' : 'logo_updated_at';
    $imagePath = public_path('images/' . $filename);

    if (! file_exists($imagePath)) {
        $filename = 'logo.png';
        $timestampField = 'logo_updated_at';
    }

    $logoUrl = asset('images/' . $filename);
    $cacheBuster = optional($appearanceSettings)->{$timestampField}?->timestamp;

    if ($cacheBuster) {
        $logoUrl .= '?v=' . $cacheBuster;
    }
@endphp
<img src="{{ $logoUrl }}" alt="Logo" {{ $attributes->except('variant') }}>
