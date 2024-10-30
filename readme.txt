=== KeyWord Collector ===
Contributors: adsimple
Donate link: http://www.adsimple.at/keyword-collector-wordpress-plugin/
Tags: keyword, tag, shortcode, autoinsert
Requires at least: 4.6
Tested up to: 4.7.3
Stable tag: 4.7
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Collects keywords for single URLs via SISTRIX API (API key needed) and displays them in a flexible manner on the very same single URL

== Description ==

KeyWord Collector is a WordPress plugin which allows to store keywords obtains via SISTRIX API (API key necessary) for a single post, page or custom post type. It allows to output the collected keywords with the help of the shortcode ([keywords_collector]) and/or store those keywords as post terms.

Major features in KeyWord Collector include:

* Shortcode autoinsert into each post/page right after the content
* Ability to edit the keywords wrapper HTML
* Auto-add the keywords to post or page tags
* Set the number of stored and displayed keywords

== Installation ==

To install the plugin you need to make the following steps:

1. Upload the plugin files to the `/wp-content/plugins/keyword-collector` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Use the Settings->Keyword Collector screen to configure the plugin.

== Settings Description ==

Here's the detailed description of the plugin settings:

* HTML before the list - HTML code which is inserted before the whole keywords list.
* HTML after the list - HTML code which is inserted after the whole keywords list.
* HTML before each item - HTML code which is inserted before each keyword.
* HTML after each items - HTML code which is inserted after each keyword.
* Item count - the amount of keywords which will be displayed.  
* SISTRIX API Key - API key (can be received on the site https://www.sistrix.com/).
* Update interval (days) - interval for automatic keywords update.
* Delete interval (days) - interval which controls how many days the keywords are kept before deletion. Prevents API usage for URLs which don’t use shortcode.
* Country shortcode - the country code (can be received on the site https://www.sistrix.com/).
* Auto Insert - the shortcode autoinsert right after content of posts/pages.
* Add keywords to post/page tags - the keywords autoinsert the keywords as post/page tags which means you get an ever growing list of tags for posts/pages.

== Changelog ==

For more information, see [Releases](http://www.adsimple.at/keyword-collector-wordpress-plugin/).

