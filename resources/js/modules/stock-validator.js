export class StockValidator {
    constructor(config = {}) {
        this.assetTypeSelect = document.getElementById(config.assetTypeSelectId);
        this.quantityField = document.getElementById(config.quantityFieldId);
        this.stockInfo = document.getElementById(config.stockInfoId);
        this.quantityError = document.getElementById(config.quantityErrorId);
        // Support both callback names
        this.onValidationChange = config.onValidationChange || config.onChange;
        this.availableStock = 0;
        this.isValid = false;
    }

    checkStock() {
        if (!this.assetTypeSelect) {
            console.warn('⚠️ Asset type select not found');
            return { isValid: false, stock: 0 };
        }

        const selectedOption = this.assetTypeSelect.options[this.assetTypeSelect.selectedIndex];
        this.availableStock = parseInt(selectedOption.getAttribute('data-stock')) || 0;

        console.log('📦 Checking stock:', {
            assetType: selectedOption.text,
            availableStock: this.availableStock,
            hasValue: !!selectedOption.value
        });

        if (!selectedOption.value) {
            this._hideMessages();
            this.isValid = false;
            this.quantityField?.removeAttribute('max');
            this._triggerChange();
            return { isValid: false, stock: 0 };
        }

        // Update stock info display
        if (this.stockInfo) {
            if (this.availableStock === 0) {
                this.stockInfo.textContent = '⚠️ Out of Stock';
                this.stockInfo.className = 'mt-1 text-sm text-red-600';
                this.stockInfo.classList.remove('hidden');
                this.isValid = false;
                this.quantityField?.removeAttribute('max');
            } else {
                this.stockInfo.textContent = `✓ Available: ${this.availableStock} unit(s)`;
                this.stockInfo.className = 'mt-1 text-sm text-green-600';
                this.stockInfo.classList.remove('hidden');
                this.quantityField?.setAttribute('max', this.availableStock);
                
                // Auto-adjust if quantity exceeds stock
                const currentQty = parseInt(this.quantityField?.value) || 1;
                if (this.quantityField && currentQty > this.availableStock) {
                    this.quantityField.value = this.availableStock;
                }
            }
        }

        // Validate quantity immediately after stock check
        this.validateQuantity();
        
        return { isValid: this.isValid, stock: this.availableStock };
    }

    validateQuantity() {
        if (!this.quantityField) {
            console.warn('⚠️ Quantity field not found');
            return false;
        }

        const quantity = parseInt(this.quantityField.value) || 0;
        const assetSelected = this.assetTypeSelect?.value;

        console.log('📦 Validating quantity:', {
            quantity,
            availableStock: this.availableStock,
            assetSelected: !!assetSelected
        });

        if (!assetSelected) {
            this._hideMessages();
            this.isValid = false;
            this._triggerChange();
            return false;
        }

        let errorMsg = '';
        
        if (this.availableStock === 0) {
            errorMsg = 'Item is out of stock';
            this.isValid = false;
        } else if (quantity < 1) {
            errorMsg = 'Quantity must be at least 1';
            this.isValid = false;
        } else if (quantity > this.availableStock) {
            errorMsg = `Maximum available: ${this.availableStock}`;
            this.isValid = false;
        } else {
            // Valid!
            this._hideQuantityError();
            this.isValid = true;
            this._triggerChange();
            return true;
        }

        // Show error
        this._showQuantityError(errorMsg);
        this._triggerChange();
        return false;
    }

    _showQuantityError(message) {
        if (this.quantityError) {
            this.quantityError.textContent = message;
            this.quantityError.classList.remove('hidden', 'text-green-600');
            this.quantityError.classList.add('text-red-600');
        }
    }

    _hideQuantityError() {
        if (this.quantityError) {
            this.quantityError.classList.add('hidden');
        }
    }

    _hideMessages() {
        if (this.stockInfo) {
            this.stockInfo.classList.add('hidden');
        }
        if (this.quantityError) {
            this.quantityError.classList.add('hidden');
        }
    }

    _triggerChange() {
        console.log('📦 Stock validation result:', this.isValid);
        if (this.onValidationChange) {
            this.onValidationChange(this.isValid);
        }
    }

    getIsValid() {
        return this.isValid;
    }

    getCurrentStock() {
        return this.availableStock;
    }

    init() {
        if (!this.assetTypeSelect || !this.quantityField) {
            console.error('❌ Stock validator: Required fields not found');
            return;
        }

        // Asset type change event
        this.assetTypeSelect.addEventListener('change', () => {
            console.log('🔄 Asset type changed');
            this.checkStock();
        });

        // Quantity input/change events
        this.quantityField.addEventListener('input', () => {
            console.log('🔄 Quantity input changed');
            this.validateQuantity();
        });

        this.quantityField.addEventListener('change', () => {
            console.log('🔄 Quantity changed');
            this.validateQuantity();
        });

        // Initial validation if fields have values
        if (this.assetTypeSelect.value && this.quantityField.value) {
            this.checkStock();
        }

        console.log('✅ Stock validator initialized');
    }
}