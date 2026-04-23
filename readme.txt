=== Plugin Name ===
SGOplus Software Key

Contributors: sgoleo, sgoplus
Tags: software license, license manager, rest api, react dashboard, dataviews
Requires at least: 6.5
Tested up to: 6.9
Stable tag: 1.0.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A modern, high-performance software license management system for WordPress, featuring a React-based dashboard and secure REST API.

== Description ==

SGOplus Software Key is a next-generation license management solution. It allows developers and software vendors to securely manage license keys, track domain activations, and provide a seamless remote activation experience via a secure REST API.

Key features include:
*   Modern React-based Dashboard using WordPress DataViews API.
*   Secure REST API for remote license activation and validation.
*   Zero-downtime asynchronous background migration for legacy data.
*   Infinite scroll and view persistence for efficient management.
*   Advanced capability-based permission checks.

== Installation ==

1. Upload the `sgoplus-software-key` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Go to 'Software Key' in the admin menu to manage your licenses.
4. Integrate the REST API `sgoplus-swk/v1/activate` into your software for remote verification.

== Changelog ==

= 1.0.0 =
* Initial release.
* Complete refactor from legacy WP Share system.
* New database schema for license and domain management.
* Implemented background migration engine.
* Added React-based DataViews dashboard.
