@props(['warehouse'])

@can('warehouses.manage')
    <section class="mi-guide-section">
        <h3 class="mi-guide-section-title">
            <i class="fas fa-trash"></i> Deletion rules
        </h3>
        <ul class="mi-guide-tips">
            <li><i class="fas fa-check"></i> Reassign all staff before deleting.</li>
            <li><i class="fas fa-check"></i> Stock on hand must be zero to delete.</li>
        </ul>

        @if ($warehouse->users_count > 0 || $warehouse->stock_balances_count > 0)
            <div class="mi-guide-note mi-guide-note-amber">
                <i class="fas fa-triangle-exclamation"></i>
                <p>
                    @if ($warehouse->users_count > 0)
                        This warehouse still has {{ $warehouse->users_count }} assigned {{ Str::plural('user', $warehouse->users_count) }}.
                    @endif
                    @if ($warehouse->users_count > 0 && $warehouse->stock_balances_count > 0)
                        <br>
                    @endif
                    @if ($warehouse->stock_balances_count > 0)
                        {{ $warehouse->stock_balances_count }} stock balance {{ Str::plural('row', $warehouse->stock_balances_count) }} on record.
                    @endif
                </p>
            </div>
        @else
            <form action="{{ route('warehouses.destroy', $warehouse) }}" method="POST" class="mt-3" data-confirm="Delete {{ addslashes($warehouse->name) }}? This cannot be undone easily." data-confirm-variant="danger">
                @csrf
                @method('DELETE')
                <button type="submit" class="mi-btn-danger w-full justify-center">
                    <i class="fas fa-trash text-xs"></i>
                    Delete Warehouse
                </button>
            </form>
        @endif
    </section>
@endcan
