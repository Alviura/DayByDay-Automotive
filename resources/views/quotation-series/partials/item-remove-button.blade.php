@props(['item', 'series'])

@can('procurement.manage')
    <form action="{{ route('quotation-series.items.destroy', [$series, $item]) }}" method="POST" class="inline"
          data-confirm="Remove this line from the quotation?"
          data-confirm-variant="danger">
        @csrf
        @method('DELETE')
        <button type="submit" class="mi-action del" title="Remove line">
            <i class="fas fa-trash-can"></i>
        </button>
    </form>
@endcan
