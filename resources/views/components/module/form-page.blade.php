@props([
    'title',
    'subtitle',
    'icon' => 'fa-box',
    'cardTitle' => 'Details',
    'backUrl',
    'cancelUrl' => null,
    'action',
    'method' => 'POST',
    'submitLabel',
    'isEdit' => false,
])

@php
    $cancelUrl = $cancelUrl ?? $backUrl;
@endphp

<div class="mi-page space-y-5">

    {{-- Page header --}}
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div class="flex items-start gap-3">
            <div class="mi-page-icon">
                <i class="fas {{ $icon }}"></i>
            </div>
            <div>
                <h1 class="text-[1.35rem] font-bold text-gray-900 leading-tight">{{ $title }}</h1>
                <p class="mt-0.5 text-sm text-gray-500">{{ $subtitle }}</p>
            </div>
        </div>
        <a href="{{ $backUrl }}" class="mi-btn-ghost">
            <i class="fas fa-arrow-left text-xs"></i>
            Back to List
        </a>
    </div>

    {{-- Form + guide --}}
    <div class="mi-form-split">
        <div class="mi-card mi-form-main">
            <div class="mi-card-head">
                <div class="flex items-center gap-2 text-gray-700">
                    <i class="fas fa-pen-to-square text-gray-400 text-sm"></i>
                    <span class="text-sm font-semibold">{{ $cardTitle }}</span>
                </div>
                @isset($cardMeta)
                    {{ $cardMeta }}
                @endisset
            </div>

            <form method="POST" action="{{ $action }}">
                @csrf
                @if ($method !== 'POST')
                    @method($method)
                @endif

                <div class="mi-form-body">
                    {{ $slot }}
                </div>

                <div class="mi-form-actions">
                    <a href="{{ $cancelUrl }}" class="mi-btn-ghost">Cancel</a>
                    <button type="submit" class="mi-btn-orange">
                        <i class="fas fa-{{ $isEdit ? 'check' : 'plus' }} text-xs"></i>
                        {{ $submitLabel }}
                    </button>
                </div>
            </form>
        </div>

        @isset($guide)
            {{ $guide }}
        @endisset
    </div>
</div>
