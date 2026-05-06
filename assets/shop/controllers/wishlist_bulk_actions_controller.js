import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    initialize() {
        this.onContainerChange = this.onContainerChange.bind(this);
        this.onSubmit = this.onSubmit.bind(this);
    }

    connect() {
        this.container = this.element.closest('.card-body') || document;
        this.submitButton = this.element.querySelector('button[type="submit"]');

        this.container.addEventListener('change', this.onContainerChange);
        this.element.addEventListener('submit', this.onSubmit);
        this.updateSubmitButton();
    }

    disconnect() {
        this.container.removeEventListener('change', this.onContainerChange);
        this.element.removeEventListener('submit', this.onSubmit);
    }

    onContainerChange(event) {
        if (event.target.matches('[data-js-bulk-checkboxes]')) {
            this.toggleAll(event.target.checked);
        }

        this.updateSubmitButton();
    }

    onSubmit() {
        this.removeExistingIdInputs();

        this.selectedCheckboxes.forEach((checkbox) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = checkbox.value;
            input.dataset.wishlistBulkActionsInput = 'true';

            this.element.appendChild(input);
        });
    }

    toggleAll(checked) {
        this.rowCheckboxes.forEach((checkbox) => {
            checkbox.checked = checked;
        });
    }

    updateSubmitButton() {
        if (this.submitButton === null) {
            return;
        }

        this.submitButton.disabled = this.selectedCheckboxes.length === 0;
    }

    removeExistingIdInputs() {
        this.element
            .querySelectorAll('[data-wishlist-bulk-actions-input="true"]')
            .forEach((input) => input.remove());
    }

    get rowCheckboxes() {
        return Array.from(
            this.container.querySelectorAll('.form-check-input[type="checkbox"]:not([data-js-bulk-checkboxes])'),
        );
    }

    get selectedCheckboxes() {
        return this.rowCheckboxes.filter((checkbox) => checkbox.checked);
    }
}
