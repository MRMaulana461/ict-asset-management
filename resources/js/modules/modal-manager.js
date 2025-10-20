export class ModalManager {
    static open(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('hidden');
            // Trigger lucide icons if available
            if (window.lucide) {
                setTimeout(() => window.lucide.createIcons(), 100);
            }
        }
    }

    static close(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('hidden');
        }
    }

    static setupClickOutside(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            document.addEventListener('click', (e) => {
                if (e.target === modal) {
                    this.close(modalId);
                }
            });
        }
    }
}