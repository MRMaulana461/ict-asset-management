import { pageAnimations } from '../modules/page-animations.js';

export function initWithdrawalIndex() {
    console.log('🚀 Withdrawal Index initializing...');
    
    // Use universal animations
    pageAnimations.init();
    
    console.log('✅ Withdrawal Index initialized successfully');
}