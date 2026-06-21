<x-app-layout title="Reports">
    @push('styles')<x-module.page-index-styles />@endpush
    <div class="mi-page space-y-5">
        <div class="flex items-start gap-3">
            <div class="mi-page-icon"><i class="fas fa-chart-line"></i></div>
            <div>
                <h1 class="text-[1.35rem] font-bold text-gray-900">Reports</h1>
                <p class="text-sm text-gray-500">Sales, inventory, procurement, transfers, and financial summaries.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            @foreach ([
                ['sales', 'fa-cash-register', 'Sales', 'Revenue, tickets, top sellers'],
                ['inventory', 'fa-boxes-stacked', 'Inventory', 'Valuation, low stock, movements'],
                ['procurement', 'fa-folder-open', 'Quotation Series', 'Series, POs, receipts'],
                ['transfers', 'fa-right-left', 'Transfers', 'Requests and dispatch activity'],
                ['financial', 'fa-coins', 'Financial', 'Revenue, refunds, payment mix'],
            ] as [$slug, $icon, $title, $desc])
                <a href="{{ route('reports.'.$slug) }}" class="mi-card p-5 hover:border-orange-200 transition group">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-lg bg-orange-50 flex items-center justify-center text-orange-500 group-hover:bg-orange-100">
                            <i class="fas {{ $icon }}"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">{{ $title }}</p>
                            <p class="text-sm text-gray-500 mt-0.5">{{ $desc }}</p>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</x-app-layout>
