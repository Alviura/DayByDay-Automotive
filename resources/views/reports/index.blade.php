<x-app-layout title="Reports">
    @push('styles')<x-module.page-index-styles />@endpush
    <div class="mi-page space-y-6">
        <div class="flex items-start gap-3">
            <div class="mi-page-icon"><i class="fas fa-chart-line"></i></div>
            <div>
                <h1 class="text-[1.35rem] font-bold text-gray-900">Reports</h1>
                <p class="text-sm text-gray-500">Operational insights across sales, inventory, procurement, transfers, returns, and receivables.</p>
            </div>
        </div>

        @foreach ($groupedReports as $categoryKey => $reports)
            @php $category = $categories[$categoryKey] ?? ['label' => ucfirst($categoryKey), 'icon' => 'fa-chart-bar']; @endphp
            <section class="space-y-3">
                <h2 class="text-sm font-bold uppercase tracking-wider text-gray-400 flex items-center gap-2">
                    <i class="fas {{ $category['icon'] }} text-orange-400"></i>
                    {{ $category['label'] }}
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                    @foreach ($reports as $report)
                        <a href="{{ route('reports.show', $report['slug']) }}" class="mi-card p-5 hover:border-orange-200 transition group">
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 rounded-lg bg-orange-50 flex items-center justify-center text-orange-500 group-hover:bg-orange-100">
                                    <i class="fas {{ $category['icon'] }}"></i>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900">{{ $report['label'] }}</p>
                                    <p class="text-sm text-gray-500 mt-0.5">{{ $report['description'] }}</p>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>
</x-app-layout>
