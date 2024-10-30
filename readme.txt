=== Category Text ===
Contributors: isikom
Tags: widget, seo, category, wpmu
Requires at least: 2.7.1
Tested up to: 2.8.4
Stable tag: 1.2.0

category text allows you easily to add a widget for a Category Text-Box.

== Description ==

Category Text add a widget that allow to add arbitrary text or HTML code, different for each category of your blog.
It's very usefull for SEO, because you can make different text for different category, so also different banner etc.
Now you can select also the home page position.

The plugin born from idea of [Vanny Rosso](http://www.rossozingone.it "web agency") for his blog [Fantagiochi](http://www.fantagiochi.it "on line games")

== Installation ==

= Installation =
Istallation it easy, you need to put the plugin, into wordpress plugin directory
At first activation plugins add tables in your database,

1. Upload `plugin-name.php` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Manage your text, with the Category Text administration menu

= Usage =
Manage your text boxes (add/delete/edit) thanks a simple admin menu.

You can create one or more lists that contains your text boxes, it is very usefull create different list for a different sidebars.

To put the text boxes into your, you can add into your sidebar the "Category Text" **widget**, and select the appropriate list.
If your theme don't have a sidebar, you can also add into the template page the code:
`<?php get_ctext_elements(*number-of-list*); ?>`

= Localization =
The plugin is getTexed, the italian localization it's included, if you want traslate the plugin into other language, please send me the translation so i can add it into SVN repository.

== Screenshots ==

http://michele.menciassi.name/en/wordpress-plugins/category-text/3/

== Changelog ==

= 1.2.0 =
Home checkbox added, it allow to show your text also into the home page of blog.
Fix widget bug, as report by ericbellot, the title wasn't showed into a posts.

= 1.1.1 =
Fix widget bug.
Elements with check 'posts' not showed correctly.

= 1.1.0 =
Posts checkbox added, it allow to show your text also into a post of the selected category.
