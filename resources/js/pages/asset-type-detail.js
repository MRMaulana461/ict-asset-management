import { pageAnimations } from '../modules/page-animations.js';

export function initAssetTypeDetail(chartData) {
    console.log('🚀 Asset Type Detail initializing...');
    console.log('📊 Chart data received:', chartData);

    // Check if Chart.js is loaded
    if (typeof Chart === 'undefined') {
        console.error('❌ Chart.js is not loaded!');
        return;
    }

    // Use universal animations
    pageAnimations.init();

    // ===== STATUS DISTRIBUTION CHART =====
    const ctx = document.getElementById('statusChart');
    console.log('🎯 Status chart element:', ctx);
    
    if (ctx) {
        console.log('✅ Creating status chart with data:', chartData);
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: chartData.labels,
                datasets: [{
                    data: chartData.data,
                    backgroundColor: chartData.colors,
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
                            font: { size: 12 }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
        console.log('✅ Status chart initialized');
    }

    console.log('✅ Asset Type Detail initialized successfully');
}