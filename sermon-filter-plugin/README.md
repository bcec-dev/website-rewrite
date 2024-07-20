# Sermon Filter Plugin

Adds filter buttons for recent, speaker, and scripture on the sermon post type.

## Description

This plugin allows you to filter sermons by recent, speaker, and scripture. You can add the filter buttons using a shortcode and customize the appearance and behavior through the shortcode attributes.

## Installation

1. Upload the `sermon-filter-plugin` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Add the `[sermon_filter_buttons]` shortcode to a post or page where you want the filter buttons to appear.

## Shortcode

Use the `[sermon_filter_buttons]` shortcode to display the filter buttons.

**Attributes:**
- `taxonomy` - A comma-separated list of taxonomy slugs (e.g., `speaker,scripture`).
- `items_per_page` - The number of items to display per page (default is 10).
- `display_names` - A comma-separated list of display names corresponding to the taxonomies (e.g., `Speakers,Scriptures`).

Example: `[sermon_filter_buttons taxonomy="speaker,scripture" items_per_page="5" display_names="Speakers,Scriptures"]`

## Frequently Asked Questions

**How do I change the text of the filter buttons?**

You can change the display names of the filter buttons using the `display_names` attribute in the shortcode.

**Can I customize the appearance of the filter buttons?**

Yes, you can customize the appearance of the filter buttons using CSS. The buttons have the class `sermon-filter-button`.

**How do I translate the plugin?**

The plugin includes support for translations. You can translate the plugin into your language by creating a `.po` file in the `languages` folder.

## Changelog

### 1.0
* Initial release.

## License

This plugin is licensed under the GPLv2 or later.

