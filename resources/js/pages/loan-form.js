import { EmployeeValidator } from '../modules/employee-validator.js';

export function initLoanForm() {  // ✅ Ganti dari initManualLoanForm
    console.log('🚀 Loan Form initializing...');

    // ===== EMPLOYEE VALIDATOR =====
    const employeeValidator = new EmployeeValidator({
        employeeIdField: document.getElementById('ghrs_id'),
        employeeNameField: document.getElementById('employee_name'),
        employeeDeptField: document.getElementById('employee_dept'),
        employeeErrorField: document.getElementById('employeeError')
    });
    employeeValidator.init();

    // ===== FORM VALIDATION =====
    const checkFormValidity = () => {
        const loanDate = document.getElementById('loan_date')?.value;
        const loanTime = document.getElementById('loan_time')?.value;
        const ghrsId = document.getElementById('ghrs_id')?.value?.trim() ?? '';
        const employeeName = document.getElementById('employee_name')?.value?.trim() ?? '';
        const assetTypeId = document.getElementById('asset_type_id')?.value;  // ✅ UBAH
        const quantity = parseInt(document.getElementById('quantity')?.value) || 0;
        const durationDays = parseInt(document.getElementById('duration_days')?.value) || 0;
        const purpose = document.getElementById('purpose')?.value?.trim() ?? '';
        const status = document.getElementById('status')?.value;
        const submitBtn = document.getElementById('submitBtn');
        
        if (!submitBtn) return;

        // Base validation
        let isValid = loanDate && 
                    loanTime &&
                    ghrsId.length > 0 && 
                    employeeName.length > 0 && 
                    assetTypeId &&  // ✅ UBAH
                    quantity > 0 && 
                    durationDays > 0 && 
                    purpose.length >= 1;

        // Additional validation if status is "Returned"
        if (status === 'Returned') {
            const returnDate = document.getElementById('return_date')?.value;
            const returnTime = document.getElementById('return_time')?.value;
            isValid = isValid && returnDate && returnTime;
        }
        
        // Update button state
        if (isValid) {
            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            console.log('✅ Form valid - Submit enabled');
        } else {
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
            console.log('❌ Form invalid - Submit disabled');
        }
    };

    // ===== ATTACH EVENT LISTENERS =====
    const fields = [
        'loan_date',
        'loan_time',
        'ghrs_id',
        'employee_name',
        'asset_type_id',  // ✅ UBAH dari asset_id
        'quantity',
        'duration_days',
        'purpose',
        'status',
        'return_date',
        'return_time'
    ];

    fields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
            field.addEventListener('input', checkFormValidity);
            field.addEventListener('change', checkFormValidity);
            console.log(`✅ Event listener attached to: ${fieldId}`);
        }
    });

    // ===== INITIAL VALIDATION CHECK =====
    setTimeout(() => {
        checkFormValidity();
        console.log('🔍 Initial validation check completed');
    }, 500);

    // ===== FORM SUBMIT HANDLER =====
    const form = document.getElementById('loanForm');  // ✅ Ganti ID
    
    if (form) {
        form.addEventListener('submit', function(e) {
            console.log('📤 Form submitting...');
            
            const submitBtn = document.getElementById('submitBtn');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Saving...';
                submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
            }
        });
        
        console.log('✅ Form submit handler attached');
    } else {
        console.error('❌ Form element not found!');
    }

    console.log('✅ Loan Form initialized successfully');
}