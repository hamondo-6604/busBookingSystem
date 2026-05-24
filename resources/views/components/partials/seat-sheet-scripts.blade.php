<script>
window.SeatSheet = {
    selectedSeats: new Map(),
    remainingAllowed: 5,
    currentStep: 'seats',
    originalSubmitText: '',

    init(root) {
        const el = root || document.querySelector('.seat-sheet-root');
        if (!el) return;
        this.selectedSeats.clear();
        this.remainingAllowed = parseInt(el.dataset.remaining || '5', 10);
        
        const continueBtn = document.getElementById('seat-sheet-continue');
        if (continueBtn) {
            this.originalSubmitText = continueBtn.textContent.trim();
        }
        
        this.currentStep = 'seats';
        this.setStep('seats');
        this.updateSummary();
        if (typeof lucide !== 'undefined') lucide.createIcons();
    },

    toggleSeat(element) {
        const seatLabel = element.dataset.seat;
        const fare = parseFloat(element.dataset.fare);

        if (element.classList.contains('selected')) {
            element.classList.remove('selected');
            this.selectedSeats.delete(seatLabel);
        } else {
            if (this.selectedSeats.size >= this.remainingAllowed) {
                alert(`You can only select up to ${this.remainingAllowed} seat(s) for this trip.`);
                return;
            }
            element.classList.add('selected');
            this.selectedSeats.set(seatLabel, fare);
        }

        this.updateSummary();
    },

    updateSummary() {
        const emptyState = document.getElementById('seat-sheet-empty');
        const summary = document.getElementById('seat-sheet-summary');
        const countSpan = document.getElementById('seat-sheet-count');
        const totalSpan = document.getElementById('seat-sheet-total');
        const hiddenInputs = document.getElementById('seat-sheet-hidden-inputs');
        const continueBtn = document.getElementById('seat-sheet-continue');
        const boardDropBtn = document.getElementById('step-btn-board-drop');

        if (!hiddenInputs || !continueBtn) return;

        hiddenInputs.innerHTML = '';
        let total = 0;

        if (this.selectedSeats.size === 0) {
            emptyState?.classList.remove('hidden');
            summary?.classList.add('hidden');
            continueBtn.disabled = true;
            continueBtn.classList.remove('bg-primary-600', 'hover:bg-primary-700', 'shadow-lg');
            continueBtn.classList.add('bg-slate-300', 'cursor-not-allowed');
            if (boardDropBtn) boardDropBtn.setAttribute('disabled', 'true');
        } else {
            emptyState?.classList.add('hidden');
            summary?.classList.remove('hidden');
            continueBtn.disabled = false;
            continueBtn.classList.remove('bg-slate-300', 'cursor-not-allowed');
            continueBtn.classList.add('bg-primary-600', 'hover:bg-primary-700', 'shadow-lg');
            if (boardDropBtn) boardDropBtn.removeAttribute('disabled');

            this.selectedSeats.forEach((fare, seat) => {
                total += fare;
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'selected_seats[]';
                input.value = seat;
                hiddenInputs.appendChild(input);
            });
        }

        if (countSpan) countSpan.textContent = this.selectedSeats.size;
        if (totalSpan) totalSpan.textContent = total.toLocaleString();
    },

    setStep(step) {
        this.currentStep = step;
        const seatsContent = document.getElementById('step-content-seats');
        const boardDropContent = document.getElementById('step-content-board-drop');
        const seatsBtn = document.getElementById('step-btn-seats');
        const boardDropBtn = document.getElementById('step-btn-board-drop');
        const continueBtn = document.getElementById('seat-sheet-continue');

        if (step === 'seats') {
            if (seatsContent) seatsContent.style.display = '';
            if (boardDropContent) boardDropContent.style.display = 'none';

            if (seatsBtn) {
                seatsBtn.className = "pb-3 text-primary-600 font-semibold border-b-2 border-primary-600 whitespace-nowrap";
            }
            if (boardDropBtn) {
                boardDropBtn.className = "pb-3 text-slate-400 font-medium whitespace-nowrap";
            }
            
            if (continueBtn) {
                continueBtn.textContent = "Select board/drop point →";
            }
        } else if (step === 'board-drop') {
            if (seatsContent) seatsContent.style.display = 'none';
            if (boardDropContent) boardDropContent.style.display = 'block';

            if (seatsBtn) {
                seatsBtn.className = "pb-3 text-slate-500 font-medium whitespace-nowrap";
            }
            if (boardDropBtn) {
                boardDropBtn.className = "pb-3 text-primary-600 font-semibold border-b-2 border-primary-600 whitespace-nowrap";
                boardDropBtn.removeAttribute('disabled');
            }

            if (continueBtn) {
                continueBtn.textContent = this.originalSubmitText || "Continue to passenger details →";
            }
        }
    },

    handleContinue(e) {
        if (this.currentStep === 'seats') {
            e.preventDefault();
            this.setStep('board-drop');
        } else {
            // Let the form submit naturally
        }
    },

    switchTab(tabId) {
        document.querySelectorAll('.seat-info-tab').forEach(btn => {
            const onclick = btn.getAttribute('onclick') || '';
            btn.classList.toggle('active', onclick.includes("'" + tabId + "'"));
        });
        document.querySelectorAll('.seat-info-panel').forEach(panel => {
            panel.classList.toggle('active', panel.id === 'seat-info-' + tabId);
        });
    },

    open(url) {
        const overlay = document.getElementById('seat-sheet-overlay');
        const content = document.getElementById('seat-sheet-content');
        if (!overlay || !content) {
            window.location.href = url;
            return;
        }

        content.innerHTML = `
            <div class="flex items-center justify-center h-full">
                <div class="text-slate-400 text-sm flex items-center gap-2">
                    <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                    Loading seats...
                </div>
            </div>`;

        overlay.classList.add('open');
        document.body.style.overflow = 'hidden';

        requestAnimationFrame(() => {
            overlay.classList.add('open');
        });

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html',
            },
        })
            .then(res => {
                if (!res.ok) throw new Error('Failed to load');
                return res.text();
            })
            .then(html => {
                content.innerHTML = html;
                this.init(content.querySelector('.seat-sheet-root'));
            })
            .catch(() => {
                window.location.href = url;
            });
    },

    close() {
        const overlay = document.getElementById('seat-sheet-overlay');
        if (!overlay) return;

        if (overlay.dataset.mode === 'page') {
            document.body.style.overflow = '';
            if (window.history.length > 1) {
                window.history.back();
            } else {
                window.location.href = '/ticket-booking';
            }
            return;
        }

        overlay.classList.remove('open');
        document.body.style.overflow = '';
        this.selectedSeats.clear();
        const content = document.getElementById('seat-sheet-content');
        if (content) content.innerHTML = '';
    },

    openCouponModal() {
        const modal = document.getElementById('coupon-detail-modal');
        if (modal) {
            modal.classList.remove('hidden');
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }
    },

    closeCouponModal() {
        const modal = document.getElementById('coupon-detail-modal');
        if (modal) {
            modal.classList.add('hidden');
        }
    },
};

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') SeatSheet.close();
});
</script>
