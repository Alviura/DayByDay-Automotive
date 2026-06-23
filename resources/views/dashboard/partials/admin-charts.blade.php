@if ($showChart ?? false)
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const chartData = @json($chartData ?? []);

                Chart.defaults.font.family = "'Figtree', system-ui, sans-serif";
                Chart.defaults.font.size = 11;
                Chart.defaults.color = '#9ca3af';

                const axisGrid = { color: '#f3f4f6' };
                const formatK = v => v >= 1000 ? (v / 1000).toFixed(v >= 10000 ? 0 : 1) + 'k' : v;

                const salesCanvas = document.getElementById('dbSalesChart');
                if (salesCanvas && chartData.monthly) {
                    const monthly = chartData.monthly;
                    new Chart(salesCanvas, {
                        type: 'bar',
                        data: {
                            labels: monthly.labels,
                            datasets: [
                                {
                                    label: 'Revenue (KES)',
                                    data: monthly.revenue,
                                    backgroundColor: 'rgba(255, 107, 53, 0.78)',
                                    borderRadius: 5,
                                    yAxisID: 'y',
                                },
                                {
                                    label: 'Sales',
                                    data: monthly.counts,
                                    type: 'line',
                                    borderColor: '#8b5cf6',
                                    backgroundColor: 'transparent',
                                    borderWidth: 2,
                                    pointRadius: 3,
                                    tension: .35,
                                    yAxisID: 'y1',
                                },
                            ],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: { mode: 'index', intersect: false },
                            plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, padding: 12 } } },
                            scales: {
                                y: { beginAtZero: true, grid: axisGrid, ticks: { callback: formatK } },
                                y1: { beginAtZero: true, position: 'right', grid: { display: false } },
                                x: { grid: { display: false } },
                            },
                        },
                    });
                }

                const procurementCanvas = document.getElementById('dbProcurementChart');
                if (procurementCanvas && chartData.procurement) {
                    const procurement = chartData.procurement;
                    const hasData = procurement.counts?.some(c => c > 0);

                    new Chart(procurementCanvas, {
                        type: 'doughnut',
                        data: {
                            labels: hasData ? procurement.labels : ['No open series'],
                            datasets: [{
                                data: hasData ? procurement.counts : [1],
                                backgroundColor: hasData ? procurement.colors : ['#e5e7eb'],
                                borderWidth: 2,
                                borderColor: '#fff',
                            }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '62%',
                            plugins: {
                                legend: { position: 'bottom', labels: { boxWidth: 10, padding: 10, font: { size: 10 } } },
                            },
                        },
                    });
                }

                const shopCanvas = document.getElementById('dbShopRevenueChart');
                if (shopCanvas && chartData.revenueByShop) {
                    const shops = chartData.revenueByShop;
                    const hasData = shops.revenue?.length > 0;

                    new Chart(shopCanvas, {
                        type: 'bar',
                        data: {
                            labels: hasData ? shops.labels : ['No sales'],
                            datasets: [{
                                label: 'Revenue (KES)',
                                data: hasData ? shops.revenue : [0],
                                backgroundColor: 'rgba(34, 197, 94, 0.75)',
                                borderRadius: 5,
                            }],
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } },
                            scales: {
                                x: { beginAtZero: true, grid: axisGrid, ticks: { callback: formatK } },
                                y: { grid: { display: false } },
                            },
                        },
                    });
                }
            });
        </script>
    @endpush
@endif
