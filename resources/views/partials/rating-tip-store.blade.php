@php
    $tipDismissed = auth()->check() ? auth()->user()->observationTipDismissed() : true;
@endphp
Alpine.store('ratingTip', {
    dismissed: @json($tipDismissed),
    open: false,
    dontShowAgain: @json($tipDismissed),
    init() {
        this.dontShowAgain = this.dismissed;
        if (!this.dismissed) {
            try {
                if (sessionStorage.getItem('rating_tip_seen') === '1') return;
                sessionStorage.setItem('rating_tip_seen', '1');
            } catch (e) { /* storage unavailable */ }
            this.open = true;
        }
    },
    openModal() {
        this.dontShowAgain = this.dismissed;
        this.open = true;
    },
    closeAndSave() {
        const dismiss = this.dontShowAgain;
        this.open = false;
        if (dismiss === this.dismissed) return;
        this.dismissed = dismiss;
        this.persist(dismiss);
    },
    persist(dismiss) {
        fetch('{{ route('preferences.observation-tip-dismiss') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify({ dismissed: dismiss })
        }).catch(function () {});
    }
});