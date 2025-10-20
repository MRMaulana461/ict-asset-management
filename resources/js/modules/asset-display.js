/**
 * Asset Display Manager
 * Handles displaying hardware and peripheral assets with different views
 * Location: resources/js/pages/asset-display.js
 */

export class AssetDisplayManager {
    constructor() {
        this.assetsData = [];
        this.currentTab = 'hardware';
        
        // DOM Elements
        this.tabHardware = null;
        this.tabPeripheral = null;
        this.hardwareTable = null;
        this.peripheralTable = null;
        this.peripheralTbody = null;
        this.peripheralEmpty = null;
        this.hardwareEmpty = null;
        this.hardwareTbody = null;
        
        // Status color mapping
        this.statusColors = {
            'In Stock': 'bg-blue-100 text-blue-800',
            'In Use': 'bg-green-100 text-green-800',
            'Broken': 'bg-red-100 text-red-800',
            'Retired': 'bg-gray-100 text-gray-800',
            'Taken': 'bg-orange-100 text-orange-800'
        };
    }

    /**
     * Initialize the asset display manager
     */
    init() {
        this.initElements();
        this.loadAssetsData();
        this.attachEventListeners();
        this.checkHardwareEmpty();
    }

    /**
     * Initialize DOM elements
     */
    initElements() {
        this.tabHardware = document.getElementById('tab-hardware');
        this.tabPeripheral = document.getElementById('tab-peripheral');
        this.hardwareTable = document.getElementById('hardware-table');
        this.peripheralTable = document.getElementById('peripheral-table');
        this.peripheralTbody = document.getElementById('peripheral-tbody');
        this.peripheralEmpty = document.getElementById('peripheral-empty');
        this.hardwareEmpty = document.getElementById('hardware-empty');
        this.hardwareTbody = document.getElementById('hardware-tbody');
    }

    /**
     * Load assets data from JSON script tag
     */
    loadAssetsData() {
        const dataElement = document.getElementById('assets-data');
        if (dataElement) {
            try {
                this.assetsData = JSON.parse(dataElement.textContent);
            } catch (error) {
                console.error('Error parsing assets data:', error);
                this.assetsData = [];
            }
        }
    }

    /**
     * Attach event listeners
     */
    attachEventListeners() {
        if (this.tabHardware) {
            this.tabHardware.addEventListener('click', () => this.switchTab('hardware'));
        }
        
        if (this.tabPeripheral) {
            this.tabPeripheral.addEventListener('click', () => this.switchTab('peripheral'));
        }
    }

    /**
     * Switch between hardware and peripheral tabs
     * @param {string} tab - 'hardware' or 'peripheral'
     */
    switchTab(tab) {
        this.currentTab = tab;

        if (tab === 'hardware') {
            this.activateTab(this.tabHardware, this.tabPeripheral);
            this.showTable(this.hardwareTable, this.peripheralTable);
            this.checkHardwareEmpty();
        } else {
            this.activateTab(this.tabPeripheral, this.tabHardware);
            this.showTable(this.peripheralTable, this.hardwareTable);
            this.renderPeripheralTable();
        }
    }

    /**
     * Activate a tab and deactivate another
     * @param {HTMLElement} activeTab 
     * @param {HTMLElement} inactiveTab 
     */
    activateTab(activeTab, inactiveTab) {
        if (activeTab) {
            activeTab.classList.add('border-saipem-primary', 'text-saipem-primary');
            activeTab.classList.remove('border-transparent', 'text-gray-500');
        }
        
        if (inactiveTab) {
            inactiveTab.classList.remove('border-saipem-primary', 'text-saipem-primary');
            inactiveTab.classList.add('border-transparent', 'text-gray-500');
        }
    }

    /**
     * Show one table and hide another
     * @param {HTMLElement} showTable 
     * @param {HTMLElement} hideTable 
     */
    showTable(showTable, hideTable) {
        if (showTable) showTable.classList.remove('hidden');
        if (hideTable) hideTable.classList.add('hidden');
    }

    /**
     * Group peripheral assets by type
     * @returns {Array} Grouped peripheral data
     */
    groupPeripherals() {
        const peripherals = this.assetsData.filter(a => a.category === 'peripheral');
        const grouped = {};

        peripherals.forEach(asset => {
            if (!grouped[asset.type_id]) {
                grouped[asset.type_id] = {
                    type_name: asset.type_name,
                    type_id: asset.type_id,
                    notes: asset.notes,
                    total: 0,
                    in_stock: 0,
                    in_use: 0,
                    broken: 0,
                    other: 0,
                    items: []
                };
            }

            const qty = asset.quantity || 1;
            grouped[asset.type_id].total += qty;
            grouped[asset.type_id].items.push(asset);

            switch (asset.status) {
                case 'In Stock':
                    grouped[asset.type_id].in_stock += qty;
                    break;
                case 'In Use':
                    grouped[asset.type_id].in_use += qty;
                    break;
                case 'Broken':
                    grouped[asset.type_id].broken += qty;
                    break;
                default:
                    grouped[asset.type_id].other += qty;
            }
        });

        return Object.values(grouped);
    }

    /**
     * Render peripheral table with grouped data
     */
    renderPeripheralTable() {
        if (!this.peripheralTbody) return;

        const grouped = this.groupPeripherals();
        
        if (grouped.length === 0) {
            if (this.peripheralEmpty) {
                this.peripheralEmpty.classList.remove('hidden');
            }
            this.peripheralTbody.innerHTML = '';
            return;
        }

        if (this.peripheralEmpty) {
            this.peripheralEmpty.classList.add('hidden');
        }

        this.peripheralTbody.innerHTML = grouped.map(item => this.createPeripheralRow(item)).join('');
        
        // Attach event listeners for view items buttons
        this.attachViewItemsListeners();
    }

    /**
     * Attach event listeners to view items buttons
     */
    attachViewItemsListeners() {
        const buttons = this.peripheralTbody.querySelectorAll('[data-view-items]');
        buttons.forEach(button => {
            button.addEventListener('click', (e) => {
                const typeId = parseInt(e.target.dataset.viewItems);
                this.showPeripheralDetails(typeId);
            });
        });
    }

    /**
     * Create HTML for a peripheral row
     * @param {Object} item - Grouped peripheral item
     * @returns {string} HTML string
     */
    createPeripheralRow(item) {
        return `
            <tr class="hover:bg-purple-50">
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">${this.escapeHtml(item.type_name)}</div>
                </td>
                <td class="px-6 py-4">
                    <div class="text-sm text-gray-500">${this.escapeHtml(item.notes) || '-'}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-bold text-purple-600">${item.total}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    ${this.createStatusBadge('In Stock', item.in_stock)}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    ${this.createStatusBadge('In Use', item.in_use)}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    ${this.createStatusBadge('Broken', item.broken)}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                        ${item.other}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <button data-view-items="${item.type_id}" class="text-blue-600 hover:text-blue-900">
                        View Items
                    </button>
                </td>
            </tr>
        `;
    }

    /**
     * Create status badge HTML
     * @param {string} status 
     * @param {number} count 
     * @returns {string} HTML string
     */
    createStatusBadge(status, count) {
        const colorClass = this.statusColors[status] || 'bg-gray-100 text-gray-800';
        return `
            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${colorClass}">
                ${count}
            </span>
        `;
    }

    /**
     * Check if hardware table is empty and show/hide empty message
     */
    checkHardwareEmpty() {
        if (!this.hardwareTbody || !this.hardwareEmpty) return;

        const rows = this.hardwareTbody.querySelectorAll('tr');
        if (rows.length === 0) {
            this.hardwareEmpty.classList.remove('hidden');
        } else {
            this.hardwareEmpty.classList.add('hidden');
        }
    }

    /**
     * Show peripheral item details in modal
     * @param {number} typeId 
     */
    showPeripheralDetails(typeId) {
        const items = this.assetsData.filter(a => 
            a.category === 'peripheral' && a.type_id === typeId
        );

        if (items.length === 0) {
            alert('No items found');
            return;
        }

        // Create modal content
        const details = items.map(item => {
            const owner = item.assigned_to ? 
                `${item.assigned_to.name} (${item.assigned_to.employee_id})` : 
                'Not Assigned';
            
            return `Asset: ${item.asset_tag}
Status: ${item.status}
Quantity: ${item.quantity || 1}
Owner: ${owner}
Notes: ${item.notes || '-'}`;
        }).join('\n\n---\n\n');

        alert(`Peripheral Items Details:\n\n${details}`);
        
        // TODO: Replace with proper modal implementation
    }

    /**
     * Escape HTML to prevent XSS
     * @param {string} text 
     * @returns {string}
     */
    escapeHtml(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }
}