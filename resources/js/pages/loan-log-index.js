import { pageAnimations } from '../modules/page-animations.js';

export function initLoanLogIndex() {
    console.log('🚀 Loan Log Index initializing...');
    
    // Use universal animations
    pageAnimations.init();
    
    // Initialize return modal handlers
    setupReturnModal();
    
    // Handle alert close buttons
    setupAlertHandlers();
    
    console.log('✅ Loan Log Index initialized successfully');
}

/**
 * Setup return confirmation modal
 */
function setupReturnModal() {
    const modal = document.getElementById('returnModal');
    
    if (!modal) {
        console.log('ℹ️ Return modal not found');
        return;
    }
    
    // Close modal when clicking outside
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            closeReturnModal();
        }
    });
    
    // Close on ESC key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeReturnModal();
        }
    });
    
    console.log('✅ Return modal handlers set up');
}

/**
 * Setup alert close handlers
 */
function setupAlertHandlers() {
    const closeButtons = document.querySelectorAll('.close-alert');
    
    closeButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const alertId = this.getAttribute('data-alert-id');
            const alertElement = document.getElementById(alertId);
            
            if (alertElement) {
                alertElement.style.transition = 'opacity 0.3s ease';
                alertElement.style.opacity = '0';
                
                setTimeout(() => {
                    alertElement.remove();
                    console.log(`✖️ Alert closed: ${alertId}`);
                }, 300);
            }
        });
    });
    
    console.log(`✅ Alert handlers set up (${closeButtons.length} buttons)`);
}

/**
 * Global functions for modal (called from blade)
 */
window.confirmReturn = function(loanId, borrowerName) {
    document.getElementById('borrowerName').textContent = borrowerName;
    document.getElementById('returnForm').action = `/loan-log/${loanId}/return`;
    document.getElementById('returnModal').classList.remove('hidden');
    console.log(`📋 Return modal opened for loan #${loanId}`);
};

window.closeReturnModal = function() {
    document.getElementById('returnModal').classList.add('hidden');
    console.log('✖️ Return modal closed');
};