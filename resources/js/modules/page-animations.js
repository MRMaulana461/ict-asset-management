export class PageAnimations {
    constructor() {
        this.initialized = false;
    }

    /**
     * Initialize all animations on page
     */
    init() {
        if (this.initialized) {
            console.warn('⚠️ PageAnimations already initialized');
            return;
        }

        console.log('🎬 Initializing Page Animations...');
        
        this.animateStatCards();
        this.animateTableRows();
        this.animateChartCards();
        this.setupFilterAutoFocus();
        
        this.initialized = true;
        console.log('✅ Page Animations initialized');
    }

    /**
     * Animate statistics cards on page load
     */
    animateStatCards() {
        const statCards = document.querySelectorAll('.grid .bg-white, .grid .bg-blue-50, .grid .bg-green-50, .grid .bg-red-50, .grid .bg-gray-50, .grid .bg-orange-50, .grid .bg-purple-50, .grid .bg-yellow-50');
        
        if (statCards.length === 0) {
            console.log('ℹ️ No stat cards found to animate');
            return;
        }

        statCards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            setTimeout(() => {
                card.style.transition = 'all 0.5s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });

        console.log(`✅ Animated ${statCards.length} stat cards`);
    }

    /**
     * Animate table rows on scroll
     */
    animateTableRows() {
        const tableRows = document.querySelectorAll('tbody tr');
        
        if (tableRows.length === 0) {
            console.log('ℹ️ No table rows found to animate');
            return;
        }

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

        tableRows.forEach(row => {
            row.style.opacity = '0';
            row.style.transform = 'translateY(20px)';
            row.style.transition = 'all 0.4s ease';
            observer.observe(row);
        });

        console.log(`✅ Set up animation for ${tableRows.length} table rows`);
    }

    /**
     * Animate chart cards (fade in)
     */
    animateChartCards() {
        const chartCards = document.querySelectorAll('.bg-white canvas');
        
        if (chartCards.length === 0) {
            console.log('ℹ️ No chart cards found to animate');
            return;
        }

        chartCards.forEach((canvas, index) => {
            const card = canvas.closest('.bg-white');
            if (card) {
                card.style.opacity = '0';
                card.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    card.style.transition = 'all 0.6s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'scale(1)';
                }, 300 + (index * 150));
            }
        });

        console.log(`✅ Animated ${chartCards.length} chart cards`);
    }

    /**
     * Auto-focus search input if empty
     */
    setupFilterAutoFocus() {
        const searchInput = document.querySelector('input[name="search"]');
        
        if (!searchInput) {
            console.log('ℹ️ No search input found');
            return;
        }

        // Only focus if empty and no active filters
        if (!searchInput.value && !window.location.search) {
            setTimeout(() => searchInput.focus(), 500);
            console.log('✅ Auto-focused search input');
        }
    }

    /**
     * Reset all animations (for SPA-like behavior)
     */
    reset() {
        this.initialized = false;
        console.log('🔄 Page animations reset');
    }
}

// Export singleton instance
export const pageAnimations = new PageAnimations();