=== Demo Lock ===
Contributors: Colin84
Donate link: http://arcsec.ca/
Tags: live, demo, lockdown, test, plugin, developer, secure, admin
Requires at least: 3.5
Tested up to: 3.5.1
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Demo Lock is a fully configurable plugin for the wp-admin interface enabling plugin developers to easily setup a secure live demo environment.

== Description ==
Demo Lock is a fully configurable plugin for WordPress developers that aims to provide a secure environment in the wp-admin interface enabling users to test admin interface plugins in a live demo with extremely limited access to all other WordPress settings.
This is a heavily modified version of a plugin originally written by Thomas Griffin (http://thomasgriffinmedia.com/blog/2012/08/create-live-demo-wordpress-plugin/). It has been modified to 
be completely plugin-independent and fully customizable through the config.php file, and updated to WordPress 3.5 standards.

== Installation ==

* Unzip archive into your plugins directory.
* Open up config.php in your favourite text editor.
* Edit the variable values to reflect your demo plugin settings (by default, all default wp-admin pages and widgets are blocked).
* Save your new config.php file.
* Create a Demo User account in your live WordPress install with the credentials you set in the config.php file under ['username'] and ['password'].
* Set the user role for your new demo account to reflect the value set in the ['role'] variable (default is subscriber).
* Navigate to your plugins page in your admin interface, and enable Demo-Lock.


== Changelog ==

= 1.0.0 =
* First release.