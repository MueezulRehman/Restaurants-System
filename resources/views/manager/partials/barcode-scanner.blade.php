<button type="button" data-barcode-scan-target="{{ $inputId }}"
    class="inline-flex items-center gap-2 rounded-lg border border-hut-green/30 bg-hut-green/10 px-3 py-2 text-sm font-semibold text-hut-dark hover:bg-hut-green/20"
    aria-label="Scan barcode with camera" title="Scan barcode with camera">
    <i class="fas fa-camera" aria-hidden="true"></i>
    <span>Camera scan</span>
</button>

<div data-barcode-scanner-modal="{{ $inputId }}"
    class="fixed inset-0 z-[70] hidden items-center justify-center bg-black/70 p-4" role="dialog" aria-modal="true"
    aria-label="Scan product barcode">
    <div class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl">
        <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3">
            <h2 class="font-display font-semibold text-hut-dark">Scan product barcode</h2>
            <button type="button" data-barcode-scan-close
                class="rounded-lg px-3 py-1 text-xl leading-none text-gray-500 hover:bg-gray-100"
                aria-label="Close scanner">
                &times;
            </button>
        </div>
        <div class="bg-black p-3">
            <video data-barcode-scan-video class="aspect-video w-full rounded-lg object-cover" playsinline
                muted></video>
        </div>
        <p data-barcode-scan-status class="px-4 py-3 text-sm text-gray-600">Allow camera access, then hold the barcode
            inside the frame.</p>
    </div>
</div>