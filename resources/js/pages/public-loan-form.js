import { EmployeeValidator } from '../modules/employee-validator.js';
import { DateCalculator } from '../modules/date-calculator.js';
import { StockValidator } from '../modules/stock-validator.js';

export function initPublicLoanForm() {
    console.log('🚀 Public Loan Form initializing...');

    let isEmployeeValid = false;
    let isStockValid = false;

    // ===== EMPLOYEE VALIDATOR =====
    const employeeValidator = new EmployeeValidator({
        apiEndpoint: '/api/employee',
        employeeIdField: document.getElementById('ghrs_id'),
        employeeNameField: document.getElementById('employee_name'),
        employeeErrorField: document.getElementById('employeeError'),
        onValidationChange: (valid) => {
            isEmployeeValid = valid;
            checkFormValidity();
        }
    });
    employeeValidator.init();

    // ===== STOCK VALIDATOR =====
    const stockValidator = new StockValidator({
        assetTypeSelectId: 'asset_type_id',
        quantityFieldId: 'quantity',
        stockInfoId: 'stockInfo',
        quantityErrorId: 'quantityError',
        onValidationChange: (valid) => {
            console.log('📦 Stock validation changed:', valid);
            isStockValid = valid;
            checkFormValidity();
        }
    });
    stockValidator.init();
    console.log('📦 Stock validator initialized:', stockValidator);

    // ===== DATE CALCULATOR =====
    const updateReturnDate = () => {
        DateCalculator.setupReturnDateField('duration_days', 'expectedReturn', {
            maxDays: 5,
            isTextContent: true,
            invalidMessage: 'Please enter duration (1-5 days)'
        });
    };

    const durationField = document.getElementById('duration_days');
    if (durationField) {
        durationField.addEventListener('input', updateReturnDate);
        updateReturnDate();
    }

    // ===== SUCCESS MESSAGE WITH CALCULATED RETURN DATE =====
    const successAlert = document.querySelector('.alert-success');
    if (successAlert) {
        const loanQuantity = successAlert.dataset.loanQuantity;
        const loanDuration = successAlert.dataset.loanDuration;
        
        if (loanQuantity && loanDuration) {
            const returnDateText = DateCalculator.calculateReturnDate(
                parseInt(loanDuration), 
                'en-US'
            );
            
            successAlert.innerHTML = `
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-green-500 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <p class="font-semibold">Loan request submitted successfully!</p>
                        <p class="text-sm mt-1">${loanQuantity} item(s) borrowed. Please return by <strong>${returnDateText}</strong> (${loanDuration} business days)</p>
                    </div>
                </div>
            `;
            console.log('✅ Success message updated with calculated return date:', returnDateText);
        }
    }

    // ===== FORM VALIDATION =====
    const checkFormValidity = () => {
        const employeeId = document.getElementById('ghrs_id')?.value.trim();
        const employeeName = document.getElementById('employee_name')?.value.trim();
        const assetType = document.getElementById('asset_type_id')?.value;
        const quantity = parseInt(document.getElementById('quantity')?.value) || 0;
        const duration = parseInt(document.getElementById('duration_days')?.value) || 0;
        const purpose = document.getElementById('purpose')?.value.trim();
        const submitBtn = document.getElementById('submitBtn');
        
        console.log('🔍 Form Validation Check:', {
            employeeId: employeeId.length >= 3,
            employeeName: employeeName.length > 0,
            isEmployeeValid,
            assetType: !!assetType,
            quantity: quantity > 0,
            isStockValid,
            duration: duration > 0 && duration <= 7,
            purposeLength: purpose.length,
            purposeValid: purpose.length >= 1
        });
        
        // All conditions must be met
        const isValid = 
            employeeId.length >= 3 && 
            employeeName.length > 0 && 
            isEmployeeValid &&
            assetType.length > 0 && 
            quantity > 0 && 
            isStockValid &&
            duration > 0 && 
            duration <= 7 &&
            purpose.length >= 1; 
        
        if (submitBtn) {
            submitBtn.disabled = !isValid;
            
            if (isValid) {
                submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                submitBtn.classList.add('hover:bg-opacity-90');
                console.log('✅ Form valid - Submit enabled');
            } else {
                submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
                submitBtn.classList.remove('hover:bg-opacity-90');
                console.log('❌ Form invalid - Submit disabled', {
                    employeeId: employeeId.length >= 3,
                    employeeName: employeeName.length > 0,
                    isEmployeeValid,
                    assetType: !!assetType,
                    quantity: quantity > 0,
                    isStockValid,
                    duration: duration > 0 && duration <= 7,
                    purpose: purpose.length >= 1
                });
            }
        }
    };

    // ===== ATTACH EVENT LISTENERS =====
    const fields = ['ghrs_id', 'employee_name', 'asset_type_id', 'quantity', 'duration_days', 'purpose'];
    
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
    setTimeout(() => {
        checkFormValidity();
        console.log('🔍 Initial validation check completed');
    }, 500);

    // ===== FORM SUBMIT HANDLER =====
    const form = document.getElementById('loanForm');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            console.log('📤 Form submitting...');
            console.log('Form action:', this.action);
            console.log('Form method:', this.method);
            
            const formData = new FormData(this);
            const formDataObj = Object.fromEntries(formData);
            console.log('Form data:', formDataObj);
            
            // Validate before submit
            if (!isEmployeeValid) {
                e.preventDefault();
                alert('Please enter a valid Employee ID');
                console.error('❌ Submit blocked: Invalid employee');
                return false;
            }
            
            if (!isStockValid) {
                e.preventDefault();
                alert('Please check stock availability');
                console.error('❌ Submit blocked: Invalid stock');
                return false;
            }
            
            const submitBtn = document.getElementById('submitBtn');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Submitting...';
                submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
            }
            
            console.log('✅ Form validation passed, submitting to server...');
        });
        
        console.log('✅ Form submit handler attached');
    } else {
        console.error('❌ Form element #loanForm not found!');
    }

    console.log('✅ Public Loan Form initialized successfully');
}