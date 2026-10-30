// VMGT BOOKINGS - Public site JS

document.addEventListener('DOMContentLoaded', function () {

    // ---- Mobile nav toggle ----
    var toggle = document.getElementById('mobileToggle');
    var nav = document.getElementById('mainNav');
    if (toggle && nav) {
        toggle.addEventListener('click', function () {
            nav.classList.toggle('open');
        });
    }

    // ---- Newsletter AJAX subscribe ----
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

    // ---- Booking wizard ----
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
            var celebLabel = celebSelect.options[celebSelect.selectedIndex].text;
            var get = function (name) {
                var el = wizard.querySelector('[name="' + name + '"]');
                return el ? el.value : '';
            };
            var box = document.getElementById('reviewBox');
            box.innerHTML =
                '<dl>' +
                '<dt>Celebrity</dt><dd>' + escapeHtml(celebLabel) + '</dd>' +
                '<dt>Event Type</dt><dd>' + escapeHtml(get('event_type') || '-') + '</dd>' +
                '<dt>Event Date</dt><dd>' + escapeHtml(get('event_date') || '-') + '</dd>' +
                '<dt>Location</dt><dd>' + escapeHtml(get('event_location') || '-') + '</dd>' +
                '<dt>Budget</dt><dd>' + escapeHtml(get('budget_range') || '-') + '</dd>' +
                '<dt>Full Name</dt><dd>' + escapeHtml(get('full_name')) + '</dd>' +
                '<dt>Email</dt><dd>' + escapeHtml(get('email')) + '</dd>' +
                '<dt>Phone</dt><dd>' + escapeHtml(get('phone') || '-') + '</dd>' +
                '<dt>Organization</dt><dd>' + escapeHtml(get('organization') || '-') + '</dd>' +
                '<dt>Message</dt><dd>' + escapeHtml(get('message') || '-') + '</dd>' +
                '</dl>';
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

        // If a celebrity was preselected via ?celebrity=slug, jump straight past step 1 politely (still visible/editable)
    }
});
