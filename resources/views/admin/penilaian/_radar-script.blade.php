<script>
(function initRadarChart() {
    function drawChart() {
        const labels = @json($radarData['labels']);
        const scores = @json($radarData['scores']);
        if (!labels || labels.length < 3) return;

        const canvas = document.getElementById('radarChart');
        if (!canvas) return;
        const ctx = canvas.getContext('2d');

        // Destroy existing chart instance jika ada (mencegah duplikasi)
        if (canvas._chartInstance) {
            canvas._chartInstance.destroy();
        }

        canvas._chartInstance = new Chart(ctx, {
            type: 'radar',
            data: {
                labels,
                datasets: [{
                    label: 'Skor Rata-rata',
                    data: scores,
                    backgroundColor: 'rgba(59,130,246,.12)',
                    borderColor: '#3b82f6',
                    borderWidth: 2.5,
                    pointBackgroundColor: '#3b82f6',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: '#3b82f6',
                    pointRadius: 5,
                    pointHoverRadius: 7,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                animation: { duration: 1200, easing: 'easeInOutQuart' },
                scales: {
                    r: {
                        min: 0, max: 5,
                        ticks: {
                            stepSize: 1,
                            backdropColor: 'transparent',
                            color: '#94a3b8',
                            font: { size: 10, weight: '600' }
                        },
                        grid:        { color: '#e2e8f0' },
                        angleLines:  { color: '#e2e8f0' },
                        pointLabels: {
                            font: { size: 11, weight: '700' },
                            color: '#1e293b'
                        },
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        titleColor: '#94a3b8',
                        bodyColor: '#f1f5f9',
                        borderColor: '#1e293b',
                        borderWidth: 1,
                        padding: 12,
                        callbacks: { label: ctx => ` ${ctx.parsed.r} / 5 bintang` }
                    }
                }
            }
        });
    }

    // Tunggu Chart.js selesai load
    if (typeof Chart !== 'undefined') {
        drawChart();
    } else {
        // Chart.js belum load — load manual lalu draw
        const s = document.createElement('script');
        s.src = 'https://cdn.jsdelivr.net/npm/chart.js';
        s.onload = drawChart;
        document.head.appendChild(s);
    }
})();
</script>