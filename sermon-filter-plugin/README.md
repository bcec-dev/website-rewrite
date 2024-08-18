# Sermon Filter Plugin

## Description

The Sermon Filter Plugin enhances your WordPress site by adding filter functionality to the sermon post type. This plugin allows users to filter sermons by recent posts, speaker, scripture, and other taxonomies. It supports AJAX-based filtering for a seamless user experience, and includes pagination and search functionality.

## Features

- **Filter Buttons:** Filter sermons by recent posts, speaker, scripture, and other custom taxonomies.
- **Search Bar:** Allows users to search for specific sermons.
- **Pagination:** Paginate through sermons or terms efficiently.
- **AJAX Powered:** All filtering and pagination actions are handled asynchronously for a seamless user experience.
- **Customizable Shortcode**: Use the `[sermon_filter_buttons]` shortcode to insert the filter buttons anywhere on your site.

## Installation

1. Upload the plugin files to the `/wp-content/plugins/sermon-filter-plugin` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Use the `[sermon_filter_buttons]` shortcode in your posts or pages to display the filter buttons.

## Shortcodes

- **`[sermon_filter_buttons]`**: Generates filter buttons for recent posts, speakers, and scripture.

  **Attributes:**
  
  - **`taxonomy`**: (Optional) Comma-separated list of taxonomies to include in the filter buttons. If not provided, defaults to an empty string.
  - **`posts_per_page`**: (Optional) Number of sermons to display per page. Defaults to 6.
  - **`taxonomy_terms_per_page`**: (Optional) Number of taxonomy terms to display per page. Defaults to 10.
  - **`display_names`**: (Optional) Comma-separated list of display names for the taxonomies. If not provided, the default taxonomy names are used.

### Example

```php
[sermon_filter_buttons taxonomy="speaker,scripture" posts_per_page="5" display_names="Speaker,Scripture"]
```

## AJAX Handling

The plugin uses jQuery to handle AJAX requests for filtering sermons. When a filter button is clicked, the relevant sermons are fetched and displayed without a full page reload. A loading spinner is displayed while the AJAX request is processed.

## Changelog

### 1.0.3
- Initial release with filter buttons, AJAX loading, pagination, and search bar.

## Author

Wai Ho Chan

## License

This plugin is licensed under the GPL v2 or later.



