@php
    $account = $account ?? null;
    $size = $size ?? 'md';
    $iconClass = 'fin-acct-icon '.$account->typePillClass();
    if ($size === 'lg') { $iconClass .= ' !w-11 !h-11 !text-base'; }
@endphp
@if ($account)
    <div class="fin-acct-icon {{ $account->typePillClass() }} {{ $size === 'lg' ? 'w-11 h-11 text-base' : '' }}">
        <i class="fas {{ $account->typeIcon() }}"></i>
    </div>
@endif
