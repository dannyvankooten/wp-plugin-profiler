<?php

namespace PluginProfiler;

class PluginManager {

	/**
	 * @var int
	 */
	private $step = 'all_plugins';

	/**
	 * @var string
	 */
	private $profiled_plugin_slug = '';

	/**
	 * Constructor
	 */
	public function __construct() {

		// determine current step
		$this->step = ( isset( $_GET['step'] ) ) ? $_GET['step'] : '';
		$this->profiled_plugin_slug = ( isset( $_GET['slug'] ) ) ? $_GET['slug'] : '';

		// add filter to disable or enable plugins
		add_filter( 'option_active_plugins', array( $this, 'filter_active_plugins' ) );
	}

	/**
	 * @param $plugins
	 *
	 * @return array
	 */
	public function filter_active_plugins( $plugins ) {

		switch( $this->step ) {

			// test no plugins
			case 'no_plugins':
				return array();
				break;

			// test only plugin to benchmark
			case 'only_profiled_plugin':
				return array( $this->profiled_plugin_slug );
				break;

			// test all plugins minus the plugin to benchmark
			case 'all_plugins_minus_profiled':
				foreach( $plugins as $key => $plugin_slug ) {
					if( $plugin_slug == $this->profiled_plugin_slug ) {
						die();
						unset( $plugins[$key] );
						return $plugins;
					}
				}
				break;

			// test all (active) plugins
			case 'all_plugins':
				return $plugins;
				break;

		}

		return $plugins;

	}


}