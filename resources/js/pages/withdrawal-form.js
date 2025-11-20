import { EmployeeValidator } from '../modules/employee-validator.js';

export function initWithdrawalForm() {
    console.log('🚀 Withdrawal Form initializing...');

    // ===== EMPLOYEE VALIDATOR =====
    const employeeValidator = new EmployeeValidator({
        employeeIdField: document.getElementById('ghrs_id'),
        employeeNameField: document.getElementById('employee_name'),
        employeeDeptField: document.getElementById('employee_dept'),
        employeeErrorField: document.getElementById('employeeError')
    });
    employeeValidator.init();

    // ===== SIMPLE FORM VALIDATION =====
    // Enable/disable submit button based on form validity
    const checkFormValidity = () => {
        const employeeId = document.getElementById('ghrs_id')?.value.trim();
        const employeeName = document.getElementById('employee_name')?.value.trim();
        const assetType = document.getElementById('asset_type_id')?.value;
        const quantity = parseInt(document.getElementById('quantity')?.value) || 0;
        const reason = document.getElementById('reason')?.value.trim();
        const submitBtn = document.getElementById('submitBtn');
        
        // Validation rules
        const isValid = employeeId.length > 0 && 
                       employeeName.length > 0 && 
                       assetType && assetType.length > 0 && 
                       quantity > 0 && 
                       reason.length >= 1;
        
        // Update button state
        if (isValid) {
            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            console.log('✅ Form valid - Submit enabled');
        } else {
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
            console.log('❌ Form invalid - Submit disabled', {
                employeeId: !!employeeId,
                employeeName: !!employeeName,
                assetType: !!assetType,
                quantity: quantity > 0,
                reason: reason.length >= 1
            });
        }
    };

    // ===== ATTACH EVENT LISTENERS =====
    const fields = [
        'ghrs_id', 
        'employee_name', 
        'asset_type_id', 
        'quantity', 
        'reason'
    ];
    
    fields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
            field.addEventListener('input', checkFormValidity);
            field.addEventListener('change', checkFormValidity);
            console.log(`✅ Event listener attached to: ${fieldId}`);
        } else {
            console.warn(`⚠️ Field not found: ${fieldId}`);
        }
    });

    // ===== INITIAL VALIDATION CHECK =====
    // Check form validity after page load (for Laravel old() values)
    setTimeout(() => {
        checkFormValidity();
        console.log('🔍 Initial validation check completed');
    }, 500);

    // ===== FORM SUBMIT HANDLER =====
    const form = document.getElementById('manualWithdrawalForm');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            console.log('📤 Form submitting to Laravel...');
            console.log('Form action:', this.action);
            console.log('Form method:', this.method);
            
            // Get all form data for debugging
            const formData = new FormData(this);
            console.log('Form data:', Object.fromEntries(formData));
            
            // Disable button to prevent double submission
            const submitBtn = document.getElementById('submitBtn');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Submitting...';
                submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
            }
        });
        
        console.log('✅ Form submit handler attached');
    } else {
        console.error('❌ Form element not found!');
    }

    console.log('✅ Withdrawal Form initialized successfully');
}