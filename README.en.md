# IO Blog Editorial Free

[Russian / Русский](README.md)

A lightweight editorial WordPress theme by **Kirill Aleksandrov** for personal blogs and technology publications.

**Free 1.11.0** · WordPress 6.5+ · PHP 8.1+ · GPL-2.0-or-later

## Download and Install

Download the installable `ioblog-editorial-1.11.0.zip` from [GitHub Releases](https://github.com/acidtmn/ioblog-editorial/releases/tag/v1.11.0), not the automatically generated source archive. The release ZIP has the correct `ioblog-editorial/` root directory. Verify SHA-256 with `SHA256SUMS.txt`.

In WordPress open **Appearance → Themes → Add New → Upload Theme**, install the ZIP and activate it. Then open **IO Blog** in the main dashboard menu. Configure the homepage, reading layout, search and comments; assign Primary and Footer menus. Set your logo and Site Icon in **Appearance → Customize → Site Identity**.

## Included in Free

- Editorial homepage with a featured post, editor picks and a recent-post grid.
- Reading presets, adjustable typography and three article layouts.
- A 71-setting design studio: separate palettes, six font roles, heading sizes/weights, local WOFF2, geometry and region controls.
- Private responsive preview, explicit publication, reader mode and a printable layout.
- Light/dark modes and locally hosted DM Sans fonts.
- Live search and a responsive table of contents.
- Automatic lightweight HTML/CSS covers for posts without featured images.
- Four native Gutenberg patterns and core block styling.
- Native comments with optional images and an emoji picker.
- Article claps: up to ten per browser, personal undo and supporter counters.
- A browser-local library with bookmarks, searchable reading history, status filters and resume-reading controls.
- Actual photo/CSS cover previews in bookmarks and history, including Clearfy REST compatibility.
- Private JSON export/import for manual library transfer between devices on the same site.
- Full settings export/import, including saved Pro advertising snippets, analytics, CAPTCHA keys, Customizer values and custom CSS.
- Built-in anti-spam and optional Yandex SmartCaptcha / Google reCAPTCHA.
- Footer menu and optional social links.
- English interface and bundled Russian translations.
- A privacy/consent dashboard for site-owner documents, consent revisions and optional theme analytics/advertising.

External integrations are opt-in. Free requires no license key and does not contact the licensing store. Reading a clap counter creates no cookie; explicit claps use this site's signed anonymous cookie and local database. Library data remains in browser localStorage without account or cloud synchronization.

Library exports contain reading history and should be kept private. Imports are limited to 256 KB, validate the whole file, preserve existing bookmarks and require the same scheme, hostname and port. Import does not send history or bookmark events to Pro statistics. See the privacy guide before deploying.

## New in 1.11.0

Article cards show real clap totals and link to reactions. Reader-mode and print tooltips are populated. Author cards display actual profiles rather than a generic editorial biography. SmartCaptcha checks support nonstandard ports. Russian translations and instructions are updated.

Private **Pro 1.6.0** adds an opt-in frontend Gutenberg author newsroom, own drafts, autosave, editorial review, proposed updates and editorial reputation ranking. Review is required by default, subscriber roles stay unchanged, and anonymous claps never grant publishing privileges. Automatic advertising avoids nested callouts and quotes. This is not a complete Medium/w3a clone; Pro code is not published here.

## Previously: 1.10.0

**IO Blog → Privacy and consent** connects published owner-authored documents to Pro forms and the visitor notice. Theme analytics and advertising remain inert until their category is allowed; visitors can change their choice. Complete settings backup includes document configuration and matches references to existing published pages on import. Russian translations and setup guides have been updated.

These are technical tools, not legal certification or ready-made policies for Russian Law 152-FZ. The purchaser writes their own privacy policy, separate processing consent and separate profile-publication consent. Other plugins, manual embeds, CAPTCHA and OAuth are not controlled by the category switches. Choices expire after 180 days or document changes; revocation reloads the page but does not automatically delete third-party cookies.

## Design Studio

**IO Blog → Design studio** includes searchable settings, per-field reset, a contrast indicator and desktop/tablet/mobile previews. Preview drafts are restricted to their administrator and do not change the public site. WOFF2 uploads are administrator-only and limited to 2 MB. Design, conditional ads and Pro account configuration are included in complete backup. Previous release archives remain unchanged.

## Complete Settings Backup

**IO Blog → Export and import** downloads every saved theme/Pro setting in one JSON file, including advertising code, enabled states, inline position, integrations and CAPTCHA keys. Import accepts up to 1 MB, previews changes, supports section selection and explicit confirmation, masks secrets in preview and rejects stale confirmations. References to content, menus and images are matched to existing objects rather than blindly assigning foreign IDs. Inactive Pro settings remain available without enabling paid features.

**The file contains secrets and is not encrypted. Keep it private and never publish it.** This is a settings backup, not a database/media backup. Posts, media bytes, menu items and license activation are not copied. URLs are not rewritten automatically. Importing advertising/analytics code requires `unfiltered_html`. Install the selected WordPress language pack before import. Ordinary write failures trigger a compensating restore; retain a database backup in case a database outage prevents rollback.

## Optional Pro Extension

This is **one theme**, not separate Free and Pro themes. The separately distributed **Pro 1.6.0** plugin adds a visual cover editor, link cards, conditional advertising, short links, integrations, series, social PNG covers and opt-in reader interests. Publishing tools require Free 1.11.0 or later.

Pro 1.4.0 adds accessible login/recovery dialogs, email-code registration without a username field, bundled provider SVG icons, a responsive conversation UI with unread counts and drafts, and a separate SMTP panel with SSL/STARTTLS, strict-by-default certificate validation and administrator delivery tests. Complete private settings exports include the saved SMTP password.

Pro 1.5.0 adds extended profiles, a headline, biography, optional location/HTTPS website and an accessible browser avatar cropper. Only the selected square is uploaded and reencoded as WebP. Profiles are private by default; public access requires independent consent and can be withdrawn. Email/social registration validates document revisions before account creation. Native WordPress personal-data export/erasure includes profile, avatar and consent history. Configure separate published documents before enabling registration with required consent; existing sign-in and recovery remain available.

New independently switchable Pro modules include reader accounts, collections, opt-in library sync, category feed, own comments, confirmed email, sessions, notifications and consenting text messages with limits, blocking and reports. VK, Yandex, Telegram, Google and GitHub sign-in requires your registered applications. No Odnoklassniki or Mail.ru login is included. Digests require configured WordPress mail and cron. The account shortcode works independently of the active theme. Account modules are disabled by default.

**Pro code, keys, customer data and site configuration are not included in this public repository or its releases.** Official Pro downloads and updates are managed separately by the author's store. Pro is installed through Plugins, not Themes.

## Documentation and Support

The bundled [Russian user guide](readme.html) explains setup and the Free/Pro workflow. The [WordPress readme](readme.txt) contains requirements, translation information, privacy details and the changelog.

For reproducible bugs, open a GitHub issue with your WordPress/PHP/theme versions, reproduction steps and screenshots. Never include passwords, CAPTCHA secrets, license keys or database exports.

## License and Author

Original code and visuals: © 2026 Kirill Aleksandrov, [GNU GPL v2 or later](license.txt). DM Sans is distributed under the [SIL Open Font License 1.1](assets/fonts/OFL.txt).

Author: [kodalexandrova.ru](https://kodalexandrova.ru/).
