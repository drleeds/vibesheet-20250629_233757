# vibesheet-20250629_233757

**Project Type:** WordPress Plugin  
**Description:** Enter project notes here

---

## Table of Contents

1. [Overview](#overview)  
2. [Features](#features)  
3. [Installation](#installation)  
4. [Configuration](#configuration)  
5. [Usage](#usage)  
   - [Shortcode](#shortcode)  
   - [Widget](#widget)  
   - [Gutenberg Block](#gutenberg-block)  
6. [Components](#components)  
7. [Dependencies](#dependencies)  
8. [Development](#development)  
9. [Uninstall](#uninstall)  
10. [License](#license)  

---

## Overview

**Blog List** is a modular WordPress plugin that provides a customizable way to display lists of blog posts on the frontend via:

- Shortcode (`[blog_list]`)  
- Widget (drag-and-drop sidebar placement)  
- Gutenberg block (with sidebar controls)  

It includes an admin settings page for configuration, supports pagination, category/tag filtering, caching via the Transient API, template overrides for styling, and internationalization support.

---

## Features

- Shortcode `[blog_list]` with attributes: `number`, `category`, `order`  
- Widget for sidebar placement  
- Gutenberg block with sidebar controls  
- Admin settings page for defaults and styling  
- Pagination support  
- Category and tag filtering  
- Template override capability  
- Caching via Transient API  
- Enqueued CSS/JS assets  
- Internationalization (i18n) support  

---

## Installation

1. Download or clone the repository into your WordPress plugins directory:  
   ```bash
   wp-content/plugins/vibesheet-20250629_233757
   ```
2. Log in to your WordPress admin dashboard.  
3. Go to **Plugins** ? **Installed Plugins**.  
4. Locate **vibesheet-20250629_233757** and click **Activate**.  

---

## Configuration

1. In the admin sidebar, go to **Settings** ? **Blog List Settings**.  
2. Configure your default post count, order, styling options, cache expiration, etc.  
3. Save changes.

---

## Usage

### Shortcode

Insert the shortcode in any post or page:
```html
[blog_list number="5" category="news" order="DESC"]
```
- `number` (int): Number of posts to display (default from settings).  
- `category` (slug or ID): Filter by category.  
- `order` (ASC|DESC): Sort order.  

### Widget

1. Go to **Appearance** ? **Widgets**.  
2. Add **Blog List** widget to your desired sidebar.  
3. Configure title, number of posts, filters, etc.  

### Gutenberg Block

1. In the block editor, click **+** and search for **Blog List**.  
2. Insert the block and configure in the sidebar controls (number, category, order).  

---

## Components

Below is a breakdown of key files and classes:

- **pluginmain.php**  
  Main plugin bootstrap with header, hook registrations, textdomain loading.  
- **mainpluginfile.php**  
  Initializes plugin, activation/deactivation hooks, loads all components.  
- **classbloglistplugin.php**  
  Core class: `__construct()`, `activate()`, `deactivate()`, `init()`, `loadTextdomain()`, `registerComponents()`.  
- **adminsettingspage.php** / **classbloglistadmin.php**  
  Admin settings page: register settings, sections, fields, render the settings UI.  
- **shortcodehandler.php** / **classbloglistshortcode.php**  
  Registers `[blog_list]` shortcode, fetches posts, renders output.  
- **widgethandler.php** / **classbloglistwidget.php**  
  Defines a widget class for sidebar placement (form, update, widget output).  
- **blockregistration.js** / **classbloglistblock.php**  
  Registers Gutenberg block, enqueues editor & frontend assets, render callback.  
- **assetsstyles.css**  
  Default CSS for blog list output.  
- **bloglist.php**  
  Template loader: fetches data and includes template files for rendering.  
- **uninstall.php**  
  Cleans up options and transients on uninstall.

---

## Dependencies

- WordPress 5.0+  
- PHP 7.0+  
- Gutenberg (for block support)  
- No external libraries  

---

## Development

1. Clone the repo and run a local WordPress environment (e.g., [LocalWP](https://localwp.com/), [Docker](https://hub.docker.com/r/wordpress/)).  
2. Enable debug mode in `wp-config.php`:  
   ```php
   define( 'WP_DEBUG', true );
   define( 'WP_DEBUG_LOG', true );
   define( 'WP_DEBUG_DISPLAY', false );
   ```  
3. Write code in the `src/` folder (if splitting source) or directly in plugin files.  
4. Use WordPress Coding Standards and PHPCS for linting.  
5. Translate strings:  
   ```bash
   xgettext --language=PHP --keyword=__ --keyword=_e --output=languages/vibesheet.pot path/to/files/*.php
   ```  

---

## Uninstall

Upon plugin uninstall (via **Plugins** ? **Delete**), the `uninstall.php` script will:

- Remove all plugin options (settings).  
- Delete any transients created for caching.  

---

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.