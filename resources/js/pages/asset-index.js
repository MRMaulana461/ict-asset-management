import { pageAnimations } from '../modules/page-animations.js';

export function initAssetIndex() {
    console.log('🚀 Asset Index initializing...');
    
    // Use universal animations
    pageAnimations.init();
    
    // Initialize export modal
    setupExportModal();
    
    console.log('✅ Asset Index initialized successfully');
}

/**
 * Setup export modal handlers
 */
function setupExportModal() {
    const modal = document.getElementById('exportModal');
    const openButtons = document.querySelectorAll('[data-action="open-export-modal"]');
    const closeButtons = document.querySelectorAll('[data-action="close-export-modal"]');
    
    if (!modal) {
        console.log('ℹ️ Export modal not found');
        return;
    }
    
    // Open modal
    openButtons.forEach(button => {
        button.addEventListener('click', () => {
            modal.classList.remove('hidden');
            console.log('📂 Export modal opened');
        });
    });
    
    // Close modal
    closeButtons.forEach(button => {
        button.addEventListener('click', () => {
            modal.classList.add('hidden');
            console.log('✖️ Export modal closed');
        });
    });
    
    // Close on outside click
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.classList.add('hidden');
            console.log('✖️ Export modal closed (outside click)');
        }
    });
    
    // Close on ESC key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
            modal.classList.add('hidden');
            console.log('✖️ Export modal closed (ESC key)');
        }
    });
    
    console.log('✅ Export modal handlers set up');
}