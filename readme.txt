=== SGOplus Software Key ===
Contributors: sgoleo
Tags: license manager, software licensing, rest api, slm migration
Requires at least: 6.5
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A modern and secure Software License Manager for WordPress, featuring premium SGOplus design and a powerful REST API.

== Description ==

SGOplus Software Key is a professional-grade licensing solution designed for developers who need a reliable way to manage software activations. It inherits the premium visual identity of the SGOplus ecosystem.

= Key Features =
* **Secure Database Schema**: Dedicated tables for licenses and registered domains.
* **Modern REST API**: Verify activations via `/wp-json/sgoplus-license/v1/verify` with secret key and user email validation.
* **Custom Key Generation**: Define your own license prefix and generate keys with one click.
* **User Email Binding**: Secure keys by binding them to specific licensee emails.
* **High-Performance Migration**: Built-in module to import data from legacy Software License Manager (SLM) with real-time progress feedback.
* **Bilingual Developer Guild**: Full API documentation in both English and Traditional Chinese with a premium Glassmorphism design.

== Installation ==

1. Upload the plugin folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Configure your API Secret Key and Prefix under 'Software Key+ > Settings'.

== Changelog ==

= 1.1.0 =
* Added custom license prefix support.
* Implemented User Email validation for enhanced security.
* Added "API Quick Copy" utility to settings.
* Refactored Developer Guild page with bilingual support (EN/ZH).
* Improved SLM migration logic with better table detection and email matching.
* Enhanced admin UI with expanded card layouts and glassmorphism effects.

= 1.0.0 =
* Initial release.
* Core database architecture and REST API.
* SLM migration module.
