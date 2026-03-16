(function() {
    function copyText(text) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            return navigator.clipboard.writeText(text);
        }
        return new Promise(function(resolve, reject) {
            try {
                var input = document.createElement('textarea');
                input.value = text;
                input.setAttribute('readonly', 'readonly');
                input.style.position = 'absolute';
                input.style.left = '-9999px';
                document.body.appendChild(input);
                input.select();
                document.execCommand('copy');
                document.body.removeChild(input);
                resolve();
            } catch (e) {
                reject(e);
            }
        });
    }

    function revealCoupon(coupon) {
        if (!coupon || coupon.classList.contains('sscb-revealed')) return;

        var codeWrap = coupon.querySelector('.sscb-codewrap');
        var mask = coupon.querySelector('.sscb-code-mask');
        var full = coupon.querySelector('.sscb-code-full');
        var feedback = coupon.querySelector('.sscb-feedback');
        if (!codeWrap) return;

        var code = codeWrap.getAttribute('data-code') || '';
        var copiedLabel = codeWrap.getAttribute('data-copied-label') || 'Code copied';

        coupon.classList.add('sscb-revealed');
        if (mask) mask.hidden = true;
        if (full) full.hidden = false;

        copyText(code).then(function() {
            if (feedback) feedback.textContent = copiedLabel;
        }).catch(function() {
            if (feedback) feedback.textContent = copiedLabel;
        });
    }

    document.addEventListener('click', function(e) {
        var wrap = e.target.closest('.sscb-codewrap');
        if (!wrap) return;
        e.preventDefault();
        revealCoupon(wrap.closest('.sscb-coupon'));
    });

    document.addEventListener('keydown', function(e) {
        var wrap = e.target.closest('.sscb-codewrap');
        if (!wrap) return;
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            revealCoupon(wrap.closest('.sscb-coupon'));
        }
    });
})();
