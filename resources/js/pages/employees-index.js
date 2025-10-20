import { pageAnimations } from '../modules/page-animations.js';

export function initEmployeesIndex() {
    console.log('🚀 Employees Index initializing...');
    
    // Use universal animations
    pageAnimations.init();
    
    console.log('✅ Employees Index initialized successfully');
}