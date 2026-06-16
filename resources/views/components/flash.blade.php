@if (session('status'))
    <div class="rounded-md bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm">
        {{ session('status') }}
    </div>
@endif

@if (session('error'))
    <div class="rounded-md bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm">
        {{ session('error') }}
    </div>
@endif
