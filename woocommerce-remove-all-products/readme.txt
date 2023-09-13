=== WooCommerce Remove All Products ===
Contributors: Gabriel Reguly, Erik Golinelli
Donate link: https://github.com/greguly/WooCommerce-Remove-All-Products
Tags: woocommerce, products, remove
Requires at least: 3.0
Tested up to: 6.3.1
Stable tag: 8.1.0
WC requires at least: 3.0
WC tested up to: 8.1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Removes all products from WooCommerce, 150 products per round.

== Description ==

Useful for developers who are importing products and want a tool to easily remove the products.

= Contributing and reporting bugs =

You can contribute code and localizations to this plugin via GitHub: [https://github.com/greguly/WooCommerce-Remove-All-Products](https://github.com/greguly/WooCommerce-Remove-All-Products)

= Support =

Use the WordPress.org forums for community support - I cannot offer support directly for free. If you spot a bug, you can of course log it on [Github](https://github.com/greguly/WooCommerce-Remove-All-Products) instead where I can act upon it more efficiently.

If you want help with a customisation, consider [hiring a developer](http://omniwp.com.br/hire-a-developer/) 


== Installation ==

1. Upload plugin files to your plugins folder, or install using WordPress' built-in Add New Plugin installer
1. Activate the plugin
1. Find the menu entry 'Remove All Products' under 'WooCommerce' menu
1. Your WooCommerce store now has an easy way to remove all products


== Frequently Asked Questions == 

= What is the plugin license? =

* This plugin is released under a GPL license

= How can I remove more than 150 products per round? =

* Find and edit code  where " 'numberposts' => 150 ". 
* Use the WordPress.org forums for community support as I cannot offer support directly for free.
If you want help with a customisation, [http://omniwp.com.br/hire-a-developer/](hire a developer!)

== Changelog ==

=  8.1.0 2023-09-13 =
* Updated for WordPress 6.3.1 and WooCommerce 8.1.0
* Dev: Bumped WC compatibility headers

=  6.4 2022-04-04 =
* Updated for WordPress 5.9.2 and WooCommerce 6.4
* Dev: Bumped WC compatibility headers

=  4.2 2020-06-19 =
* Updated for WordPress 5.4.2 and WooCommerce 4.2
* Dev: Bumped WC compatibility headers

=  4.1 2020-05-15 =
* Updated for WordPress 5.4 and WooCommerce 4.0
* Enhancement: Remove product images, 🎉 https://github.com/jraoatlogic
* Enhancement: Direct link to plugin page
* Dev: Bumped WC compatibility headers
* Tweak: Better code formatting 
* Cosmetic: Better word formatting

=  1.0.6 2018-05-12 =
* Bug fix: Failed due to fatal error: Call to a member function get_formatted_name() on boolean. Thanks Joel @https://wordpress.org/support/topic/failed-due-to-fatal-error/
* Dev: Bumped WC compatibility headers

=  1.0.5 2017-09-21 =
* Dev: Added WC compatibility headers

=  1.0.4.1 2016-10-03 =
* Cosmetic: Changed HTML header levels ( e.g. h2 to h1 )

=  1.0.4 2015-08-07 =
* Cosmetic: Changed wording from 'Removing 250 products' to 'Trying to remove 250 products'. Thanks Nick @https://github.com/greguly/WooCommerce-Remove-All-Products/issues/2

= 1.0.3 =
* Improvement: To fix memory/processing timeout errors now we get product count instead of getting the actual products

= 1.0.2 =
* Added: ask for users to review the plugin at WordPress.org
* Improvement: Updated text domains 

= 1.0.1 =
* Improvement: get products with any post status, kudos for Stevinoz (https://wordpress.org/support/profile/stevinoz) who warned me about 'drafts' not being removed 

= 1.0 =
* Initial plugin release

== Upgrade Notice ==

= 8.1.0 = 
* Version bump

= 6.4 = 
* Version bump

= 4.2 = 
* Version bump

= 4.1 = 
* Improvement (now removes product images), upgrade safe

= 1.0.6 = 
* Bug fix, upgrade safe

= 1.0.3 = 
* Improvement, upgrade safe

= 1.0.2 = 
* Improvement, upgrade safe

= 1.0.1 = 
* Improvement, upgrade safe

= 1.0 = 
* Enjoy it!
