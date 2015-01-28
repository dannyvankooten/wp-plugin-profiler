=== Plugin Profiler ===
Contributors: DvanKooten
Donate link: https://dannyvankooten.com/donate/
Tags: profiler,benchmark,development,plugins
Requires at least: 3.8
Tested up to: 4.1.1
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Basic profiler for your plugins.

== Description ==

Basic profiler for WordPress Plugins. Benchmarks any given plugin by testing response times with and without the plugin activated.

= Development of Plugin Profiler =

Bug reports (and Pull Requests) for [Plugin Profiler are welcomed on GitHub](https://github.com/dannyvankooten/wp-plugin-profiler). Please note that GitHub is _not_ a support forum.

**More information**

- Developers; follow or contribute to the [Plugin Profiler plugin on GitHub](https://github.com/dannyvankooten/wp-plugin-profiler)
- Other [WordPress plugins](https://dannyvankooten.com/wordpress-plugins/#utm_source=wp-plugin-repo&utm_medium=link&utm_campaign=more-info-link) by [Danny van Kooten](https://dannyvankooten.com#utm_source=wp-plugin-repo&utm_medium=link&utm_campaign=more-info-link)
- [@DannyvanKooten](https://twitter.com/dannyvankooten) on Twitter

== Installation ==

= Installing Plugin Profiler =

1. In your WordPress admin panel, go to *Plugins > New Plugin*, search for **Plugin Profiler** and click "*Install now*"
2. Alternatively, download the plugin and upload the contents of `plugin-profiler.zip` to your plugins directory, which usually is `/wp-content/plugins/`.
3. Create the file `/wp-content/mu-plugins/profiler.php` with the following contents.
`
<?php

if( ! defined( 'ABSPATH' ) ) {
	exit;
}

// load the plugin profiler plugin early
require_once WP_PLUGIN_DIR . '/plugin-profiler/plugin-profiler.php';
`
4. Go to **Tools > Plugin Profiler**, set some configurations for the profile to run and have a look!

== Frequently Asked Questions ==

= I think I found a bug. What now? =

Please report it on [GitHub issues](https://github.com/dannyvankooten/wp-plugin-profiler/issues) if it's not in the list of known issues.

= I have another question =

Please open a topic on the [WordPress.org plugin support forums](https://wordpress.org/support/plugin/plugin-profiler).

== Changelog ==

= 1.0 - January 28, 2015 =
Initial release
