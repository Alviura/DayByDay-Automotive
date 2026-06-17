@props(['supplier'])

@can('suppliers.manage')
    <section class="mi-guide-section">
        <h3 class="mi-guide-section-title">
            <i class="fas fa-trash"></i> Deletion rules
        </h3>
        <ul class="mi-guide-tips">
            <li><i class="fas fa-check"></i> Remove or reassign linked products before deleting.</li>
            <li><i class="fas fa-check"></i> Open procurement folders should be closed first.</li>
        </ul>

        <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST" class="mt-3"
              onsubmit="return confirm('Delete {{ addslashes($supplier->name) }}? This cannot be undone easily.');">
            @csrf
            @method('DELETE')
            <button type="submit" class="mi-btn-danger w-full justify-center">
                <i class="fas fa-trash text-xs"></i>
                Delete Supplier
            </button>
        </form>
    </section>
@endcan
