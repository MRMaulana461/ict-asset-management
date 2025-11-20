import './bootstrap';
import Alpine from 'alpinejs';

// Initialize with fallback functions
const fallbackFn = () => console.warn('Function not loaded');

let initAdminLoanForm = fallbackFn;
let initPublicLoanForm = fallbackFn;
let initWithdrawalForm = fallbackFn;
let initAssetIndex = fallbackFn;
let initEmployeesIndex = fallbackFn;
let initDamagesDetail = fallbackFn;
let initAssetTypeDetail = fallbackFn;
let initLoanLogIndex = fallbackFn;
let initWithdrawalIndex = fallbackFn;
let initActivitiesDetail = fallbackFn;
let initDashboardPanel = fallbackFn;
let initLoanForm = fallbackFn;  // ✅ Ganti nama

// Load all modules
Promise.all([
    import('./pages/admin-loan-form.js').then(module => {
        initAdminLoanForm = module.initAdminLoanForm || fallbackFn;
        console.log('✅ Admin Loan Form module loaded');
    }).catch(error => {
        console.error('❌ Failed to load admin-loan-form:', error);
    }),
    
    import('./pages/public-loan-form.js').then(module => {
        initPublicLoanForm = module.initPublicLoanForm || fallbackFn;
        console.log('✅ Public Loan Form module loaded');
    }).catch(error => {
        console.error('❌ Failed to load public-loan-form:', error);
    }),
    
    import('./pages/withdrawal-form.js').then(module => {
        initWithdrawalForm = module.initWithdrawalForm || fallbackFn;
        console.log('✅ Withdrawal Form module loaded');
    }).catch(error => {
        console.error('❌ Failed to load withdrawal-form:', error);
    }),
    
    import('./pages/asset-index.js').then(module => {
        initAssetIndex = module.initAssetIndex || fallbackFn;
        console.log('✅ Asset Index module loaded');
    }).catch(error => {
        console.error('❌ Failed to load asset-index:', error);
    }),

    import('./pages/employees-index.js').then(module => {
        initEmployeesIndex = module.initEmployeesIndex || fallbackFn;
        console.log('✅ Employees Index module loaded');
    }).catch(error => {
        console.error('❌ Failed to load employees-index:', error);
    }),

    import('./pages/damages-detail.js').then(module => {
        initDamagesDetail = module.initDamagesDetail || fallbackFn;
        console.log('✅ Damages Detail module loaded');
    }).catch(error => {
        console.error('❌ Failed to load damages-detail:', error);
    }),

    import('./pages/asset-type-detail.js').then(module => {
        initAssetTypeDetail = module.initAssetTypeDetail || fallbackFn;
        console.log('✅ Asset Type Detail module loaded');
    }).catch(error => {
        console.error('❌ Failed to load asset-type-detail:', error);
    }),
    
    import('./pages/loan-log-index.js').then(module => {
        initLoanLogIndex = module.initLoanLogIndex || fallbackFn;
        console.log('✅ Loan Log module loaded');
    }).catch(error => {
        console.error('❌ Failed to load Loan Log:', error);
    }),
    
    import('./pages/withdrawal-index.js').then(module => {
        initWithdrawalIndex = module.initWithdrawalIndex || fallbackFn;
        console.log('✅ Withdrawal Index module loaded');
    }).catch(error => {
        console.error('❌ Failed to load Withdrawal Index:', error);
    }),
    
    import('./pages/activity-detail.js').then(module => {
        initActivitiesDetail = module.initActivitiesDetail || fallbackFn;
        console.log('✅ Activity Detail module loaded');
    }).catch(error => {
        console.error('❌ Failed to load Activity Detail:', error);
    }),
    
    import('./pages/dashboard-panel.js').then(module => {
        initDashboardPanel = module.initDashboardPanel || fallbackFn;
        console.log('✅ Dashboard Panel module loaded');
    }).catch(error => {
        console.error('❌ Failed to load Dashboard Panel:', error);
    }),
    
    import('./pages/loan-form.js').then(module => {
        initLoanForm = module.initLoanForm || fallbackFn;
        console.log('✅ Loan Form module loaded');
    }).catch(error => {
        console.error('❌ Failed to load Loan Form:', error);
    }),
]).then(() => {
    window.Alpine = Alpine;
    Alpine.start();

    window.ICTAssetApp = {
        initAdminLoanForm,
        initPublicLoanForm,
        initWithdrawalForm,
        initAssetIndex,
        initEmployeesIndex,
        initDamagesDetail,
        initAssetTypeDetail, 
        initWithdrawalIndex,
        initLoanLogIndex, 
        initActivitiesDetail,
        initDashboardPanel,
        initLoanForm  
    };

    console.log('🚀 ICT Asset Management App loaded successfully');
    console.log('📦 Available functions:', Object.keys(window.ICTAssetApp));
    
    // Dispatch custom event when app ready
    window.dispatchEvent(new Event('ICTAssetAppReady'));
});