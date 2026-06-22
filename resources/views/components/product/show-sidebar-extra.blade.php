@props(['product'])

@can('products.archive')
    <section class="mi-guide-section">
        <h3 class="mi-guide-section-title">
            <i class="fas fa-trash"></i> Deletion rules
        </h3>
        <ul class="mi-guide-tips">
            <li><i class="fas fa-check"></i> Products with stock movements cannot be hard-deleted later.</li>
            <li><i class="fas fa-check"></i> Prefer deactivating over deleting when possible.</li>
        </ul>

        <form action="{{ route('products.destroy', $product) }}" method="POST" class="mt-3" data-confirm="Delete {{ addslashes($product->name) }}?" data-confirm-variant="danger">
            @csrf
            @method('DELETE')
            <button type="submit" class="mi-btn-danger w-full justify-center">
                <i class="fas fa-trash text-xs"></i>
                Delete Product
            </button>
        </form>
    </section>
@endcan
