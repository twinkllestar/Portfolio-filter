# Portfolio Filter

A custom WordPress plugin that displays a project grid with AJAX category filtering and a like button, built with PHP and jQuery.



## Features

- Custom post type `project` and custom taxonomy `project_category`
- `[project_grid]` shortcode renders category buttons and project cards
- Category filtering via jQuery AJAX, with no page reload
- Like button that stores counts in post meta via AJAX

## Tech Stack

PHP, WordPress (hooks, WP_Query, shortcodes), jQuery, AJAX

## How It Works

1. `init` action registers the custom post type and taxonomy
2. `wp_enqueue_scripts` loads `filter.js`; `wp_localize_script` passes `ajaxUrl` and a nonce to it
3. jQuery sends a POST request to `admin-ajax.php`
4. The PHP callback (`wp_ajax_` / `wp_ajax_nopriv_`) verifies the nonce, sanitizes input, queries with `WP_Query`, and returns JSON
5. jQuery replaces the grid HTML with the response; event delegation keeps the like button working after the grid is replaced

## Security

- Nonce verification with `check_ajax_referer`
- Input sanitization with `sanitize_text_field` and `absint`
- Post type validation before updating post meta

## Installation

1. Download this repo as a ZIP, or clone it into `wp-content/plugins/portfolio-filter`
2. Activate **Portfolio Filter** in the WordPress admin
3. Add some Projects and Project Categories
4. Put `[project_grid]` in any page using a Shortcode block