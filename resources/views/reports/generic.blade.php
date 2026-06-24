<x-app-layout :title="$definition['label'] ?? 'Report'">
    @push('styles')<x-module.page-index-styles />@endpush
    <div class="mi-page space-y-5">
        <x-reports.page-header :definition="$definition" :filters="$filters" :slug="$slug" />

        <x-reports.filters
            :filters="$filters"
            :report-type="$slug"
            :definition="$definition"
            :shops="$shops"
            :warehouses="$warehouses"
            :suppliers="$suppliers"
            :scoped-shop-id="$scopedShopId"
            :scoped-warehouse-id="$scopedWarehouseId"
        />

        @if (!empty($summary))
            <x-reports.kpi-row :summary="$summary" />
        @endif

        @if (!empty($rows) && count($rows))
            <x-reports.data-table title="Results" :rows="$rows" />
        @endif

        @if (!empty($daily) && count($daily))
            <x-reports.data-table title="Daily Breakdown" :rows="$daily" />
        @endif

        @if (!empty($topProducts) && count($topProducts))
            <x-reports.data-table title="Top Products" :rows="$topProducts" />
        @endif

        @if (!empty($topReasons) && count($topReasons))
            <x-reports.data-table title="Top Reasons" :rows="$topReasons" />
        @endif

        @if (!empty($locations) && count($locations))
            <x-reports.data-table title="By Location" :rows="$locations" />
        @endif

        @if (!empty($movements) && count($movements))
            <x-reports.data-table title="Movements" :rows="$movements" />
        @endif

        @if (!empty($lowStock) && count($lowStock))
            <x-reports.data-table title="Low Stock" :rows="$lowStock" />
        @endif

        @if (!empty($paymentBreakdown) && count($paymentBreakdown))
            <x-reports.data-table title="Payment Mix" :rows="$paymentBreakdown" />
        @endif

        @if (!empty($saleTypeBreakdown) && count($saleTypeBreakdown))
            <x-reports.data-table title="By Sale Type" :rows="$saleTypeBreakdown" />
        @endif

        @if (!empty($statusBreakdown) && count($statusBreakdown))
            <x-reports.data-table title="Status Breakdown" :rows="$statusBreakdown" />
        @endif

        @if (!empty($recent) && count($recent))
            <x-reports.data-table title="Recent" :rows="$recent" />
        @endif

        @if (!empty($recentSeries) && count($recentSeries))
            <x-reports.data-table title="Recent Series" :rows="$recentSeries" />
        @endif

        @if (!empty($recentPos) && count($recentPos))
            <x-reports.data-table title="Recent POs" :rows="$recentPos" />
        @endif

        @if (!empty($recentRequests) && count($recentRequests))
            <x-reports.data-table title="Recent Requests" :rows="$recentRequests" />
        @endif

        @if (empty($rows) && empty($daily) && empty($summary))
            <div class="mi-card p-10 text-center text-gray-400">No data for this report.</div>
        @endif
    </div>
</x-app-layout>
