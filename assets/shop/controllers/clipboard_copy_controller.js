import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['label', 'source'];

    disconnect() {
        clearTimeout(this.resetLabelTimeout);
    }

    async copy() {
        if (!this.hasSourceTarget || !this.hasLabelTarget) {
            return;
        }

        const text = this.sourceTarget.value;
        if (!text) {
            return;
        }

        try {
            await navigator.clipboard.writeText(text);
        } catch (error) {
            console.error('Failed to copy to clipboard.', error);

            return;
        }

        const { clipboardDefaultLabel, clipboardSuccessLabel } = this.labelTarget.dataset;

        this.labelTarget.textContent = clipboardSuccessLabel;

        clearTimeout(this.resetLabelTimeout);
        this.resetLabelTimeout = setTimeout(() => {
            this.labelTarget.textContent = clipboardDefaultLabel;
        }, 2000);
    }
}
