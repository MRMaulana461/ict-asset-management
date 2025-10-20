import { pageAnimations } from '../modules/page-animations.js';

export function initDamagesDetail(chartData) {
    console.log('🚀 Damages Detail initializing...');
    console.log('📊 Chart data received:', chartData);

    // Check if Chart.js is loaded
    if (typeof Chart === 'undefined') {
        console.error('❌ Chart.js is not loaded!');
        return;
    }

    // Use universal animations
    pageAnimations.init();

    // ===== TOP REPORTERS CHART =====
    const reportersCtx = document.getElementById('reportersChart');
    console.log('🎯 Reporters chart element:', reportersCtx);
    
    if (reportersCtx && chartData.reporters.labels.length > 0) {
        console.log('✅ Creating reporters chart with data:', chartData.reporters);
        new Chart(reportersCtx, {
            type: 'doughnut',
            data: {
                labels: chartData.reporters.labels,
                datasets: [{
                    data: chartData.reporters.data,
                    backgroundColor: [
                        '#8B5CF6', '#EC4899', '#F59E0B', '#10B981', '#3B82F6',
                        '#EF4444', '#6366F1', '#14B8A6', '#F97316', '#84CC16'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            font: { size: 11 },
                            generateLabels: function(chart) {
                                const data = chart.data;
                                if (data.labels.length && data.datasets.length) {
                                    return data.labels.map((label, i) => {
                                        const value = data.datasets[0].data[i];
                                        return {
                                            text: `${label}: ${value}`,
                                            fillStyle: data.datasets[0].backgroundColor[i],
                                            hidden: false,
                                            index: i
                                        };
                                    });
                                }
                                return [];
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((value / total) * 100).toFixed(1);
                                return `${label}: ${value} reports (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
        console.log('✅ Reporters chart initialized');
    }

    // ===== MONTHLY TREND CHART =====
    const trendCtx = document.getElementById('trendChart');
    console.log('📈 Trend chart element:', trendCtx);
    
    if (trendCtx) {
        console.log('✅ Creating trend chart with data:', chartData.trend);
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: chartData.trend.labels,
                datasets: [{
                    label: 'Damage Reports',
                    data: chartData.trend.data,
                    borderColor: '#8B5CF6',
                    backgroundColor: 'rgba(139, 92, 246, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 6,
                    pointHoverRadius: 8,
                    pointBackgroundColor: '#8B5CF6',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: '#8B5CF6',
                    pointHoverBorderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        cornerRadius: 8,
                        titleFont: { size: 14, weight: 'bold' },
                        bodyFont: { size: 13 },
                        callbacks: {
                            label: function(context) {
                                return `Reports: ${context.parsed.y}`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            font: { size: 12 }
                        },
                        grid: { color: 'rgba(0, 0, 0, 0.05)' }
                    },
                    x: {
                        ticks: { font: { size: 12 } },
                        grid: { display: false }
                    }
                }
            }
        });
        console.log('✅ Trend chart initialized');
    }

    // ===== ANIMATE TABLE ROWS =====
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry, index) => {
            if (entry.isIntersecting) {
                setTimeout(() => {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }, index * 50);
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    const tableRows = document.querySelectorAll('tbody tr');
    tableRows.forEach(row => {
        row.style.opacity = '0';
        row.style.transform = 'translateY(20px)';
        row.style.transition = 'all 0.4s ease';
        observer.observe(row);
    });

    // ===== ANIMATE STAT CARDS =====
    const statCards = document.querySelectorAll('.grid .bg-white');
    statCards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        setTimeout(() => {
            card.style.transition = 'all 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });

    console.log('✅ Damages Detail initialized successfully');
}