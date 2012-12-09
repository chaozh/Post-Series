=== Plugin Name ===
Contributors: chaozh
Donate link: http://www.chaozh.com/wordpress-plugin-post-series-publish/
Tags: post, category, series, taxonomy
Tested up to: 3.5
Stable tag: 1.3
Requires at least: 3.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Simple Post Series allows you to organize your posts and insert short code for displaying a bunch of posts within a serie.
为Wordpress增加按照专题管理文章并展示的功能，集所有类似插件功能之所长，设置选项完善。

== Description ==

**simple-post-series** allows you to insert series short code for displaying a bunch of posts in the same serie.
This plugin allows to include posts into series, to create, delete or rename series. It also includes *widgets* and *shortcodes* to display list of series, or the list of posts belonging to the series of the current post.

With this plugin, you can:

* Add / Delete a post from a serie,
* Create / Rename / Delete series,
* Display the list of series in a post, or display this list in sidebar, with widgets for exemple,
* Filter posts by a special serie in your admin post managent page,
* Automatically insert the list of posts of a specific serie, without using shortcode.

**simple-post-series** is *TinyMCE Integrated*. That means you don't need to learn the shortcode syntax. The plugin add a button in the tinymce toolbar. You just have to click on this button, choose parameters/options, and click insert. That's all, the shortcode will be insert into your post with the right parameters.

You can find latest source code in [github project](https://github.com/chaozh/Post-Series).

This plugin is enlighted by Tuts+ and their Sessions. You can find a very helpful [cource in NetTuts+](http://wp.tutsplus.com/tutorials/plugins/adding-post-series-functionality-to-wordpress-with-taxonomies/) to figure out how this plugin works in Wordpress.

融合所有类似插件的相关功能：

* 可以在文章编辑页面通过按钮与面板添加shortcode以展示某个专题下的文章列表，支持文章与页面，
* 可以设置自动在文章页或主页上显示某个专题下的文章列表，可以设置展示摘要或缩略图，
* 可以在管理员文章管理页面上对某个专题进行筛选，文章管理更加方便。

== Installation ==

1. Uzip the `simple-post-series.zip` folder.
2. Upload the `simple-post-series` folder to your `/wp-content/plugins` directory.
3. In your WordPress dashboard, head over to the *Plugins* section.
4. Activate *Post Series*.

== Usage ==

**simple post series** adds two administration pages, a widget and shortcode.

= Customize your theme =

simple-post-series uses now a specific taxonomy to implement series. It means 

* The link to series can be: http://host/path/series/[Name of the serie]
* You can build / customize a specific page in your theme to display the content of a serie.

You can customize your theme by creating page like 'taxonomy-series.php' which overrides default archive page to display the content of a serie.
A sample archive page template is located in '[simple-post-series]/template/taxonomy-series.php' and you can freely modified it and place in your own theme.

= Administration pages =

* **Series** gives ability to change or rename series,
* **Settings/Post Series**  contains all options of the plugin. In this page, you can activate the **auto display** feature.

= Shortcodes = 

* To display the list of series: [series *options*], with the following options:
	* **id** of the series. Default '',
	* **slug** of the series. Default '',
	* **title** of the list. Default '',
	* **limit**: number of series to display in the list. Default: -1 to display all series,
	* **show_future**: displays unpublish posts in the series. Default: on,
	* **class_prefix**: of the list section. Default 'post-series'.

== Screenshots ==

1. Post Editing page: TinyMCE integration and additional metabox to quickly choose or add a serie,
2. TinyMCE window to choose shortcodes options, and insert shortcodes,
3. Manage easily series,
4. Options page,
5. List of posts in a serie.

== Changelog ==

= 1.3 =

* New: Add option to choose where to display lists
* New: Add option to choose to display excerpt
* New: Add option to choose to display thumbnail

= 1.2 =

* New: Add series filter in admin's edit.php
* New: optional load of the stylesheet 
* New: sample archive page for displaying series taxonomy in custom theme

= 1.1 =

* New: add widget for post series with many options

= 1.0 =

* Plugin released.  Everything is new! Have to change name from post-series to simple-post-series.