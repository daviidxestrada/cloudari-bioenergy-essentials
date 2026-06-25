=== Cloudari BioEnergy Essentials ===
Contributors: cloudari
Tags: members, access control, eera bioenergy
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.4.15
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Adds isolated bcrypt login and members-area access control for the EERA Bioenergy Elementor kit.

== Description ==

Cloudari BioEnergy Essentials provides private member login, protected page access, member menu handling, and recovered publication redirects for the EERA Bioenergy site.

== Changelog ==

= 1.4.15 =
* Make the Datos Contacto panel visual with tabbed editors and live widget previews.
* Keep repeatable email and phone rows synced with the preview before saving.

= 1.4.14 =
* Add the Datos Contacto wp-admin panel for editing contact widget content.
* Add shortcodes `[eera_contact_widget_1]`, `[eera_contact_widget_2]`, and `[eera_contact_widget_3]`.
* Support multiple validated email rows in contact cards.

= 1.4.13 =
* Exclude protected members-area pages from secondary public queries such as Elementor listing widgets.

= 1.4.12 =
* Keep protected members-area pages out of public home, archive, and search queries even when a WordPress admin is logged in.

= 1.4.11 =
* Let logged-in WordPress administrators access the members area without a separate members login.
* Show administrators as `Bienvenid@ admin` and prioritize the admin identity over any existing members-area session.
* Use ZIP release assets for GitHub plugin updates.

= 1.4.10 =
* Imported the live production plugin from eerabioenergy.eu.
* Connected plugin updates to this public GitHub repository through Plugin Update Checker.
