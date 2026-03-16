(function (blocks, element, blockEditor, components) {
    var el = element.createElement;
    var useBlockProps = blockEditor.useBlockProps;
    var InspectorControls = blockEditor.InspectorControls;
    var PanelBody = components.PanelBody;
    var TextControl = components.TextControl;
    var TextareaControl = components.TextareaControl;
    var SelectControl = components.SelectControl;
    var ToggleControl = components.ToggleControl;

    var offers = (window.SSCBData && window.SSCBData.offers) ? window.SSCBData.offers : [];

    function getLabels(lang) {
        var map = {
            de: { active: 'Aktiver Code', show: 'Code aufdecken', shop: 'Zum Shop', review: 'Review lesen', discount: 'Rabatt', deal: 'Unser aktueller Deal-Tipp', reveal: 'Zum Aufdecken klicken' },
            en: { active: 'Active code', show: 'Reveal code', shop: 'Go to shop', review: 'Read review', discount: 'Discount', deal: 'Our current deal pick', reveal: 'Click to reveal' },
            fr: { active: 'Code actif', show: 'Révéler le code', shop: 'Voir la boutique', review: 'Lire l’avis', discount: 'Réduction', deal: 'Notre bon plan du moment', reveal: 'Cliquez pour révéler' }
        };
        return map[lang] || map.en;
    }

    function maskCode(code) {
        code = String(code || '').trim();
        if (!code) return 'SAV••••••TH';
        if (code.length <= 4) return '••••';
        var start = Math.min(3, Math.max(1, Math.floor(code.length / 3)));
        var end = Math.min(2, Math.max(1, Math.floor(code.length / 4)));
        return code.slice(0, start) + Array(Math.max(4, code.length - start - end) + 1).join('•') + code.slice(code.length - end);
    }

    function offerOptions() {
        var options = [{ label: 'None (manual entry)', value: 0 }];
        offers.forEach(function (offer) {
            options.push({ label: offer.label, value: offer.id });
        });
        return options;
    }

    function getSelectedOffer(id) {
        var num = parseInt(id || 0, 10);
        for (var i = 0; i < offers.length; i++) {
            if (parseInt(offers[i].id, 10) === num) return offers[i];
        }
        return null;
    }

    blocks.registerBlockType('savesleuth/coupon-block', {
        apiVersion: 3,
        title: 'SaveSleuth Coupon',
        icon: 'tickets-alt',
        category: 'widgets',
        description: 'Beautiful coupon block with masked code, affiliate link, and review button.',
        attributes: {
            offerId: { type: 'number', default: 0 },
            storeName: { type: 'string', default: 'Happy 420' },
            discount: { type: 'string', default: '10%' },
            couponCode: { type: 'string', default: 'SAVESLEUTH' },
            affiliateUrl: { type: 'string', default: 'https://happy-420.de/?ref=savesleuth' },
            reviewUrl: { type: 'string', default: '/de/stores/happy-420/' },
            language: { type: 'string', default: 'de' },
            variant: { type: 'string', default: 'full' },
            headline: { type: 'string', default: '' },
            description: { type: 'string', default: '' },
            copyOpensTab: { type: 'boolean', default: true },
            showReview: { type: 'boolean', default: true }
        },
        edit: function (props) {
            var a = props.attributes;
            var set = props.setAttributes;
            var selectedOffer = getSelectedOffer(a.offerId);
            var preview = selectedOffer || a;
            var l = getLabels(preview.language || a.language || 'de');
            var headline = preview.headline || (preview.variant === 'compact' ? (preview.storeName || 'Store') : l.deal);
            var blockProps = useBlockProps({ className: 'sscb-editor-wrap' });

            return el('div', blockProps,
                el(InspectorControls, {},
                    el(PanelBody, { title: 'Coupon Settings', initialOpen: true },
                        el(SelectControl, {
                            label: 'Saved offer',
                            value: a.offerId || 0,
                            options: offerOptions(),
                            onChange: function (v) { set({ offerId: parseInt(v || 0, 10) || 0 }); }
                        }),
                        !selectedOffer && el(TextControl, {
                            label: 'Store name',
                            value: a.storeName || '',
                            onChange: function (v) { set({ storeName: v }); }
                        }),
                        !selectedOffer && el(TextControl, {
                            label: 'Discount',
                            value: a.discount || '',
                            onChange: function (v) { set({ discount: v }); }
                        }),
                        !selectedOffer && el(TextControl, {
                            label: 'Coupon code',
                            value: a.couponCode || '',
                            onChange: function (v) { set({ couponCode: String(v || '').toUpperCase() }); }
                        }),
                        !selectedOffer && el(TextControl, {
                            label: 'Affiliate URL',
                            value: a.affiliateUrl || '',
                            onChange: function (v) { set({ affiliateUrl: v }); }
                        }),
                        !selectedOffer && el(TextControl, {
                            label: 'Review URL',
                            value: a.reviewUrl || '',
                            onChange: function (v) { set({ reviewUrl: v }); }
                        }),
                        !selectedOffer && el(SelectControl, {
                            label: 'Language',
                            value: a.language || 'de',
                            options: [
                                { label: 'Deutsch', value: 'de' },
                                { label: 'English', value: 'en' },
                                { label: 'Français', value: 'fr' }
                            ],
                            onChange: function (v) { set({ language: v }); }
                        }),
                        !selectedOffer && el(SelectControl, {
                            label: 'Variant',
                            value: a.variant || 'full',
                            options: [
                                { label: 'Full', value: 'full' },
                                { label: 'Compact', value: 'compact' }
                            ],
                            onChange: function (v) { set({ variant: v }); }
                        }),
                        !selectedOffer && el(TextControl, {
                            label: 'Custom headline (optional)',
                            value: a.headline || '',
                            onChange: function (v) { set({ headline: v }); }
                        }),
                        !selectedOffer && el(TextareaControl, {
                            label: 'Custom description (optional)',
                            value: a.description || '',
                            onChange: function (v) { set({ description: v }); }
                        }),
                        !selectedOffer && el(ToggleControl, {
                            label: 'Open affiliate link after clicking “show code”',
                            checked: !!a.copyOpensTab,
                            onChange: function (v) { set({ copyOpensTab: v }); }
                        }),
                        !selectedOffer && el(ToggleControl, {
                            label: 'Show review button',
                            checked: !!a.showReview,
                            onChange: function (v) { set({ showReview: v }); }
                        }),
                        selectedOffer && el('p', { className: 'description' }, 'This block uses a saved offer. Edit the offer in SaveSleuth Coupons to update all instances at once.')
                    )
                ),
                el('div', { className: 'sscb-coupon sscb-preview sscb-variant-' + (preview.variant || 'full') },
                    el('div', { className: 'sscb-toprow' },
                        el('div', { className: 'sscb-badges' },
                            el('span', { className: 'sscb-discount' }, (preview.discount || '10%') + ' ' + l.discount),
                            el('span', { className: 'sscb-status' }, l.active)
                        )
                    ),
                    el('div', { className: 'sscb-headline' }, headline),
                    el('p', { className: 'sscb-description' }, preview.description || ((preview.storeName || 'Store') + ' preview block in editor.')),
                    el('div', { className: 'sscb-mainrow' },
                        el('div', { className: 'sscb-codepanel' },
                            el('div', { className: 'sscb-codewrap' },
                                el('span', { className: 'sscb-code-mask' }, maskCode(preview.couponCode || 'SAVESLEUTH')),
                                el('span', { className: 'sscb-reveal-curtain' }, el('span', { className: 'sscb-curtain-text' }, l.reveal))
                            )
                        ),
                        el('div', { className: 'sscb-actions' },
                            el('a', { className: 'sscb-shop-link', href: '#', onClick: function (e) { e.preventDefault(); } }, l.shop),
                            (selectedOffer ? selectedOffer.reviewUrl : a.showReview) && el('a', { className: 'sscb-review-link', href: '#', onClick: function (e) { e.preventDefault(); } }, l.review)
                        )
                    )
                )
            );
        },
        save: function () { return null; }
    });
})(window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.components);
