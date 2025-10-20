import { EmployeeValidator } from '../modules/employee-validator.js';
import { DateCalculator } from '../modules/date-calculator.js';
import { AssetInfoDisplay } from '../modules/asset-info-display.js';

export function initAdminLoanForm() {
    console.log('🚀 Admin Loan Form initializing...');

    // ===== EMPLOYEE VALIDATOR =====
    // Auto-fill employee name dari API
    const employeeValidator = new EmployeeValidator({
        employeeIdField: document.getElementById('employee_id'),
        employeeNameField: document.getElementById('employee_name'),
        employeeErrorField: document.getElementById('employeeError'),
        borrowerIdField: document.getElementById('borrower_id')
    });
    employeeValidator.init();

    // ===== ASSET INFO DISPLAY =====
    // Display asset information (replaces StockValidator for admin)
    const assetInfo = new AssetInfoDisplay('asset_id', 'assetInfo');
    assetInfo.init();

    // ===== DATE CALCULATOR =====
    // Auto-calculate expected return date
    DateCalculator.setupReturnDateField('duration_days', 'expectedReturn', {
        maxDays: 7,
        locale: 'id-ID',
        isTextContent: true,
        invalidMessage: 'Please enter duration (1-5 days)'
    });

    // Trigger date calculation on duration change
    const durationField = document.getElementById('duration_days');
    durationField?.addEventListener('input', () => {
        DateCalculator.setupReturnDateField('duration_days', 'expectedReturn', {
            maxDays: 5,
            locale: 'id-ID',
            isTextContent: true,
            invalidMessage: 'Please enter duration (1-5 days)'
        });
    });

    // ===== SIMPLE FORM VALIDATION =====
    // Enable/disable submit button based on form validity
    const checkFormValidity = () => {
        const employeeId = document.getElementById('employee_id')?.value.trim();
        const borrowerId = document.getElementById('borrower_id')?.value.trim();
        const assetId = document.getElementById('asset_id')?.value;
        const quantity = parseInt(document.getElementById('quantity')?.value) || 0;
        const duration = parseInt(document.getElementById('duration_days')?.value) || 0;
        const purpose = document.getElementById('purpose')?.value.trim();
        const submitBtn = document.getElementById('submitBtn');
        
        // Validation rules
        const isValid = employeeId.length > 0 && 
                       borrowerId.length > 0 && 
                       assetId && assetId.length > 0 && 
                       quantity > 0 && 
                       duration >= 1 && 
                       purpose.length >= 1;
        
        // Update button state
        if (submitBtn) {
            if (isValid) {
                submitBtn.disabled = false;
                submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                console.log('✅ Form valid - Submit enabled');
            } else {
                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
                console.log('❌ Form invalid - Submit disabled', {
                    employeeId: !!employeeId,
                    borrowerId: !!borrowerId,
                    assetId: !!assetId,
                    quantity: quantity > 0,
                    duration: duration > 0 && duration <= 5,
                    purpose: purpose.length >= 1
                });
            }
        }
    };

    // ===== ATTACH EVENT LISTENERS =====
    const fields = [
        'employee_id',
        'borrower_id',
        'asset_id', 
        'quantity', 
        'duration_days', 
        'purpose'
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
    const form = document.getElementById('loanForm');
    
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

    console.log('✅ Admin Loan Form initialized successfully');
}