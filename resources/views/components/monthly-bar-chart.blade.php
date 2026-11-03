@props(['chartId', 'title', 'description', 'labels', 'values', 'axis' => 'Jumlah'])

<section class="mt-6 mb-4 rounded-lg border border-slate-200 bg-white p-4 shadow-sm" aria-labelledby="{{ $chartId }}-title">
    <div class="mb-3">
        <h2 id="{{ $chartId }}-title" class="text-sm font-semibold text-slate-800">{{ $title }}</h2>
        <p class="mt-1 text-xs text-slate-500">{{ $description }}</p>
    </div>
    <div class="relative h-56 sm:h-64">
        <canvas id="{{ $chartId }}" role="img" aria-label="{{ $title }}"></canvas>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const canvas = document.getElementById(@json($chartId));
    if (!canvas || typeof Chart === 'undefined') return;
    Chart.defaults.font.family = 'Inter, ui-sans-serif, system-ui, sans-serif';

    new Chart(canvas, {
        type: 'bar',
        data: {
            labels: @json($labels),
            datasets: [{
                label: @json($axis),
                data: @json($values),
                backgroundColor: '#2563eb',
                hoverBackgroundColor: '#1d4ed8',
                borderRadius: 5,
                maxBarThickness: 34,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#e2e8f0' } },
                x: { grid: { display: false } },
            },
        },
    });
});
</script>
