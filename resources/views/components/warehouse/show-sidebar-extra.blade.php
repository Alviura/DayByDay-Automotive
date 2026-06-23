@props(['warehouse', 'totals' => null, 'locationType' => 'warehouse'])

@php
    $filters = ['location_type' => $locationType, 'location_id' => $warehouse->id];
@endphp

@can('inventory.view')
    <section class="mi-guide-section">
        <h3 class="mi-guide-section-title"><i class="fas fa-boxes-stacked"></i> Inventory</h3>
        <ul class="mi-guide-tips">
            <li>
                <a href="{{ route('inventory.valuation', $filters) }}" class="text-orange-600 hover:underline font-semibold">
                    <i class="fas fa-coins"></i> Stock valuation
                </a>
                @if ($totals)
                    — KES {{ number_format($totals['value'], 2) }}
                @endif
            </li>
            <li>
                <a href="{{ route('inventory.movements', $filters) }}" class="text-orange-600 hover:underline font-semibold">
                    <i class="fas fa-right-left"></i> Movement history
                </a>
            </li>
            <li>
                <a href="{{ route('inventory.index', $filters) }}" class="text-orange-600 hover:underline font-semibold">
                    <i class="fas fa-list"></i> Product stock list
                </a>
            </li>
        </ul>
    </section>
@endcan

@can('returns.create')
    <section class="mi-guide-section">
        <h3 class="mi-guide-section-title"><i class="fas fa-truck-ramp-box"></i> Returns</h3>
        <ul class="mi-guide-tips">
            <li>
                <a href="{{ route('supplier-returns.create', ['warehouse_id' => $warehouse->id]) }}" class="text-orange-600 hover:underline font-semibold">
                    <i class="fas fa-plus"></i> New supplier return
                </a>
            </li>
        </ul>
    </section>
@endcan

@can('warehouses.manage')
    <section class="mi-guide-section">
        <h3 class="mi-guide-section-title">
            <i class="fas fa-trash"></i> Deletion rules
        </h3>
        <ul class="mi-guide-tips">
            <li><i class="fas fa-check"></i> Reassign all staff before deleting.</li>
            <li><i class="fas fa-check"></i> Stock on hand must be zero to delete.</li>
        </ul>

        @if ($warehouse->users_count > 0 || ($totals && $totals['on_hand'] > 0))
            <div class="mi-guide-note mi-guide-note-amber">
                <i class="fas fa-triangle-exclamation"></i>
                <p>
                    @if ($warehouse->users_count > 0)
                        This warehouse still has {{ $warehouse->users_count }} assigned {{ Str::plural('user', $warehouse->users_count) }}.
                    @endif
                    @if ($warehouse->users_count > 0 && $totals && $totals['on_hand'] > 0)<br>@endif
                    @if ($totals && $totals['on_hand'] > 0)
                        {{ number_format($totals['on_hand'], 0) }} units still on hand.
                    @endif
                </p>
            </div>
        @else
            <form action="{{ route('warehouses.destroy', $warehouse) }}" method="POST" class="mt-3" data-confirm="Delete {{ addslashes($warehouse->name) }}? This cannot be undone easily." data-confirm-variant="danger">
                @csrf @method('DELETE')
                <button type="submit" class="mi-btn-danger w-full justify-center">
                    <i class="fas fa-trash text-xs"></i> Delete Warehouse
                </button>
            </form>
        @endif
    </section>
@endcan
