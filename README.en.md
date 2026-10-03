# IO Blog Editorial Free

[Russian / Русский](README.md)

A lightweight editorial WordPress theme by **Kirill Aleksandrov** for personal blogs and technology publications.

**Free 1.8.0** · WordPress 6.5+ · PHP 8.1+ · GPL-2.0-or-later

## Download and Install

Download the installable `ioblog-editorial-1.8.0.zip` from [GitHub Releases](https://github.com/acidtmn/ioblog-editorial/releases/tag/v1.8.0), not the automatically generated source archive. The release ZIP has the correct `ioblog-editorial/` root directory. Verify SHA-256 with `SHA256SUMS.txt`.

In WordPress open **Appearance → Themes → Add New → Upload Theme**, install the ZIP and activate it. Then open **IO Blog** in the main dashboard menu. Configure the homepage, reading layout, search and comments; assign Primary and Footer menus. Set your logo and Site Icon in **Appearance → Customize → Site Identity**.

## Included in Free

- Editorial homepage with a featured post, editor picks and a recent-post grid.
- Reading presets, adjustable typography and three article layouts.
- Light/dark modes and locally hosted DM Sans fonts.
- Live search and a responsive table of contents.
- Automatic lightweight HTML/CSS covers for posts without featured images.
- Four native Gutenberg patterns and core block styling.
- Native comments with optional images and an emoji picker.
- Article claps: up to ten per browser, personal undo and supporter counters.
- A browser-local library with bookmarks, searchable reading history, status filters and resume-reading controls.
- Private JSON export/import for manual library transfer between devices on the same site.
- Full settings export/import, including saved Pro advertising snippets, analytics, CAPTCHA keys, Customizer values and custom CSS.
- Built-in anti-spam and optional Yandex SmartCaptcha / Google reCAPTCHA.
- Footer menu and optional social links.
- English interface and bundled Russian translations.

External integrations are opt-in. Free requires no license key and does not contact the licensing store. Reading a clap counter creates no cookie; explicit claps use this site's signed anonymous cookie and local database. Library data remains in browser localStorage without account or cloud synchronization.

Library exports contain reading history and should be kept private. Imports are limited to 256 KB, validate the whole file, preserve existing bookmarks and require the same scheme, hostname and port. Import does not send history or bookmark events to Pro statistics. See the privacy guide before deploying.

## New in 1.8.0

**IO Blog → Export and import** downloads every saved theme/Pro setting in one JSON file, including advertising code, enabled states, inline position, integrations and CAPTCHA keys. Import accepts up to 1 MB, previews changes, supports section selection and explicit confirmation, masks secrets in preview and rejects stale confirmations. References to content, menus and images are matched to existing objects rather than blindly assigning foreign IDs. Inactive Pro settings remain available without enabling paid features.

**The file contains secrets and is not encrypted. Keep it private and never publish it.** This is a settings backup, not a database/media backup. Posts, media bytes, menu items and license activation are not copied. URLs are not rewritten automatically. Importing advertising/analytics code requires `unfiltered_html`. Install the selected WordPress language pack before import. Ordinary write failures trigger a compensating restore; retain a database backup in case a database outage prevents rollback.

## Optional Pro Extension

This is **one theme**, not separate Free and Pro themes. The separately distributed **Pro 1.2.1** plugin adds a visual cover editor, link cards, advertising, short links, integrations, ordered article series, save-time social PNG covers and an opt-in reader-interest dashboard in the same IO Blog dashboard. Pro requires Free 1.6.0 or later and works with Free 1.8.0. Social PNG text uses the site language independently of an editor's profile language.

**Pro code, keys, customer data and site configuration are not included in this public repository or its releases.** Official Pro downloads and updates are managed separately by the author's store. Pro is installed through Plugins, not Themes.

## Documentation and Support

The bundled [Russian user guide](readme.html) explains setup and the Free/Pro workflow. The [WordPress readme](readme.txt) contains requirements, translation information, privacy details and the changelog.

For reproducible bugs, open a GitHub issue with your WordPress/PHP/theme versions, reproduction steps and screenshots. Never include passwords, CAPTCHA secrets, license keys or database exports.

## License and Author

Original code and visuals: © 2026 Kirill Aleksandrov, [GNU GPL v2 or later](license.txt). DM Sans is distributed under the [SIL Open Font License 1.1](assets/fonts/OFL.txt).

Author: [kodalexandrova.ru](https://kodalexandrova.ru/).
