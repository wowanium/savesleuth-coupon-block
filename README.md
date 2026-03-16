# SaveSleuth Coupon Block

A WordPress plugin that adds a reusable Gutenberg coupon block with a shared coupon-offer library in wp-admin.

It is built for affiliate and coupon pages that need a cleaner UX than pasting raw coupon codes directly into page content.

## Features

- Gutenberg coupon block for pages and posts
- Shared coupon offer library in wp-admin
- Reusable offers by ID via shortcode
- Masked coupon codes with reveal interaction
- Affiliate shop links and review links
- Compact and full display variants
- Multilingual labels for German, English, and French
- Frontend script for reveal and copy interactions

## Current version

`1.4.1`

## Included files

- `ss-coupon-block.php` – main plugin file
- `index.js` – block/editor script
- `view.js` – frontend interaction script
- `style.css` – frontend styles
- `editor.css` – editor styles
- `README.txt` – WordPress-style plugin readme

## Installation

1. Download this repository or clone it.
2. Copy the `savesleuth-coupon-block` folder into `wp-content/plugins/`.
3. Activate the plugin in WordPress.
4. Create coupon offers in **SaveSleuth Coupons** in wp-admin.
5. Insert the block in Gutenberg or use the shortcode.

## Shortcode examples

Full variant:

```text
[ss_coupon id="940" lang="de" variant="full"]
```

Compact variant:

```text
[ss_coupon id="940" lang="de" variant="compact"]
```

## Notes

This plugin is designed for coupon-style reveal UX, not for hard security. If a coupon code is intended to be public, determined users may still extract it. A later version can move coupon reveal fully server-side if needed.

## License

GPL-2.0-or-later
