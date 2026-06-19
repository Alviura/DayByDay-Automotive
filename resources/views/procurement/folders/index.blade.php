<x-app-layout title="Procurement Folders">

    @push('styles')
        <x-module.page-index-styles />
    @endpush

    <div class="mi-page space-y-5">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mi-page-icon"><i class="fas fa-folder-open"></i></div>
                <div>
                    <h1 class="text-[1.35rem] font-bold text-gray-900">Procurement Folders</h1>
                    <p class="text-sm text-gray-500">Folder → cost analysis → approval → PO → receipt → close</p>
                </div>
            </div>
            @can('procurement.manage')
                <a href="{{ route('procurement.folders.create') }}" class="mi-btn-orange">
                    <i class="fas fa-plus text-xs"></i> New Folder
                </a>
            @endcan
        </div>

        <div class="mi-kpi-row">
            <div class="mi-kpi mi-kpi-purple"><div><p class="mi-kpi-label">Total</p><p class="mi-kpi-value">{{ $stats['total'] }}</p></div><div class="mi-kpi-icon"><i class="fas fa-folder"></i></div></div>
            <div class="mi-kpi mi-kpi-amber"><div><p class="mi-kpi-label">Draft</p><p class="mi-kpi-value">{{ $stats['draft'] }}</p></div><div class="mi-kpi-icon"><i class="fas fa-pen"></i></div></div>
            <div class="mi-kpi mi-kpi-orange"><div><p class="mi-kpi-label">Pending Approval</p><p class="mi-kpi-value orange">{{ $stats['pending'] }}</p></div><div class="mi-kpi-icon"><i class="fas fa-hourglass-half"></i></div></div>
            <div class="mi-kpi mi-kpi-green"><div><p class="mi-kpi-label">In Transit</p><p class="mi-kpi-value">{{ $stats['in_transit'] }}</p></div><div class="mi-kpi-icon"><i class="fas fa-truck"></i></div></div>
        </div>

        <div class="mi-card">
            <div class="mi-table-wrap">
                <table class="mi-table">
                    <thead>
                        <tr>
                            <th>Folder</th>
                            <th>Supplier</th>
                            <th>Lines</th>
                            <th>Landing Cost</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($folders as $folder)
                            <tr>
                                <td><a href="{{ route('procurement.folders.show', $folder) }}" class="mi-cat-badge">{{ $folder->folder_number }}</a></td>
                                <td>{{ $folder->supplier?->name }}</td>
                                <td>{{ $folder->items_count }}</td>
                                <td>{{ number_format($folder->total_landing_cost, 2) }} {{ $folder->currency }}</td>
                                <td><span class="mi-status-pending">{{ $folder->statusLabel() }}</span></td>
                                <td class="text-sm text-gray-500">{{ $folder->created_at->format('d M Y') }}</td>
                                <td><a href="{{ route('procurement.folders.show', $folder) }}" class="mi-action view"><i class="fas fa-eye"></i></a></td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="!py-14 text-center text-gray-400">No procurement folders yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($folders->hasPages())<div class="mi-card-foot">{{ $folders->links() }}</div>@endif
        </div>
    </div>
</x-app-layout>
