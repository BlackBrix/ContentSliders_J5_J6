# BlackBrix – Content Sliders

A lightweight, standalone accordion/content-slider plugin for **Joomla 5 and Joomla 6**, written in PHP and vanilla JavaScript. It preserves the familiar `{slider=Title}...{/slider}` syntax without requiring jQuery, Font Awesome, or other 3rd-party framework.

**Version:** 1.0.3  
**Plugin group:** `content`  
**Plugin element:** `bbcontentsliders`  
**License:** GPL-2.0-or-later

## Features

- Convert `{slider=Title}...{/slider}` blocks in Joomla articles into collapsible content sections.
- Also recognize `{slide=Title}...{/slide}` and common standalone `<p>` wrappers around these tags.
- Configure exclusive mode (only one section open at a time) or allow multiple open sections.
- Configure opening and closing durations independently (defaults: 400 ms and 250 ms; 0 = immediate).
- Optionally scroll to an opened section with configurable offset (default: 60 px) and scrolling duration (default: 500 ms).
- Open sections by URL fragments such as `#Topic-1` or `#Topic-2`.
- Keyboard-accessible buttons, ARIA expanded state, and support for the system's reduced-motion preference.
- No bundled third-party framework or front-end dependencies.
- English and German Joomla language files.

## Installation

1. Download **`plg_content_bbcontentsliders_v1.0.3.zip`** from the [GitHub Releases](../../releases) page. Do not use GitHub's auto-generated source-code ZIP as the Joomla installation package.
2. In Joomla, choose **System → Install → Extensions** and upload the installation ZIP.
3. Enable **Content – BlackBrix Content Sliders** in Joomla's plugin manager.
4. If migrating from any other **3rd-party Tabs and Sliders** (`jw_ts`), disable the old plugin so that two plugins do not process the same article tags.
5. Clear the Joomla and browser caches if you are upgrading and older CSS/JavaScript assets remain cached.

## Usage

In a Joomla article:

```text
{slider=Topic 1}
<p>Any Joomla article content, including links, images, and formatted HTML.</p>
{/slider}

{slider=Topic 2}
<p>Another section.</p>
{/slider}
```

Deep link examples:

```text
https://example.org/companies#Topic-1
https://example.org/companies#Topic-2
```

Titles with non-ASCII characters are transliterated when generating fragment IDs where supported. Duplicate titles receive unique suffixed IDs.

## Settings

| Setting | Default |
| --- | --- |
| Only one slider open at a time | Yes |
| Opening duration | 400 ms |
| Closing duration | 250 ms |
| Automatic scrolling | Yes |
| Scroll offset | 60 px |
| Scroll duration | 500 ms |
| URL fragment/deep-link support | Yes |

Animations with a duration of `0` are immediate. The operating system's reduced-motion preference is respected.

## Requirements and compatibility

- Designed for Joomla 5.x and Joomla 6.x using Joomla's modern plugin architecture.
- **Successfully tested by the project maintainer on Joomla 5.4.x and Joomla 6.2.x** (functional tests in live Joomla test installations).
- Uses the native Joomla 5/6 plugin architecture; Joomla’s backward compatibility plugin is not required.
- Validation performed during packaging: PHP lint, JavaScript syntax check, XML parsing, and ZIP integrity. These checks are not a complete security audit or automated cross-browser test suite.
- The plugin processes Joomla article HTML; it does not independently sanitize article content or replace Joomla's content permissions and filtering.
- Nested slider tags and tab layouts are not supported.

## Source layout

| Path | Purpose |
| --- | --- |
| `bbcontentsliders.xml` | Joomla plugin manifest and configuration fields |
| `services/provider.php` | Joomla dependency injection registration |
| `src/Extension/Bbcontentsliders.php` | Joomla content plugin and tag conversion |
| `media/js/sliders.js` | Accordion interaction, animation, scrolling, deep links |
| `media/css/sliders.css` | Presentation |
| `language/` | English and German plugin translations |

The release installation ZIP is packaged from the plugin manifest and directories above. Files such as `README.md`, `LICENSE`, and `.gitignore` are repository files and are not required in the Joomla installer package.

## Testing status

- Joomla 5.4.x — successful functional test reported by the maintainer.
- Joomla 6.2.x — successful functional test reported by the maintainer.
- The default accordion behavior, configurable opening/closing durations, administration settings, and extension name display were checked during development.
- Further robustness, accessibility, and security testing may be useful; no formal security audit is claimed.

## Changelog

### 1.0.3 — 2026-11-07

- Added English and German `.sys.ini` language files for the correct extension name in Joomla's plugin and extension lists.
- No functional changes to the front end or configuration from v1.0.2.

### 1.0.2 — 2026-11-07

- Added separate opening and closing animation durations (400 ms / 250 ms defaults).
- Adjusted the three Yes/No switcher options to the expected Joomla ordering.

### 1.0.1 — 2026-11-07

- Corrected Web Asset Manager asset URIs to ensure CSS and JavaScript load correctly.

### 1.0.0 — 2026-11-07

- Initial implementation.

## License

Copyright (C) 2026 BlackBrix. Licensed under the GNU General Public License, version 2 or (at your option) any later version (`GPL-2.0-or-later`). See [LICENSE](LICENSE).
