export class AssetInfoDisplay {
    constructor(selectId, infoContainerId) {
        this.select = document.getElementById(selectId);
        this.infoContainer = document.getElementById(infoContainerId);
        this.fields = {
            tag: this.infoContainer?.querySelector('#infoTag'),
            type: this.infoContainer?.querySelector('#infoType'),
            brand: this.infoContainer?.querySelector('#infoBrand'),
            model: this.infoContainer?.querySelector('#infoModel')
        };
    }

    update() {
        if (!this.select || !this.infoContainer) return;

        const selectedOption = this.select.options[this.select.selectedIndex];
        
        if (selectedOption.value) {
            const data = {
                tag: selectedOption.dataset.tag || '-',
                type: selectedOption.dataset.type || '-',
                brand: selectedOption.dataset.brand || '-',
                model: selectedOption.dataset.model || '-'
            };

            Object.keys(this.fields).forEach(key => {
                if (this.fields[key]) {
                    this.fields[key].textContent = data[key];
                }
            });

            this.infoContainer.classList.remove('hidden');
        } else {
            this.infoContainer.classList.add('hidden');
        }
    }

    init(onChange) {
        if (!this.select) return;

        this.select.addEventListener('change', () => {
            this.update();
            if (onChange) onChange();
        });

        // Initial update
        if (this.select.value) {
            this.update();
        }
    }
}
