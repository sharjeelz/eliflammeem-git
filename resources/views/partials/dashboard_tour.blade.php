@push('scripts')
<style>
#tour-overlay {
    position: fixed; inset: 0; z-index: 9998;
    background: rgba(0,0,0,0.45); pointer-events: none;
}
#tour-popover {
    position: fixed; z-index: 9999;
    background: #fff; border-radius: 10px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.18);
    padding: 20px 22px 16px;
    max-width: 300px; min-width: 240px;
    font-family: Inter, sans-serif;
    pointer-events: all;
}
[data-bs-theme="dark"] #tour-popover {
    background: #1e1e2d; color: #cdcde6; border: 1px solid #2b2b40;
}
#tour-popover h6 { font-weight: 700; font-size: 14px; margin-bottom: 6px; }
#tour-popover p  { font-size: 13px; color: #5e6278; margin-bottom: 14px; line-height: 1.5; }
[data-bs-theme="dark"] #tour-popover p { color: #9d9dbd; }
#tour-popover .tour-arrow {
    position: absolute; width: 12px; height: 12px;
    background: inherit; border: inherit;
    transform: rotate(45deg);
}
#tour-popover .tour-footer {
    display: flex; justify-content: space-between; align-items: center; gap: 8px;
}
#tour-popover .tour-step-count { font-size: 11px; color: #a1a5b7; }
#tour-popover .tour-highlight {
    position: fixed; z-index: 9997; border-radius: 6px;
    box-shadow: 0 0 0 4px rgba(0, 158, 247, 0.5), 0 0 0 9999px rgba(0,0,0,0.45);
    pointer-events: none; transition: all 0.25s ease;
}
</style>

<script>
(function () {
    const TOUR_KEY = 'schoolytics_tour_done_{{ auth()->id() }}';

    const steps = [
        {
            target: '#tour-nav-dashboard',
            title: '👋 Welcome to ElifLammeem!',
            text:  "Let's take a quick tour so you know where everything is.",
            side:  'right'
        },
        {
            target: '#tour-nav-issues',
            title: '📋 Issues',
            text:  'View and manage all complaints submitted by parents and teachers. Assign to staff and track resolution.',
            side:  'right'
        },
        {
            target: '#tour-nav-contacts',
            title: '👥 Roster Contacts',
            text:  'Import parents and teachers here. Each contact gets a unique access code to submit issues via the public portal.',
            side:  'right'
        },
        {
            target: '.tour-kpi-row',
            title: '📊 Live Stats',
            text:  'These cards show real-time metrics — open issues, resolved today, AI sentiment, and more.',
            side:  'bottom'
        },
        {
            target: '#tour-nav-settings',
            title: '⚙️ Settings',
            text:  'Configure your school profile, branches, issue categories, staff users, and integrations.',
            side:  'right'
        },
        {
            target: '#kt_aside_footer',
            title: '🚀 Your Plan',
            text:  'See your current plan and available features. Click it to view the full plan comparison.',
            side:  'right'
        },
    ];

    let currentStep = 0;
    let overlay, popover, highlight;

    function getEl(selector) {
        return document.querySelector(selector);
    }

    function createElements() {
        overlay = document.createElement('div');
        overlay.id = 'tour-overlay';
        document.body.appendChild(overlay);

        highlight = document.createElement('div');
        highlight.className = 'tour-highlight';
        document.body.appendChild(highlight);

        popover = document.createElement('div');
        popover.id = 'tour-popover';
        document.body.appendChild(popover);
    }

    function removeElements() {
        [overlay, popover, highlight].forEach(el => el && el.remove());
        overlay = popover = highlight = null;
    }

    function positionPopover(target, side) {
        const r = target.getBoundingClientRect();
        const pw = 300, ph = 160;
        const margin = 14;
        let top, left;

        if (side === 'right') {
            top  = r.top + r.height / 2 - ph / 2;
            left = r.right + margin;
        } else if (side === 'bottom') {
            top  = r.bottom + margin;
            left = r.left + r.width / 2 - pw / 2;
        } else if (side === 'left') {
            top  = r.top + r.height / 2 - ph / 2;
            left = r.left - pw - margin;
        } else {
            top  = r.top - ph - margin;
            left = r.left + r.width / 2 - pw / 2;
        }

        // Keep within viewport
        top  = Math.max(10, Math.min(top,  window.innerHeight - ph - 10));
        left = Math.max(10, Math.min(left, window.innerWidth  - pw - 10));

        popover.style.top  = top  + 'px';
        popover.style.left = left + 'px';
        popover.style.width = pw + 'px';

        // Highlight box
        const pad = 5;
        highlight.style.top    = (r.top  - pad + window.scrollY) + 'px';
        highlight.style.left   = (r.left - pad) + 'px';
        highlight.style.width  = (r.width  + pad * 2) + 'px';
        highlight.style.height = (r.height + pad * 2) + 'px';
    }

    function renderStep(index) {
        const step = steps[index];
        const target = getEl(step.target);

        if (!target) {
            // skip missing elements
            if (index < steps.length - 1) { currentStep++; renderStep(currentStep); }
            else doneTour();
            return;
        }

        const isFirst = index === 0;
        const isLast  = index === steps.length - 1;

        popover.innerHTML = `
            <h6>${step.title}</h6>
            <p>${step.text}</p>
            <div class="tour-footer">
                <span class="tour-step-count">${index + 1} / ${steps.length}</span>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-light" id="tour-skip">Skip</button>
                    ${!isFirst ? '<button class="btn btn-sm btn-light-primary" id="tour-prev">← Back</button>' : ''}
                    <button class="btn btn-sm ${isLast ? 'btn-success' : 'btn-primary'}" id="tour-next">
                        ${isLast ? 'Done 🎉' : 'Next →'}
                    </button>
                </div>
            </div>`;

        positionPopover(target, step.side);

        popover.querySelector('#tour-skip').addEventListener('click', doneTour);
        popover.querySelector('#tour-next').addEventListener('click', () => {
            if (isLast) { doneTour(); } else { currentStep++; renderStep(currentStep); }
        });
        const backBtn = popover.querySelector('#tour-prev');
        if (backBtn) backBtn.addEventListener('click', () => { currentStep--; renderStep(currentStep); });
    }

    function startTour() {
        currentStep = 0;
        if (!overlay) createElements();
        renderStep(currentStep);
    }

    function doneTour() {
        localStorage.setItem(TOUR_KEY, '1');
        removeElements();
    }

    // Always expose replay globally
    window.replayTour = function () {
        localStorage.removeItem(TOUR_KEY);
        if (overlay) removeElements();
        createElements();
        startTour();
    };

    // Auto-start on first visit or when redirected with ?tour=1
    const urlParams = new URLSearchParams(window.location.search);
    if (!localStorage.getItem(TOUR_KEY) || urlParams.get('tour') === '1') {
        setTimeout(startTour, 700);
    }
})();
</script>
@endpush
