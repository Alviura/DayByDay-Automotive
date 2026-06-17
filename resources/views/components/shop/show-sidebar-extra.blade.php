@props(['shop'])

@can('shops.manage')
    <section class="mi-guide-section">
        <h3 class="mi-guide-section-title">
            <i class="fas fa-trash"></i> Deletion rules
        </h3>
        <ul class="mi-guide-tips">
            <li><i class="fas fa-check"></i> Reassign all staff before deleting.</li>
            <li><i class="fas fa-check"></i> Stock on hand must be zero to delete.</li>
        </ul>

        @if ($shop->users_count > 0 || $shop->stock_balances_count > 0)
            <div class="mi-guide-note mi-guide-note-amber">
                <i class="fas fa-triangle-exclamation"></i>
                <p>
                    @if ($shop->users_count > 0)
                        This shop still has {{ $shop->users_count }} assigned {{ Str::plural('user', $shop->users_count) }}.
                    @endif
                    @if ($shop->users_count > 0 && $shop->stock_balances_count > 0)
                        <br>
                    @endif
                    @if ($shop->stock_balances_count > 0)
                        {{ $shop->stock_balances_count }} stock balance {{ Str::plural('row', $shop->stock_balances_count) }} on record.
                    @endif
                </p>
            </div>
        @else
            <form action="{{ route('shops.destroy', $shop) }}" method="POST" class="mt-3"
                  onsubmit="return confirm('Delete {{ addslashes($shop->name) }}? This cannot be undone easily.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="mi-btn-danger w-full justify-center">
                    <i class="fas fa-trash text-xs"></i>
                    Delete Shop
                </button>
            </form>
        @endif
    </section>
@endcan
