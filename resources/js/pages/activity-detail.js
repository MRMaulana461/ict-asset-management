import { pageAnimations } from '../modules/page-animations.js';

export function initActivitiesDetail() {
    console.log('🚀 Activities Detail initializing...');
    
    // Use universal animations
    pageAnimations.init();
    
    // Setup modal functionality
    setupActivityModal();
    
    // Setup tooltip interactions
    setupTooltips();
    
    // Animate timeline nodes
    animateTimelineNodes();
    
    console.log('✅ Activities Detail initialized successfully');
}

/**
 * Setup activity modal functionality
 */
function setupActivityModal() {
    const colorMap = {
        loan: { bg: 'bg-blue-500', ring: 'ring-blue-200' },
        return: { bg: 'bg-emerald-500', ring: 'ring-emerald-200' },
        broken: { bg: 'bg-rose-500', ring: 'ring-rose-200' },
        withdrawal: { bg: 'bg-violet-500', ring: 'ring-violet-200' }
    };

    // Expose modal functions globally
    window.openActivityModal = function(activity) {
        const modal = document.getElementById('activityModal');
        const modalIcon = document.getElementById('modalIcon');
        const modalIconSvg = document.getElementById('modalIconSvg');
        const modalTitle = document.getElementById('modalTitle');
        const modalDescription = document.getElementById('modalDescription');
        const modalDetails = document.getElementById('modalDetails');
        const modalDate = document.getElementById('modalDate');
        
        if (!modal) return;
        
        // Set color
        const colors = colorMap[activity.type];
        modalIcon.className = `w-16 h-16 ${colors.bg} rounded-full flex items-center justify-center ring-4 ${colors.ring} shadow-lg`;
        
        // Set icon
        modalIconSvg.setAttribute('data-lucide', activity.icon);
        
        // Set content
        modalTitle.textContent = activity.title;
        modalDescription.textContent = activity.description;
        modalDetails.textContent = activity.details;
        modalDate.textContent = activity.date;
        
        // Show modal
        modal.classList.remove('hidden');
        
        // Re-initialize lucide icons
        if (window.lucide) {
            lucide.createIcons();
        }
    };

    window.closeActivityModal = function() {
        const modal = document.getElementById('activityModal');
        if (modal) {
            modal.classList.add('hidden');
        }
    };

    // Close modal on escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            window.closeActivityModal();
        }
    });

    // Close modal on backdrop click
    const modal = document.getElementById('activityModal');
    if (modal) {
        modal.addEventListener('click', (e) => {
            if (e.target.id === 'activityModal') {
                window.closeActivityModal();
            }
        });
    }
    
    console.log('✅ Activity modal setup complete');
}

/**
 * Setup tooltip show/hide on hover
 */
function setupTooltips() {
    const nodes = document.querySelectorAll('.activity-node');
    
    if (nodes.length === 0) {
        console.log('ℹ️ No activity nodes found');
        return;
    }
    
    nodes.forEach(node => {
        const tooltip = node.querySelector('[role="tooltip"]');
        
        if (!tooltip) return;
        
        node.addEventListener('mouseenter', () => {
            tooltip.classList.remove('invisible', 'opacity-0');
            tooltip.classList.add('opacity-100');
        });
        
        node.addEventListener('mouseleave', () => {
            tooltip.classList.add('invisible', 'opacity-0');
            tooltip.classList.remove('opacity-100');
        });
    });
    
    console.log(`✅ Tooltips set up for ${nodes.length} nodes`);
}

/**
 * Animate timeline nodes on scroll
 */
function animateTimelineNodes() {
    const rows = document.querySelectorAll('.timeline-row');
    
    if (rows.length === 0) {
        console.log('ℹ️ No timeline rows found');
        return;
    }
    
    const observerOptions = {
        threshold: 0.2,
        rootMargin: '0px 0px -100px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const nodes = entry.target.querySelectorAll('.activity-node');
                
                nodes.forEach((node, index) => {
                    setTimeout(() => {
                        node.style.opacity = '1';
                        node.style.transform = 'scale(1)';
                    }, index * 100);
                });
                
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    // Set initial state and observe
    rows.forEach(row => {
        const nodes = row.querySelectorAll('.activity-node');
        nodes.forEach(node => {
            node.style.opacity = '0';
            node.style.transform = 'scale(0.8)';
            node.style.transition = 'all 0.4s ease';
        });
        
        observer.observe(row);
    });
    
    console.log(`✅ Animation set up for ${rows.length} timeline rows`);
}