=== Plugin Name ===
Contributors: rtpHarry
Tags: admin, developer
Requires at least: 4.0
Tested up to: 4.9
Stable tag: 1.1
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.txt

A developer admin plugin that lets you bulk create page stubs by providing a batch of page titles and slugs.

== Description ==

This is a developer plugin for WordPress developers to ease their initial content setup process.

It is a simple plugin; it gives you a text input area. On the first line you type the page title, on the second line you type the slug. Then you repeat the process for as many pages that you want to create.

When you press the `Create page stubs` button it will automatically create all of the stubs for you.

== Installation ==

If you are installing through the admin panel just search "Bulk Page Stub Creator" and click the install button.

If you are downloading a .zip file then just extract the folder to the `/wp-content/plugins/` directory of your site.

On the install completed page you will need to click `activate` to turn the plugin on.

Once installed you will find the plugin in the admin panel, in the `Tools` menu, under `Bulk Page Stub Creator`.

== Frequently Asked Questions ==

= I don't want to specify the slug (url) for each page =

If you leave every second line blank then WordPress will automatically generate the slug based on the title of your page.

= I want to bulk create a different posttype, is that possible? =

Not at the moment. But its really simple for you to change the block of code that creates the pages. It's just a simple
call to one of the WordPress functions called `wp_insert_post()`. The same function is used to create all posttypes by
simply altering the `post_type` parameter.

You can find documentation for the `wp_insert_post()` function in the official codex at:

* http://codex.wordpress.org/Function_Reference/wp_insert_post
 
You can find the code within the plugin in the `includes/process-data.php` file under `bpsc_bulk_create_pages()`.

= I have some other great idea for the plugin =

Great! Open an issue on https://github.com/rtpHarry/BulkPageStubCreator-WordPress/issues and lets discuss it.

== Who This Is Aimed At ==

This plugin is for website developers that want to speed up their initial content setup process.

It is a really simple plugin by design. I wrote it to help speed up my own website development process.

I can see some ideas as to how this could be expanded and if you find this useful I’m open to suggestions on the GitHub Issues page.

== How It Fits Into My Workflow ==

First I plan out the structure of my site in a sitemap document. This document contains the page names and the slugs.

Then I create another document from this which details all of the page meta like, descriptions, titles, headers, contents text, footer tags and any other optimisations I want to add.

This plugin lets me quickly process my sitemap document into real page stubs on the website, complete with the slugs I want.

The results page is then shown which has edit page links for all of the new pages.

This allows you to easily open up each tab in the order I’ve planned out (not alphabetically sorted like WordPress defaults) and insert the remaining meta to the pages.

If this sounds like your workflow then this plugin could save you time!

== Screenshots ==

1. The main bulk page stub creator admin page
2. The results of a bulk page stub creation
3. If WordPress finds that the requested slugs either were invalid or already in use it automatically renames the slug to a non-conflicting name.

== Changelog ==

= 1.1 =
* 2017-11-21
* Update WordPress supported version to 4.9
* Fix typos in documentation
* Fix unitialized variable errors shown when WP_DEBUG is on

= 1.0 =
* 2014-10-01
* Initial release