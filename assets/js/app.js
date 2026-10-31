// VMGT BOOKINGS - Modern App Experience JS

document.addEventListener('DOMContentLoaded', function () {

    // =========================================================================
    // 1. Mobile Menu Drawer & Outside Click
    // =========================================================================
    var toggle = document.getElementById('mobileToggle');
    var nav = document.getElementById('mainNav');
    if (toggle && nav) {
        toggle.addEventListener('click', function (e) {
            e.stopPropagation();
            nav.classList.toggle('open');
        });

        document.addEventListener('click', function (e) {
            if (nav.classList.contains('open') && !nav.contains(e.target) && e.target !== toggle) {
                nav.classList.remove('open');
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && nav.classList.contains('open')) {
                nav.classList.remove('open');
            }
        });
    }

    // =========================================================================
    // 2. Interactive Image & Card Shuffler
    // =========================================================================
    var shuffleBtn = document.getElementById('btnShuffleShowcase');
    var showcaseRail = document.getElementById('showcaseRail');
    if (shuffleBtn && showcaseRail) {
        shuffleBtn.addEventListener('click', function () {
            // Animate shuffle
            var cards = Array.from(showcaseRail.children);
            if (cards.length > 1) {
                showcaseRail.style.opacity = '0.3';
                showcaseRail.style.transform = 'scale(0.98)';
                showcaseRail.style.transition = 'all 0.2s ease';

                setTimeout(function () {
                    // Fisher-Yates shuffle array of cards
                    for (var i = cards.length - 1; i > 0; i--) {
                        var j = Math.floor(Math.random() * (i + 1));
                        var temp = cards[i];
                        cards[i] = cards[j];
                        cards[j] = temp;
                    }

                    // Append in new order
                    cards.forEach(function (card) {
                        showcaseRail.appendChild(card);
                    });

                    showcaseRail.style.opacity = '1';
                    showcaseRail.style.transform = 'scale(1)';
                }, 220);
            }
        });
    }

    // =========================================================================
    // 3. Category Filter Pills (Instant App Filtering)
    // =========================================================================
    var filterPills = document.querySelectorAll('[data-filter-pill]');
    var filterableCards = document.querySelectorAll('[data-category]');

    if (filterPills.length > 0 && filterableCards.length > 0) {
        filterPills.forEach(function (pill) {
            pill.addEventListener('click', function () {
                var selected = pill.getAttribute('data-filter-pill').toLowerCase();

                // Update active state
                filterPills.forEach(function (p) { p.classList.remove('active'); });
                pill.classList.add('active');

                // Filter cards
                filterableCards.forEach(function (card) {
                    var cardCategory = (card.getAttribute('data-category') || '').toLowerCase();
                    if (selected === 'all' || cardCategory.indexOf(selected) !== -1 || selected.indexOf(cardCategory) !== -1) {
                        card.style.display = '';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });
    }

    // =========================================================================
    // 4. Newsletter AJAX Subscribe
    // =========================================================================
    var newsletterForm = document.getElementById('newsletterForm');
    var newsletterMessage = document.getElementById('newsletterMessage');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function (e) {
            e.preventDefault();
            var email = newsletterForm.querySelector('input[name="email"]').value;
            var btn = newsletterForm.querySelector('button');
            btn.disabled = true;

            fetch('/subscribe.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'email=' + encodeURIComponent(email)
            })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    newsletterMessage.textContent = data.message;
                    if (data.ok) {
                        newsletterForm.reset();
                    }
                })
                .catch(function () {
                    newsletterMessage.textContent = 'Something went wrong. Please try again.';
                })
                .finally(function () {
                    btn.disabled = false;
                });
        });
    }

    // =========================================================================
    // 5. App-Style Booking Wizard (Supports Celebrities & Vacations)
    // =========================================================================
    var wizard = document.getElementById('bookingForm');
    if (wizard) {
        var steps = Array.prototype.slice.call(wizard.querySelectorAll('.wizard-step'));
        var dots = Array.prototype.slice.call(document.querySelectorAll('[data-step-dot]'));
        var currentStep = 1;

        function showStep(n) {
            steps.forEach(function (step) {
                step.hidden = parseInt(step.getAttribute('data-step'), 10) !== n;
            });
            dots.forEach(function (dot) {
                dot.classList.toggle('active', parseInt(dot.getAttribute('data-step-dot'), 10) <= n);
            });
            currentStep = n;
            if (n === 4) {
                buildReview();
            }
            wizard.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function validateStep(n) {
            var step = wizard.querySelector('.wizard-step[data-step="' + n + '"]');
            var required = step.querySelectorAll('[required]');
            for (var i = 0; i < required.length; i++) {
                if (!required[i].value.trim()) {
                    required[i].reportValidity();
                    return false;
                }
                if (required[i].type === 'email') {
                    var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!re.test(required[i].value.trim())) {
                        required[i].reportValidity();
                        return false;
                    }
                }
            }
            return true;
        }

        function buildReview() {
            var celebSelect = wizard.querySelector('[name="celebrity_id"]');
            var celebLabel = (celebSelect && celebSelect.selectedIndex >= 0) ? celebSelect.options[celebSelect.selectedIndex].text : 'Not specified';
            var get = function (name) {
                var el = wizard.querySelector('[name="' + name + '"]');
                return el ? el.value : '';
            };
            var box = document.getElementById('reviewBox');
            if (box) {
                box.innerHTML =
                    '<dl>' +
                    '<dt>Celebrity / Host</dt><dd>' + escapeHtml(celebLabel) + '</dd>' +
                    '<dt>Booking Type</dt><dd>' + escapeHtml(get('event_type') || 'Celebrity Booking') + '</dd>' +
                    '<dt>Requested Date</dt><dd>' + escapeHtml(get('event_date') || 'Flexible / To be agreed') + '</dd>' +
                    '<dt>Destination / City</dt><dd>' + escapeHtml(get('event_location') || '-') + '</dd>' +
                    '<dt>Budget Range</dt><dd>' + escapeHtml(get('budget_range') || '-') + '</dd>' +
                    '<dt>Full Name</dt><dd>' + escapeHtml(get('full_name')) + '</dd>' +
                    '<dt>Email</dt><dd>' + escapeHtml(get('email')) + '</dd>' +
                    '<dt>Phone</dt><dd>' + escapeHtml(get('phone') || '-') + '</dd>' +
                    '<dt>Special Notes</dt><dd>' + escapeHtml(get('message') || '-') + '</dd>' +
                    '</dl>';
            }
        }

        function escapeHtml(str) {
            var div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }

        wizard.querySelectorAll('.wizard-next').forEach(function (btn) {
            btn.addEventListener('click', function () {
                if (validateStep(currentStep)) {
                    showStep(currentStep + 1);
                }
            });
        });

        wizard.querySelectorAll('.wizard-back').forEach(function (btn) {
            btn.addEventListener('click', function () {
                showStep(currentStep - 1);
            });
        });
    }
});
