export class StockValidator {
    constructor(config) {
        this.assetTypeSelect = document.getElementById(config.assetTypeSelectId);
        this.quantityField = document.getElementById(config.quantityFieldId);
        this.stockInfo = document.getElementById(config.stockInfoId);
        this.quantityError = document.getElementById(config.quantityErrorId);
        this.availableStock = 0;
        this.isValid = false;
        this.onChange = config.onChange;
    }

    checkStock() {
        if (!this.assetTypeSelect) return { isValid: false, stock: 0 };

        const selectedOption = this.assetTypeSelect.options[this.assetTypeSelect.selectedIndex];
        this.availableStock = parseInt(selectedOption.getAttribute('data-stock')) || 0;

        if (!selectedOption.value) {
            this.stockInfo?.classList.add('hidden');
            this.isValid = false;
            this.quantityField?.removeAttribute('max');
            return { isValid: false, stock: 0 };
        }

        // Update stock info display
        if (this.stockInfo) {
            if (this.availableStock === 0) {
                this.stockInfo.textContent = '⚠️ Out of Stock';
                this.stockInfo.className = 'mt-1 text-sm text-red-600';
                this.isValid = false;
                this.quantityField?.removeAttribute('max');
            } else {
                this.stockInfo.textContent = `✓ Available: ${this.availableStock} unit(s)`;
                this.stockInfo.className = 'mt-1 text-sm text-green-600';
                this.quantityField?.setAttribute('max', this.availableStock);
                
                // Auto-adjust if quantity exceeds stock
                const currentQty = parseInt(this.quantityField?.value) || 1;
                if (this.quantityField && currentQty > this.availableStock) {
                    this.quantityField.value = this.availableStock;
                }
                
                this.isValid = true;
            }
            this.stockInfo.classList.remove('hidden');
        }

        this.validateQuantity();
        
        if (this.onChange) {
            this.onChange(this.isValid, this.availableStock);
        }

        return { isValid: this.isValid, stock: this.availableStock };
    }

    validateQuantity() {
        if (!this.quantityField || !this.quantityError) return true;

        const quantity = parseInt(this.quantityField.value) || 0;
        const assetSelected = this.assetTypeSelect?.value;

        if (!assetSelected) {
            this.quantityError.classList.add('hidden');
            this.isValid = false;
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
            this.quantityError.classList.add('hidden');
            this.isValid = true;
            return true;
        }

        this.quantityError.textContent = errorMsg;
        this.quantityError.classList.remove('hidden');
        
        if (this.onChange) {
            this.onChange(this.isValid, this.availableStock);
        }

        return false;
    }

    init() {
        if (this.assetTypeSelect) {
            this.assetTypeSelect.addEventListener('change', () => this.checkStock());
        }

        if (this.quantityField) {
            this.quantityField.addEventListener('input', () => this.validateQuantity());
        }
    }
}