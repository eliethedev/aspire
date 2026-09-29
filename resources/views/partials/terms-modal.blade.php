{{--
    Reusable Terms & Conditions modal (vanilla JS, no Alpine dependency).

    Usage:
        @include('partials.terms-modal', ['agreeCheckboxId' => 'terms'])

    - Open programmatically:  openTermsModal()
    - Close:                  closeTermsModal()
    - Optional 'agreeCheckboxId': shows an "I have read and agree" button that
      ticks the given checkbox and closes the modal.
--}}
<div id="terms-modal" class="hidden fixed inset-0 z-[100]" role="dialog" aria-modal="true" aria-labelledby="terms-modal-title">
    {{-- Backdrop --}}
    <div id="terms-modal-backdrop" class="absolute inset-0 bg-gray-900/60 dark:bg-black/70 backdrop-blur-[2px] opacity-0 transition-opacity duration-200"></div>

    {{-- Centering wrapper (clicking the padded area also closes) --}}
    <div id="terms-modal-wrapper" class="absolute inset-0 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 sm:p-6">
            {{-- Panel --}}
            <div id="terms-modal-panel"
                 class="relative w-full max-w-2xl bg-white dark:bg-gray-900 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden scale-95 opacity-0 transition-all duration-200">
                {{-- Header --}}
                <div class="flex items-center gap-3 px-5 sm:px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50/80 dark:bg-gray-800/60">
                    <div class="w-9 h-9 rounded-xl bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <h3 id="terms-modal-title" class="font-bold text-gray-900 dark:text-white leading-tight">Terms &amp; Conditions of Use</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">ASPIRE · DepEd Division of Sagay City</p>
                    </div>
                    <button type="button" onclick="closeTermsModal()" aria-label="Close terms and conditions"
                            class="ml-auto w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Scrollable body --}}
                <div class="px-5 sm:px-6 py-5 max-h-[60vh] overflow-y-auto">
                    @include('partials.terms-and-conditions')
                </div>

                {{-- Footer --}}
                <div class="flex flex-wrap items-center justify-end gap-2 px-5 sm:px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50/80 dark:bg-gray-800/60">
                    <button type="button" onclick="closeTermsModal()"
                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        Close
                    </button>
                    @isset($agreeCheckboxId)
                    <button type="button" onclick="agreeTermsModal('{{ $agreeCheckboxId }}')"
                            class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-colors">
                        I have read and agree
                    </button>
                    @endisset
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    if (window.__termsModalInit) return;
    window.__termsModalInit = true;

    var modal, backdrop, panel, lastFocused;

    function els() {
        modal = document.getElementById('terms-modal');
        backdrop = document.getElementById('terms-modal-backdrop');
        panel = document.getElementById('terms-modal-panel');
    }

    window.openTermsModal = function () {
        els();
        if (!modal) return;
        lastFocused = document.activeElement;
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        requestAnimationFrame(function () {
            requestAnimationFrame(function () {
                backdrop.classList.remove('opacity-0');
                panel.classList.remove('scale-95', 'opacity-0');
            });
        });
        var closeBtn = modal.querySelector('button[aria-label]');
        if (closeBtn) closeBtn.focus();
    };

    window.closeTermsModal = function () {
        els();
        if (!modal || modal.classList.contains('hidden')) return;
        backdrop.classList.add('opacity-0');
        panel.classList.add('scale-95', 'opacity-0');
        setTimeout(function () {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
            if (lastFocused && lastFocused.focus) lastFocused.focus();
        }, 200);
    };

    window.agreeTermsModal = function (checkboxId) {
        var box = document.getElementById(checkboxId);
        if (box) {
            box.checked = true;
            box.dispatchEvent(new Event('change', { bubbles: true }));
        }
        closeTermsModal();
    };

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeTermsModal();
    });

    document.addEventListener('DOMContentLoaded', function () {
        els();
        if (!modal) return;
        document.getElementById('terms-modal-backdrop').addEventListener('click', closeTermsModal);
        document.getElementById('terms-modal-wrapper').addEventListener('click', function (e) {
            if (e.target === this) closeTermsModal();
        });
    });
})();
</script>
