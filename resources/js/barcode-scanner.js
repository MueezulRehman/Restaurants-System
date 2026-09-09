function initializeBarcodeScanners() {
    document.querySelectorAll('[data-barcode-scan-target]').forEach((button) => {
        if (button.dataset.barcodeScannerReady === '1') return;
        button.dataset.barcodeScannerReady = '1';

        const target = document.getElementById(button.dataset.barcodeScanTarget);
        const modal = document.querySelector(`[data-barcode-scanner-modal="${button.dataset.barcodeScanTarget}"]`);
        const video = modal?.querySelector('[data-barcode-scan-video]');
        const status = modal?.querySelector('[data-barcode-scan-status]');
        const closeButton = modal?.querySelector('[data-barcode-scan-close]');
        let controls = null;

        if (!target || !modal || !video || !status || !closeButton) return;

        const close = () => {
            controls?.stop();
            controls = null;
            video.srcObject?.getTracks().forEach((track) => track.stop());
            video.srcObject = null;
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        };

        closeButton.addEventListener('click', close);
        modal.addEventListener('click', (event) => {
            if (event.target === modal) close();
        });

        button.addEventListener('click', async () => {
            if (!window.isSecureContext || !navigator.mediaDevices?.getUserMedia) {
                status.textContent = 'Camera scanning requires HTTPS (or localhost). You can still type or use a hardware scanner.';
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                return;
            }

            modal.classList.remove('hidden');
            modal.classList.add('flex');
            status.textContent = 'Starting camera...';

            try {
                const { BrowserMultiFormatReader } = await import('@zxing/browser');
                const reader = new BrowserMultiFormatReader();
                controls = await reader.decodeFromConstraints(
                    { video: { facingMode: { ideal: 'environment' } } },
                    video,
                    (result, error) => {
                        if (!result) {
                            if (error && error.name !== 'NotFoundException') {
                                status.textContent = 'Keep the barcode visible and steady.';
                            }
                            return;
                        }

                        target.value = result.getText().trim();
                        target.dispatchEvent(new Event('input', { bubbles: true }));
                        target.dispatchEvent(new Event('change', { bubbles: true }));
                        target.focus();
                        close();
                    },
                );
                status.textContent = 'Camera ready. Hold the barcode inside the frame.';
            } catch (error) {
                status.textContent = 'Unable to open the camera. Check permission, HTTPS, and browser support, or enter the barcode manually.';
            }
        });
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeBarcodeScanners);
} else {
    initializeBarcodeScanners();
}