=== IO Blog Editorial ===
Contributors: kirillaleksandrov
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 8.1
Stable tag: 1.11.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Tags: blog, news, two-columns, right-sidebar, custom-logo, custom-menu, featured-images, threaded-comments, translation-ready

A fast, responsive editorial theme for technology blogs with dark mode, live search, CSS covers, and Gutenberg support.

== Description ==

Version 1.11.0 displays clap totals on article cards, fixes reader/print tooltips and shows real author profile data instead of a generic editorial biography. Pro 1.6.0 adds an optional frontend Gutenberg author newsroom, drafts, editorial review and reputation. Site owners still create their own legal documents.

IO Blog Editorial is a responsive classic WordPress theme designed for readable technology publications, personal blogs, and online magazines. It combines a distinctive editorial homepage with a focused single-post layout, a sticky table of contents, accessible dark mode, and lightweight CSS-generated covers for posts without featured images.

The theme uses local assets only by default. Advertising, analytics snippets, social links, Telegram metadata, and third-party CAPTCHA providers are disabled until an administrator explicitly configures them.

= Highlights =

* Configurable editorial homepage with featured, editor-selected, and recent article sections.
* Reading presets, font size, line height, and three article layouts.
* Four translated native Gutenberg patterns.
* Accessible light and dark color schemes with a visitor-side preference toggle.
* Live search modal with configurable minimum query length and result limit.
* Automatic lightweight CSS covers generated from each post title.
* Standard featured images and responsive WordPress image sizes.
* Sticky desktop table of contents and compact mobile table of contents.
* Native comments with optional image attachments and an emoji picker.
* Article claps: up to ten per browser, personal undo, clap and supporter counters.
* Personal browser-local library with bookmarks, searchable reading history, status filters, backup export/import and explicit resume controls.
* Full theme-settings JSON export/import, including saved Pro advertising code, analytics, CAPTCHA keys, Customizer values and custom CSS.
* Design studio with separate light/dark palettes, six font roles, heading sizes and weights, local WOFF2 uploads, geometry, region visibility and private live preview.
* Reader mode, print layout, and real image/CSS cover previews in bookmarks and reading history.
* Built-in anti-spam with optional Yandex SmartCaptcha or Google reCAPTCHA v2.
* Optional Pro extension: link cards, visual cover editor, advertising, short links, series, social PNG covers and reader-interest dashboard.
* Footer menu and optional Telegram, MAX, VK, and Odnoklassniki links.
* Translation-ready PHP, JavaScript, block metadata, POT catalog, and Russian translation.
* Local DM Sans font files; no font requests are made to third-party servers.

== Installation ==

1. In the WordPress dashboard, open Appearance > Themes > Add New > Upload Theme.
2. Select the `ioblog-editorial.zip` archive and choose Install Now.
3. Activate IO Blog Editorial.
4. Open Appearance > Customize to set the homepage copy, footer text, and default color scheme.
5. Open IO Blog in the main administration menu to configure the homepage, reading layout, search, comments, CAPTCHA, and social links.
6. Optionally install IO Blog Editorial Pro 1.3.0 as a plugin to enable publishing tools and reader accounts. Install it through Plugins, not Themes.
7. Assign menus to the Primary menu and Footer menu locations.
8. Set the Site Icon in Appearance > Customize > Site Identity.

== Recommended Setup ==

= Homepage =

The theme uses the standard posts index as its editorial homepage. In Settings > Reading, choose "Your latest posts" or assign the posts page. In IO Blog > Homepage choose a featured post, up to five editorial picks, an About page, section visibility and order. Empty selections use recent posts. Disabled sections leave their posts in the latest feed.

= Menus =

Assign a concise navigation menu to Primary menu and informational links to Footer menu. The mobile menu is generated from the same Primary menu location.
Header navigation can be hidden in IO Blog > Design studio > Site regions without deleting the assigned menu. Search, theme switching and account controls remain available.

= Featured images and CSS covers =

When a regular post has no featured image, Free creates a deterministic CSS cover from the post title, category, and post ID. In Pro the post editor offers a visual cover editor; an explicitly selected CSS style overrides the featured image. Selecting Automatic restores the default behavior.

= Theme settings =

The IO Blog administration page includes:

* Live search minimum length and result count.
* Article claps switch; disabling preserves existing reactions.
* Personal reading library switch: bookmarks, searchable history, reading-status filters, backup transfer and a resume-reading prompt.
* Comment image size, emoji panel, and trusted-administrator SVG uploads.
* Built-in anti-spam, Yandex SmartCaptcha, or Google reCAPTCHA v2.
* Configurable homepage sections and article typography/layouts.
* Footer social links.
* Pro: homepage, in-article, sidebar, and after-article advertising slots.
* Pro: short redirect rules, integration snippets, and Telegram preview representation.

All advertising and external integrations are disabled on a new installation.

== Gutenberg ==

Free supports wide alignment, responsive embeds, core block styles, editor styles and four native patterns: Useful note, Pros and cons, Step-by-step guide, and Article summary. Pro adds the Link card block for internal and external HTTPS URLs. If Pro is disabled, existing cards remain ordinary links on the public site.

== Translation ==

The text domain is `ioblog-editorial`. Translation files are stored in `/languages`.

The package includes:

* `ioblog-editorial.pot` for translators.
* `ru_RU.po` and `ru_RU.mo` for the bundled Russian translation.
* WordPress JSON translation catalogs for JavaScript strings.

== Privacy and External Services ==

IO Blog Editorial does not contact external services on a fresh installation.

Article claps are enabled by default and use this site's REST API. Reading a counter creates no cookie. The first action creates a signed, HttpOnly, SameSite=Lax browser cookie for one year (Secure on HTTPS). Reactions store a hashed browser identifier, post ID and clap count in local database tables. Short-lived rate-limit buckets contain keyed hashes, not raw IP addresses. Supporters represent browser identities, not verified people: clearing cookies or using another browser can create another identity. Disabling claps or switching themes preserves reaction data; permanently deleting a post removes its reactions. Include this functionality in your site's privacy notice.

The personal library uses browser localStorage for up to 100 bookmarks and 50 recent unsaved reading positions. It validates same-origin links and never inserts stored titles as HTML. Reading progress stays in this browser; there is no account sync or transmission of progress to the server. Clearing browser storage removes the library. If storage is blocked, bookmarking reports an error rather than a false success. Disabling the library hides its controls without deleting browser data. Pro interest collection is separately opt-in and may aggregate explicit bookmark actions using the clap module's signed cookie; see the Pro guide before enabling it.

The library dialog includes Bookmarks and Reading history views, title search, and Not started / In progress / Finished filters. History includes both bookmarked and unsaved articles with recorded progress. Export library downloads a private JSON file containing post titles, same-origin URLs, bookmark flags, reading positions and update times. Keep this file private. Import library accepts this format up to 256 KB and only for the same site origin (scheme, hostname and port). It validates the entire file before writing, merges existing bookmarks, keeps the more recently updated reading position, and retains the 50 most recent unsaved entries. An import exceeding 100 bookmarks is rejected without discarding data. A storage error leaves the original snapshot unchanged. Restoring a file does not send bookmark events or reading history to Pro statistics. This is manual device-to-device transfer, not cloud synchronization; backups from production cannot be imported on localhost.

When enabled by an administrator:

* Yandex SmartCaptcha loads a script from `smartcaptcha.cloud.yandex.ru` and sends the visitor token and IP address to its validation endpoint. See https://yandex.com/legal/smartcaptcha_notice/.
* Google reCAPTCHA v2 loads a script from `google.com` and sends the visitor token and IP address to its verification endpoint. See https://policies.google.com/privacy.
* The Link card block can request metadata from the URL entered by an editor. Requests use the WordPress safe HTTP client and are cached.
* Advertising and analytics code runs only when an administrator inserts and enables that code.
* Social links send visitors to the configured external network only after the visitor follows the link.

Site owners are responsible for updating their privacy policy when optional external services are enabled.

== Frequently Asked Questions ==

= How do I back up and restore all theme settings? =

Open IO Blog > Export and import. Download a current JSON backup before importing. The file includes every saved Free and Pro setting, including all advertising slots (code, switches and inline position), analytics snippets, short links, social networks, both CAPTCHA keys, language, site title/tagline, Customizer values, custom CSS and references to menus, logo and site icon. Pro configuration is preserved even with the extension disabled.

Select a trusted JSON file up to 1 MB, preview its changes, choose sections (all selected by default), acknowledge trust and confirm within ten minutes. Configuration is not modified by preview. Changed settings, expired previews, malformed files and unavailable objects are detected before saving. Code and keys are masked in the preview but included in the file. Advertising/analytics HTML requires unfiltered_html capability. Selected sections are merged; other sections remain unchanged. Empty values can clear previous settings, including CAPTCHA secrets.

The file is NOT encrypted. Keep it private: never publish it on GitHub, in public uploads, or send it to strangers. Only administrators with manage_options can export or import. Uploads are processed temporarily without creating a media-library attachment. An import preview is user-bound, single-use and valid for ten minutes. Its private WordPress transient is removed on confirmation or expiration cleanup; with disabled WP-Cron, physical database cleanup may occur later. Importing trusted executable snippets is an intentional administrator action.

For a different domain, URLs in code and links are not rewritten. Check partner URLs and CAPTCHA domain restrictions. Install the target WordPress language pack before import; selecting its language also updates the current administrator locale. Object references are matched by slug/type, not foreign IDs; unavailable objects keep their destination settings and produce a warning. Transfer content and media first if these references are needed.

This is NOT a full site backup: posts, Gutenberg content, per-post covers, media bytes, menu items, comments, claps and browser libraries are not copied. Pro license keys and installation activation are not transferable settings and remain excluded; activate a new domain separately. The importer does not install or activate Pro. On a normal write failure the importer attempts to restore old values; a database outage can prevent rollback, so retain a database backup as well.

= Does the theme require a plugin? =

No. The front-end layout, live search, CSS covers, comments, table of contents, and theme settings are self-contained. An optimization or caching plugin can be used separately.

= Why is no advertisement visible after activation? =

Advertising is intentionally disabled by default. Add your own compliant code to a theme advertising slot and enable that slot.

= Can I use a normal featured image instead of a CSS cover? =

Yes. Upload a featured image in the post editor. It automatically replaces the generated CSS cover for that post.

= How do I translate the theme? =

Use the bundled POT file with Poedit, Loco Translate, or the WordPress i18n tools. Save compiled translations with the `ioblog-editorial` text domain.

== Changelog ==

= 1.11.0 =
Added accessible clap totals linking to article reactions on homepage/archive/related cards. Fixed reader-mode and print tooltips. Removed the misleading generic author biography; real public profile data and profile links are shown when available. Added author-newsroom hooks and full settings-transfer support for writing configuration. Fixed SmartCaptcha hostname comparison for nonstandard ports. Updated Russian translations and documentation. Paired with Pro 1.6.0.

= 1.10.0 =

* Added modular Privacy and consent administration with owner-selected documents and revision tracking.
* Added optional analytics/advertising controls with essential-only, selected and allow-optional choices; code remains inert before permission.
* Added safe transfer of legal-page identities and preserved destination configuration if documents cannot be mapped.
* Added a public-author signature extension point, Russian translations and setup documentation.
* Legal texts are created by the site owner. Other plugins, manual embeds, CAPTCHA and OAuth require separate assessment.

= 1.9.1 =
* Added a separate header-navigation switch to the design studio without removing assigned menus or header actions.
* Added an SMTP administration shortcut when Pro 1.4.0 is active.
* Extended complete settings backup with SMTP configuration and masked credential preview.
* Updated Russian translations, account instructions and Free/Pro setup documentation.

= 1.9.0 =
* Added a design studio with 70 validated settings, private responsive preview and explicit publication.
* Added separate light/dark palettes, font roles, heading sizes/weights, administrator-only local WOFF2 upload and region controls.
* Added distraction-free reader mode and a printable article layout.
* Fixed CSS cover previews in bookmarks/history, including Clearfy REST compatibility and stale redirect caching.
* Extended full settings transfer to design, fonts, Pro account configuration and conditional advertising.
* Added integration points for Pro 1.3.0 reader accounts without requiring Pro for existing Free functions.
* Updated Russian translations and setup/privacy documentation.

= 1.8.0 =

* Added a dedicated Export and import screen in the main IO Blog administration menu.
* Added full JSON configuration backups, including advertising/integration code and CAPTCHA keys without silent exclusions.
* Added section-selective preview and explicit confirmation, masked sensitive preview values, CSRF protection and administrator capability checks.
* Added user-bound expiring one-use previews, stale-configuration detection, write verification and compensating rollback.
* Added safe object-reference matching, Customizer/custom-CSS restoration and preservation of inactive Pro settings.
* Updated English/Russian localization, responsive administration styles and complete backup instructions.
* Retained Pro 1.2.1 unchanged; production and store services were not modified.

= 1.7.0 =

* Added searchable Bookmarks and Reading history views with reading-status filters.
* Added private JSON export and atomic import with origin validation, bounded storage and merge protection.
* Preserved keyboard focus when reading progress refreshes the library list.
* Added Russian translations, mobile light/dark styling and updated administration hints and instructions.
* Retained compatibility with Pro 1.2.1; no licensing or production configuration changes.

= 1.6.0 =

* Added article claps to Free, with a ten-clap limit and personal undo.
* Added a browser-local personal library, reading progress and explicit resume controls.
* Added extension hooks for Pro series, social image metadata and optional interest statistics.
* Added clap and supporter counters, keyboard controls, light/dark styling and reduced-motion support.
* Added signed browser identities, article-bound action tokens, rate limits and conflict detection between tabs.
* Kept personal responses out of shared page caches; replayed requests do not add duplicate claps.
* Added an owner-controlled switch and complete Russian translations.

= 1.5.1 =
Updated the user guide for Pro 1.1.1, activation versus read-only checks, encrypted key storage and native updates. Added the public Free repository reference. Free runtime behavior and independent operation are unchanged.

= 1.5.0 =

* Split publishing tools into the separate IO Blog Editorial Pro 1.0.0 plugin.
* Added configurable homepage sections and manual editorial picks.
* Added reading presets, font/spacing controls, and three article layouts.
* Added four translated native Gutenberg patterns with light/dark styling.
* Added a visual cover editor in Pro.
* Preserved saved Pro settings when the plugin is disabled.
* Rebuilt Russian PHP and Gutenberg translation catalogs for both packages.

The paid distribution is prepared as a separate plugin. Checkout, license service and commercial automatic updates are not connected in this release.

= 1.4.0 =
* Redesigned the theme dashboard with responsive section tabs and a persistent save bar.
* Added unsaved-change detection and a warning before leaving edited settings.
* Added keyboard tab navigation, accessible code-field labels, and visible toggle focus.
* Kept every setting in the native WordPress form, including fields on inactive tabs.
* Added Russian translations for the new dashboard controls.
* Corrected release version metadata in the package documentation.

= 1.3.1 =

* Fixed lost backslashes when saving advertising and integration JavaScript.
* Made language settings follow WordPress and retain the current locale when translation installation fails.
* Fixed table-of-contents JavaScript errors for numeric and encoded heading IDs.
* Prevented stale live-search responses and added a keyboard focus loop with focus restoration.
* Added a separate search network error message and manual copy fallback after clipboard permission denial.
* Improved color scheme handling with blocked storage and released replaced comment image previews.
* Removed duplicate category tab stops and prevented automatic ads from duplicating manual shortcodes or entering secondary content renders.

= 1.3.0 =

* Added complete internationalization for PHP, JavaScript, block metadata, and accessibility labels.
* Added Russian PO, MO, and JavaScript JSON translation catalogs.
* Added `theme.json` editor settings and an official distribution readme.
* Added explicit GPL and DM Sans font licensing files.
* Changed fresh-install advertising, tracking, social, and Telegram defaults to opt-in.
* Improved CAPTCHA language selection based on the active WordPress locale.

= 1.2.5 =

* Added automatic CSS covers for posts without featured images.
* Improved the editorial layout, comments, advertising slots, and administration page.

== Resources ==

DM Sans
Copyright 2014 The DM Sans Project Authors.
License: SIL Open Font License 1.1.
Source: https://github.com/googlefonts/dm-fonts
License file: assets/fonts/OFL.txt

All custom PHP, JavaScript, CSS, SVG icons, and screenshots bundled with the theme are copyright 2026 Kirill Aleksandrov and licensed under GPLv2 or later.
