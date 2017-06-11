=== Plugin Name ===
Contributors: chaozh
Donate link: http://www.chaozh.com/simple-post-series-plugin-officially-publish/
Tags: category, post, series, taxonomy
Tested up to: 4.8
Stable tag: 2.4
Requires at least: 3.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Simple Post Series实现按照自定义系列专题的形式管理文章并展示的功能，实现可视化拖拽、添加、小工具等功能！

== Description ==

**Simple Post Series** 文章专题插件，也叫文章系列插件，集所有类似插件功能之所长！你可以创建专题（有点像自定义的分类），将文章添加到该专题（支持拖放添加、排序哦！），然后使用简码插入到文章中，或使用小工具展示。专题内的文章都可以在后台统一管理，你无需再次修改文章。支持在可视化下一键插入某个专题的文章……更多功能与用法都等你发现！！

* 可以在文章编辑页面可视化添加简码以展示某个专题下的文章列表，除文章与页面两种内建类型外，最新2.2版开始支持自定义类型了哦；
* 可以设置自动在文章页或主页上显示某个专题下的文章列表，除去你每篇文章修改的麻烦，甚至可以设置展示摘要或缩略图；
* 可以在管理员文章管理页面上按照某个专题进行筛选，文章管理比原来更加方便；
* 从2.0版开始支持文章可视化拖拽添加到专题的功能，甚至可以改变专题下文章的显示排序哦。

可以在这个github项目中找到最新更新的beta版代码！欢迎fork/star该项目，也欢迎提出各种改进意见或使用中发现的问题。

欢迎加入**官方QQ群：297937473** 来反馈问题和交流讨论

**simple-post-series** allows you to insert series short code for displaying a bunch of posts in the same serie.
This plugin allows to include posts into series, to create, delete or rename series. It also includes *widgets* and *shortcodes* to display list of series, or the list of posts belonging to the series of the current post.

With this plugin, you can:

* Add / Delete a post from a serie with simple drag & drop move,
* Display the list of series in a post, or display this list in sidebar, with widgets for exemple,
* Filter posts by a special serie in your admin post managent page,
* Automatically insert the list of posts of a specific serie, without using shortcode.
* TinyMCE Integrated. Don't need to learn the shortcode syntax. The plugin add a button in the tinymce toolbar. You just have to click on this button, choose parameters/options, and click insert. That's all, the shortcode will be insert into your post with the right parameters.

You can find latest beta source code in [github project](https://github.com/chaozh/Post-Series).

This plugin is enlighted by Tuts+ and their Sessions. You can find a very helpful [cource in NetTuts+](http://wp.tutsplus.com/tutorials/plugins/adding-post-series-functionality-to-wordpress-with-taxonomies/) to figure out how this plugin works in Wordpress.

== Installation ==

安装：
1. 解压`simple-post-series.zip` 到同名文件夹；Uzip the `simple-post-series.zip` folder.
2. 上传`simple-post-series` 文件夹到你的`/wp-content/plugins`文件夹下面；Upload the `simple-post-series` folder to your `/wp-content/plugins` directory.
3. 在你的wordpress的插件管理面板中启用 *Post Series* 插件；In your WordPress dashboard, head over to the *Plugins* section.
4. 访问“设置 - Post series专题”可以自己设置各种展示方法。Activate *Post Series*.

== Screenshots ==

1. 加入专题设置模块, Post Editing page: TinyMCE integration and additional metabox to quickly choose or add a serie 
2. 页面展示专题列表, List of posts in a serie 
3. 拖拽管理专题下所属文章, Drag and drop easily managing series 
4. 文章编辑方便插入简码, TinyMCE window to choose shortcodes options, and insert shortcodes
5. 插入简码的结果, Insert short code in edit post 
6. 插件设置页面, Options page 

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

== Changelog ==
= 2.4 =
* New: Add support for listing series
* New: Fix two bugs

= 2.3 =
* New: Add autohide option  
* New: Navigator style improve
* New: Fix series order bugs 
* New: Improve excerpt & thumbnail display

= 2.2.1 =
* New: Add supporting QQ group url

= 2.1 =
* New: Fix bugs and improve code robustion

= 2.0 =
* New: Add admin series bulk edition page for drag and drop posts to serie

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