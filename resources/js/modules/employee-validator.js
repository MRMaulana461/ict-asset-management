import { debounce } from '../utils/helpers.js';

export class EmployeeValidator {
    constructor(config = {}) {
        this.apiEndpoint = config.apiEndpoint || '/api/employee';
        this.fields = {
            employeeId: config.employeeIdField,
            employeeName: config.employeeNameField,
            employeeDept: config.employeeDeptField,
            employeeError: config.employeeErrorField,
            borrowerId: config.borrowerIdField // For admin forms
        };
        this.onSuccess = config.onSuccess;
        this.onError = config.onError;
        this.onValidationChange = config.onValidationChange;
        this.isValid = false;
    }

    async validate(employeeId) {
        const { employeeName, employeeDept, employeeError, borrowerId } = this.fields;

        if (!employeeId) {
            this._clearFields();
            this.isValid = false;
            this._triggerChange();
            return null;
        }

        // Show loading state
        if (employeeName) employeeName.value = 'Loading...';
        if (employeeDept) employeeDept.value = 'Loading...';
        if (employeeError) employeeError.classList.add('hidden');

        try {
            const response = await fetch(`${this.apiEndpoint}/${encodeURIComponent(employeeId)}`);
            
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            
            const data = await response.json();

            if (data.success) {
                // Populate fields
                if (employeeName) employeeName.value = data.data.name;
                if (employeeDept) employeeDept.value = data.data.department || '-';
                if (borrowerId) borrowerId.value = data.data.id;
                if (employeeError) employeeError.classList.add('hidden');

                this.isValid = true;
                this._triggerChange();
                
                if (this.onSuccess) this.onSuccess(data.data);
                return data.data;
            } else {
                throw new Error(data.message || 'Employee not found');
            }
        } catch (error) {
            this._clearFields();
            
            if (employeeError) {
                employeeError.textContent = error.message || 'Failed to validate employee ID';
                employeeError.classList.remove('hidden');
            }

            this.isValid = false;
            this._triggerChange();
            
            if (this.onError) this.onError(error.message);
            return null;
        }
    }

    _clearFields() {
        const { employeeName, employeeDept, employeeError, borrowerId } = this.fields;
        if (employeeName) employeeName.value = '';
        if (employeeDept) employeeDept.value = '';
        if (borrowerId) borrowerId.value = '';
        if (employeeError) employeeError.classList.add('hidden');
    }

    _triggerChange() {
        if (this.onValidationChange) {
            this.onValidationChange(this.isValid);
        }
    }

    createDebouncedValidator() {
        return debounce(this.validate.bind(this), 500);
    }

    init() {
        const { employeeId } = this.fields;
        if (!employeeId) return;

        const debouncedValidate = this.createDebouncedValidator();

        employeeId.addEventListener('input', (e) => {
            debouncedValidate(e.target.value.trim());
        });

        employeeId.addEventListener('blur', (e) => {
            debouncedValidate(e.target.value.trim());
        });

        // Validate on page load if has value
        if (employeeId.value) {
            debouncedValidate(employeeId.value.trim());
        }
    }
}