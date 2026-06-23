@if ($showChart ?? false)
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const canvas = document.getElementById('dbSalesChart');
                if (!canvas) return;

                const chartData = @json($chartData['monthly'] ?? ['labels' => [], 'counts' => [], 'revenue' => []]);

                Chart.defaults.font.family = "'Figtree', system-ui, sans-serif";
                Chart.defaults.font.size = 11;
                Chart.defaults.color = '#9ca3af';

                new Chart(canvas, {
                    type: 'bar',
                    data: {
                        labels: chartData.labels,
                        datasets: [
                            {
                                label: 'Revenue (KES)',
                                data: chartData.revenue,
                                backgroundColor: 'rgba(255, 107, 53, 0.75)',
                                borderRadius: 4,
                                yAxisID: 'y',
                            },
                            {
                                label: 'Sales count',
                                data: chartData.counts,
                                type: 'line',
                                borderColor: '#8b5cf6',
                                backgroundColor: 'transparent',
                                borderWidth: 2,
                                pointRadius: 3,
                                yAxisID: 'y1',
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { mode: 'index', intersect: false },
                        plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, padding: 14 } } },
                        scales: {
                            y: { beginAtZero: true, grid: { color: '#f3f4f6' }, ticks: { callback: v => v >= 1000 ? (v/1000)+'k' : v } },
                            y1: { beginAtZero: true, position: 'right', grid: { display: false } },
                            x: { grid: { display: false } },
                        },
                    },
                });
            });
        </script>
    @endpush
@endif
