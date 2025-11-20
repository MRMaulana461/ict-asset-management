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

    // ===== DAMAGE BY DEPARTMENT CHART (Horizontal Bar) =====
    const deptIdCtx = document.getElementById('dept_idChart');
    console.log('🏢 Department chart element:', deptIdCtx);
    console.log('📋 Department chart data:', chartData.dept_id);
    
    if (deptIdCtx && chartData.dept_id && chartData.dept_id.labels && chartData.dept_id.labels.length > 0) {
        console.log('✅ Creating department chart with data:', chartData.dept_id);
        
        // Pastikan data tidak null/undefined
        const labels = chartData.dept_id.labels || [];
        const data = chartData.dept_id.data || [];
        
        new Chart(deptIdCtx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Damage Reports',
                    data: data,
                    backgroundColor: 'rgba(139, 92, 246, 0.8)', // Purple
                    borderColor: 'rgba(139, 92, 246, 1)',
                    borderWidth: 1,
                    borderRadius: 6,
                }]
            },
            options: {
                indexAxis: 'y', // Horizontal bar
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        cornerRadius: 8,
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                return `${context.parsed.x} reports`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            stepSize: 1,
                            font: {
                                size: 11
                            }
                        },
                        title: {
                            display: true,
                            text: 'Number of Reports'
                        }
                    },
                    y: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 11
                            }
                        },
                        title: {
                            display: true,
                            text: 'Department'
                        }
                    }
                }
            }
        });
        console.log('✅ Department chart initialized');
    } else {
        console.log('⚠️ Department chart skipped - no data or canvas not found');
        console.log('Available chart data keys:', Object.keys(chartData));
        if (chartData.dept_id) {
            console.log('Dept ID data structure:', {
                labels: chartData.dept_id.labels,
                data: chartData.dept_id.data,
                labelsLength: chartData.dept_id.labels?.length,
                dataLength: chartData.dept_id.data?.length
            });
        }
    }

    // ===== MONTHLY TREND CHART (Line) =====
    const trendCtx = document.getElementById('trendChart');
    console.log('📈 Trend chart element:', trendCtx);
    
    if (trendCtx && chartData.trend && chartData.trend.labels && chartData.trend.labels.length > 0) {
        console.log('✅ Creating trend chart with data:', chartData.trend);
        
        const trendLabels = chartData.trend.labels || [];
        const trendData = chartData.trend.data || [];
        
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: trendLabels,
                datasets: [{
                    label: 'Damage Reports',
                    data: trendData,
                    borderColor: 'rgba(239, 68, 68, 1)', // Red
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 6,
                    pointHoverRadius: 8,
                    pointBackgroundColor: 'rgba(239, 68, 68, 1)',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: 'rgba(239, 68, 68, 1)',
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
                        grid: { color: 'rgba(0, 0, 0, 0.05)' },
                        title: {
                            display: true,
                            text: 'Number of Reports'
                        }
                    },
                    x: {
                        ticks: { font: { size: 12 } },
                        grid: { display: false },
                        title: {
                            display: true,
                            text: 'Month'
                        }
                    }
                }
            }
        });
        console.log('✅ Trend chart initialized');
    } else {
        console.log('⚠️ Trend chart skipped - no data or canvas not found');
        if (chartData.trend) {
            console.log('Trend data structure:', {
                labels: chartData.trend.labels,
                data: chartData.trend.data,
                labelsLength: chartData.trend.labels?.length,
                dataLength: chartData.trend.data?.length
            });
        }
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