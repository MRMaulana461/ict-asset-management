/**
 * Dashboard Panel Animations & Interactions
 * Handles all animations and interactive elements for the dashboard page
 */

export class DashboardPanel {
    constructor() {
        this.initialized = false;
        this.animationDelay = 100; // Base delay for staggered animations
    }

    /**
     * Initialize dashboard panel
     */
    init() {
        if (this.initialized) {
            console.warn('⚠️ DashboardPanel already initialized');
            return;
        }

        console.log('🎯 Initializing Dashboard Panel...');
        
        this.animateAlerts();
        this.animateStatusCards();
        this.animateAssetTypeCards();
        this.animateBorrowedItems();
        this.animateDamagedItems();
        this.animateProgressBars();
        this.setupInteractiveElements();
        
        this.initialized = true;
        console.log('✅ Dashboard Panel initialized');
    }

    /**
     * Animate alert banners (low stock warning)
     */
    animateAlerts() {
        const alerts = document.querySelectorAll('.bg-yellow-50.border-l-4, .bg-red-50.border-l-4, .bg-blue-50.border-l-4');
        
        if (alerts.length === 0) {
            console.log('ℹ️ No alerts to animate');
            return;
        }

        alerts.forEach((alert, index) => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateX(-20px)';
            
            setTimeout(() => {
                alert.style.transition = 'all 0.6s cubic-bezier(0.34, 1.56, 0.64, 1)';
                alert.style.opacity = '1';
                alert.style.transform = 'translateX(0)';
            }, index * 200);
        });

        console.log(`✅ Animated ${alerts.length} alert(s)`);
    }

    /**
     * Animate status breakdown cards (In Stock, In Use, Broken, etc.)
     */
    animateStatusCards() {
        const statusCards = document.querySelectorAll('.grid-cols-5 > div');
        
        if (statusCards.length === 0) {
            console.log('ℹ️ No status cards found');
            return;
        }

        statusCards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px) scale(0.9)';
            
            setTimeout(() => {
                card.style.transition = 'all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1)';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0) scale(1)';
                
                // Animate the number inside
                const number = card.querySelector('.text-2xl');
                if (number) {
                    this.animateNumber(number);
                }
            }, 300 + (index * this.animationDelay));
        });

        console.log(`✅ Animated ${statusCards.length} status cards`);
    }

    /**
     * Animate asset type cards with progress bars
     */
    animateAssetTypeCards() {
        const assetCards = document.querySelectorAll('.space-y-3 > .group');
        
        if (assetCards.length === 0) {
            console.log('ℹ️ No asset type cards found');
            return;
        }

        const observerOptions = {
            threshold: 0.2,
            rootMargin: '0px 0px -100px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    const index = Array.from(assetCards).indexOf(entry.target);
                    
                    setTimeout(() => {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateX(0)';
                        
                        // Trigger progress bar animation
                        const progressBar = entry.target.querySelector('.bg-saipem-primary');
                        if (progressBar) {
                            const targetWidth = progressBar.style.width;
                            progressBar.style.width = '0%';
                            setTimeout(() => {
                                progressBar.style.transition = 'width 1s cubic-bezier(0.34, 1.56, 0.64, 1)';
                                progressBar.style.width = targetWidth;
                            }, 100);
                        }
                    }, index * 100);
                    
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        assetCards.forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateX(-30px)';
            card.style.transition = 'all 0.5s ease';
            observer.observe(card);
        });

        console.log(`✅ Set up animation for ${assetCards.length} asset cards`);
    }

    /**
     * Animate most borrowed items list
     */
    animateBorrowedItems() {
        const borrowedContainer = document.querySelector('.text-lg.font-semibold + div');
        const borrowedItems = document.querySelectorAll('.bg-purple-50 .space-y-3 > div, .bg-gray-50.hover\\:bg-purple-50');
        
        if (borrowedItems.length === 0) {
            console.log('ℹ️ No borrowed items found');
            return;
        }

        borrowedItems.forEach((item, index) => {
            item.style.opacity = '0';
            item.style.transform = 'translateX(20px)';
            
            setTimeout(() => {
                item.style.transition = 'all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1)';
                item.style.opacity = '1';
                item.style.transform = 'translateX(0)';
                
                // Animate the badge
                const badge = item.querySelector('.bg-saipem-accent');
                if (badge) {
                    badge.style.animation = 'pulse 2s infinite';
                }
            }, 400 + (index * 150));
        });

        console.log(`✅ Animated ${borrowedItems.length} borrowed items`);
    }

    /**
     * Animate most damaged items list
     */
    animateDamagedItems() {
        const damagedItems = document.querySelectorAll('.bg-red-50.hover\\:bg-red-100');
        
        if (damagedItems.length === 0) {
            console.log('ℹ️ No damaged items found');
            return;
        }

        const observerOptions = {
            threshold: 0.3,
            rootMargin: '0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    const index = Array.from(damagedItems).indexOf(entry.target);
                    
                    setTimeout(() => {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateX(0)';
                    }, index * 120);
                    
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        damagedItems.forEach(item => {
            item.style.opacity = '0';
            item.style.transform = 'translateX(-20px)';
            item.style.transition = 'all 0.4s ease';
            observer.observe(item);
        });

        console.log(`✅ Set up animation for ${damagedItems.length} damaged items`);
    }

    /**
     * Animate all progress bars with smooth width transition
     */
    animateProgressBars() {
        const progressBars = document.querySelectorAll('.bg-gray-200.rounded-full .bg-saipem-primary');
        
        if (progressBars.length === 0) {
            console.log('ℹ️ No progress bars found');
            return;
        }

        // Will be triggered by IntersectionObserver in animateAssetTypeCards
        console.log(`✅ Found ${progressBars.length} progress bars`);
    }

    /**
     * Setup interactive elements (hover effects, clicks)
     */
    setupInteractiveElements() {
        // Add smooth hover scale to cards
        const interactiveCards = document.querySelectorAll('.group, .hover\\:bg-gray-50, .hover\\:bg-purple-50, .hover\\:bg-red-100');
        
        interactiveCards.forEach(card => {
            card.addEventListener('mouseenter', () => {
                card.style.transition = 'all 0.3s ease';
            });
        });

        // Add click ripple effect to buttons
        const buttons = document.querySelectorAll('a.bg-white, a.bg-saipem-primary');
        
        buttons.forEach(button => {
            button.addEventListener('click', (e) => {
                this.createRipple(e, button);
            });
        });

        console.log('✅ Interactive elements configured');
    }

    /**
     * Create ripple effect on button click
     */
    createRipple(event, element) {
        const ripple = document.createElement('span');
        const rect = element.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const x = event.clientX - rect.left - size / 2;
        const y = event.clientY - rect.top - size / 2;

        ripple.style.width = ripple.style.height = size + 'px';
        ripple.style.left = x + 'px';
        ripple.style.top = y + 'px';
        ripple.style.position = 'absolute';
        ripple.style.borderRadius = '50%';
        ripple.style.backgroundColor = 'rgba(255, 255, 255, 0.6)';
        ripple.style.transform = 'scale(0)';
        ripple.style.animation = 'ripple 0.6s ease-out';
        ripple.style.pointerEvents = 'none';

        element.style.position = 'relative';
        element.style.overflow = 'hidden';
        element.appendChild(ripple);

        setTimeout(() => ripple.remove(), 600);
    }

    /**
     * Animate number counting up
     */
    animateNumber(element) {
        const target = parseInt(element.textContent);
        if (isNaN(target)) return;

        const duration = 1000;
        const steps = 30;
        const increment = target / steps;
        let current = 0;
        let step = 0;

        const timer = setInterval(() => {
            step++;
            current += increment;
            
            if (step >= steps) {
                current = target;
                clearInterval(timer);
            }
            
            element.textContent = Math.floor(current);
        }, duration / steps);
    }

    /**
     * Reset all animations
     */
    reset() {
        this.initialized = false;
        console.log('🔄 Dashboard panel reset');
    }
}

// Add CSS animation for ripple effect
const style = document.createElement('style');
style.textContent = `
    @keyframes ripple {
        to {
            transform: scale(4);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// Create singleton instance
const dashboardPanel = new DashboardPanel();

// Export init function for app.js
export function initDashboardPanel() {
    console.log('📊 Initializing Dashboard Panel...');
    dashboardPanel.init();
}

// Also export the instance for direct access if needed
export { dashboardPanel };