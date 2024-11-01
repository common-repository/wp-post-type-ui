=== WP Post Type UI ===
Contributors: codework
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=3866418
Tags: custom post types, cms, post, types, type, cck, taxonomy, tax
Requires at least: 3.0
Tested up to: 3.0
Stable tag: 0.5.2

Admin UI for creating dynamic post types and taxonomies in WordPress

== Description ==

Inspired by "Custom Post Type UI" this plugin that gives you what have always wanted for creating dynamic post types and custom taxonomies in WordPress. The UI is made in true WP style, with a smooth integration to give nice and easy access to create and edit post types and taxonomies.

Just install and you are ready to rock... =)

= Features =
 * True WP user interface look and feel
 * Add/Edit/Delete dynamic "Post Types" and "Taxonomies"
 * Search "Post Types" from name and description
 * Search "Taxonomies" from name and "Post Type"
 * Matching labels when creating records
 * Shows post and active taxonomy count
 * Advanced "Post Types" and "Taxonomies" options

== Screenshots ==

Please see the plugin homepage for screenshots.

== ChangeLog ==

= 0.5.2 =

 * Minor bugfix on "Show in Nav Menus"

= 0.5.1 =

 * Feature: You can now choose if permalinks should be prepended with front base.

= 0.5.0 =

 * "Core rewrite". Warnings should be gone now. Thanks to everyone of you who contacted me about this issue. 

= 0.4.2 =

 * Rewrite rules are now flushed, as they where suppose to be
 * "form-required" removed from WP standard html (custom validation will be added later on)

= 0.4.1 =

 * Bugfix on "Warning: in_array() [function.in-array]: Wrong datatype for second argument ... line 232", sorry for the delay on bugfix (thanks to ppl in the forum)

= 0.4.0 =

 * Bugfix "Hierarchical" checkbox fixed (Thanks Nicolas)
 * Feature: Use taxonomy for more than one post type (request by Tammy)
 * Feature: You can now select the standard taxonomies for your post types (request by Michael)
 * Feature: You can now edit the Capability Type of the post

= 0.3.6 =
 * Bugfix on query_var issue. Changed from query-var on line 714 & 171 to query_var (thanks to Nicolas) 

= 0.3.5 =
 * Bugfix on the listing issues. Thanks to all you who bugreported this issue, sorry for the delay on this update.
 
= 0.3.4 =
 * Bugfix "Warning: Invalid argument supplied for foreach() in ....../plugins/wp-post-type-ui/wp-post-type-ui.php on line X" removed from error_log/output by request (thanks to Dimitry).

= 0.3.3 =
 * Added support for post type slug

= 0.3.2 =
 * PHP opening tags ("<?") changed to full ones ("<?php") by request (thanks to johnbillion).

= 0.3.1 =
 * Bugfix on menu_position

= 0.3.0 =
 * Integration of search in both "Post Types" and "Taxonomies"

= 0.2.0 =
 * Ability to create and edit "Taxonomies"

= 0.1.0 =
 * Ability to create and edit "Post Types"

== Installation ==

1. Upload the `wp-post-type-ui` folder to the `/wp-content/plugins/` directory<br />
2. Activate the plugin through the `Plugins` menu in WordPress<br />
3. Navigate to Settings menu, where now will find the `Post Types` and `Taxonomy` menus
