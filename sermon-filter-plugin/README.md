# Sermon Filter Plugin

## Description

The Sermon Filter Plugin enhances your WordPress site by adding filter functionality to the sermon post type. This plugin allows users to filter sermons by recent posts, speaker, scripture, and other taxonomies. It supports AJAX-based filtering for a seamless user experience, and includes pagination and search functionality.

## Features

- **Filter Buttons:** Filter sermons by recent posts, speaker, scripture, and other custom taxonomies.
- **Search Bar:** Allows users to search for specific sermons.
- **Pagination:** Navigate through multiple sermons or taxonomy terms with pagination.
- **AJAX Powered:** All filtering and pagination actions are handled asynchronously for a seamless user experience.
- **Spinner Overlay:** Shows a loading spinner during AJAX requests to improve user experience.
- **Custom Sorting:** Special sorting for the `scripture` and `speaker` taxonomies to sort by slug instead of name when sorting by name.
- **Customizable Shortcode**: Use the `[sermon_filter_buttons]` shortcode to insert the filter buttons anywhere on your site.
- **Embedded Video Shortcode:** Use the `[sermon_video]` shortcode to display embedded videos from the `sermon_video_url` custom field in a sermon post.

## Installation

1. Upload the plugin files to the `/wp-content/plugins/sermon-filter-plugin` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.

## Shortcodes

### 1. `[sermon_filter_buttons]`

This shortcode generates filter buttons and a search bar for sermon posts.

  **Attributes:**
  
  - **`taxonomy`**: (Optional) Comma-separated list of taxonomies to include in the filter buttons. If not provided, defaults to an empty string.
  - **`posts_per_page`**: (Optional) Number of sermons to display per page. Defaults to 6.
  - **`taxonomy_terms_per_page`**: (Optional) Number of taxonomy terms to display per page. Defaults to 10.
  - **`display_names`**: (Optional) Comma-separated list of display names for the taxonomies. If not provided, the default taxonomy names are used.

### Example

```php
[sermon_filter_buttons taxonomy="speaker,scripture" posts_per_page="5" display_names="Speaker,Scripture"]
```

### 2. `[sermon_video]`
This shortcode displays an embedded video from the sermon_video_url custom field.

Usage:

Place this shortcode in a page or post template to display the video associated with a sermon post.

### Example

```php
[sermon_video]
```

## AJAX Handling

The plugin uses jQuery to handle AJAX requests for filtering sermons. When a filter button is clicked, the relevant sermons are fetched and displayed without a full page reload. A loading spinner is displayed while the AJAX request is processed.

## Custom Sorting for Scripture and Speaker Taxonomies
The plugin includes special sorting functionality for the scripture and speaker taxonomies. When sorting terms by name in the WordPress admin, the terms are instead sorted by their slug.

This is achieved by filtering the terms using the get_terms filter and applying a custom sorting logic based on the slug.

## Customization
The plugin's behavior can be customized by editing the shortcode attributes or modifying the CSS styles provided in the `sfb-style.css` file.

## Changelog

### 1.0.3
- Initial release with filter buttons, AJAX loading, pagination, and search bar.

## Author

Wai Ho Chan

## License

This plugin is licensed under the GPL v2 or later.



