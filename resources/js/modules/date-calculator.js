import { formatDate } from '../utils/helpers.js';

export class DateCalculator {
    /**
     * Add business days (skip weekends)
     */
    static addBusinessDays(startDate, days) {
        const date = new Date(startDate);
        let count = 0;
        
        while (count < days) {
            date.setDate(date.getDate() + 1);
            // Skip Saturday (6) and Sunday (0)
            if (date.getDay() !== 0 && date.getDay() !== 6) {
                count++;
            }
        }
        
        return date;
    }

    /**
     * Calculate return date from today
     */
    static calculateReturnDate(durationDays, locale = 'en-US') {
        if (!durationDays || durationDays < 1) {
            return 'Please enter duration';
        }

        const returnDate = this.addBusinessDays(new Date(), durationDays);
        return formatDate(returnDate, locale);
    }

    /**
     * Setup auto-update for return date field
     */
    static setupReturnDateField(durationFieldId, returnFieldId, config = {}) {
        const durationField = document.getElementById(durationFieldId);
        const returnField = document.getElementById(returnFieldId);
        
        if (!durationField || !returnField) return;

        const maxDays = config.maxDays || 7;
        const minDays = config.minDays || 1;
        const locale = config.locale || 'en-US';
        const isTextContent = config.isTextContent || false;
        const invalidMessage = config.invalidMessage || `Please enter duration (${minDays}-${maxDays} days)`;

        const updateDate = () => {
            const duration = parseInt(durationField.value) || 0;
            
            if (duration >= minDays && duration <= maxDays) {
                const dateText = this.calculateReturnDate(duration, locale);
                if (isTextContent) {
                    returnField.textContent = dateText;
                } else {
                    returnField.value = dateText;
                }
            } else {
                if (isTextContent) {
                    returnField.textContent = invalidMessage;
                } else {
                    returnField.value = invalidMessage;
                }
            }
        };

        durationField.addEventListener('input', updateDate);
        durationField.addEventListener('change', updateDate);
        durationField.addEventListener('blur', updateDate);

        // Initial update
        updateDate();
    }
}