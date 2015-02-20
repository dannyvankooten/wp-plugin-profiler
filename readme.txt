=== Plugin Profiler ===
Contributors: DvanKooten
Donate link: https://dannyvankooten.com/donate/
Tags: profiler,benchmark,development,plugins
Requires at least: 3.8
Tested up to: 4.1.1
Stable tag: 1.1.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Basic plugin profiler. Benchmarks response times with and without a given plugin activated.

== Description ==

Basic profiler for WordPress Plugins. Benchmarks any given plugin by testing response times with and without the plugin activated.

> **Plugin Profiler** is on GitHub.
>
> Bug reports (and pull requests) are welcomed on the [Plugin Profiler GitHub repository](https://github.com/dannyvankooten/wp-plugin-profiler).
>
> Please note that GitHub is _not_ a support forum.

= Profiling a plugin =

This plugin measures response times of any URL on your website in the following ways.

- No plugins activated
- Only the selected plugin(s) activated
- All but the selected plugin(s) activated
- All plugins activated

It then plots the response times in a chart and calculates the average response time time difference.

While this way of profiling a plugin is very low-tech it can be interesting to measure the impact of a plugin on your site's response time.
Please note that this way of benchmarking leaves a lot of factors out - like additional HTTP requests caused by a plugin, etc..

= Installing Plugin Profiler =

Since this plugin needs to filter out which plugins are activated for the profiling requests, it needs to be installed as a **must-use plugin** so it's loaded early.

Have a look at the [installation instructions](https://wordpress.org/plugins/plugin-profiler/installation/) for details.

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

== Screenshots ==

1. The plugin in action, profiling [MailChimp for WordPress](https://wordpress.org/plugins/mailchimp-for-wp/).

== Changelog ==

= 1.1.2 - February 20, 2015 =

**Fixes**

- JavaScript error for servers requiring case-sensitive file URL's.

= 1.1.1 - February 5, 2015 =

**Improvements**

- Now marking request errors & deviating requests in result details
- Better signature validating

**Additions**

- Added results table showing all request details


= 1.1 - January 28, 2015 =

**Improvements**

- Profiler now runs client-side.
- Results are shown in a bar chart, which updates live. (needs IE9+)
- Profiling can be paused & resumed.
- Improved reliability of benchmark results.

= 1.0 - January 26, 2015 =
Initial release
