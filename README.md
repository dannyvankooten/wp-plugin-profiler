WordPress Plugin Profiler
==========================

Very basic profiler for WordPress Plugins. Benchmarks a given plugin by testing response times with and without the tested plugin activated.

Usage
-----

Since this plugin needs to have control over the plugins which are loaded, it needs to be in your `mu-plugins` folder. 

```php
// load the plugin profiler plugin early
require_once WP_PLUGIN_DIR . '/plugin-profiler/plugin-profiler.php';
```

To run a profile on a given plugin, visit the following URL.

`
/wp-admin/?_pp=1&slug=woocommerce/woocommerce.php&n=20&url=http://local.wp/about/
`

Here's a rundown of the accepted parameters.

| URL parameter | Description           |
| ------------- |:-------------:|
| _pp      		| Set to `1` to run a profile | 
| slug     		| The full file slug of the plugin you want to profile      |
| n 			| The number of times the profile should run for each step. (default=10)     |
| url			| The URL which should be profiled against.	(default=homepage)|