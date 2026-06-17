@if (session('status') || session('error'))
    <div {{ $attributes->merge(['class' => 'space-y-3']) }}>
        @if (session('status'))
            <div class="flex items-start gap-2 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                <i class="fas fa-circle-check mt-0.5"></i>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="flex items-start gap-2 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <i class="fas fa-circle-exclamation mt-0.5"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif
    </div>
@endif
