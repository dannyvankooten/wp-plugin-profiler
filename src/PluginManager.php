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

			// activate no plugins
			case 'no_plugins':
				return array();
				break;

			// activate only plugin to benchmark
			case 'only_profiled_plugin':
				return array( $this->profiled_plugin_slug );
				break;

			// deactivate profiled plugin for this request
			case 'all_plugins_minus_profiled':
				$key = array_search( $this->profiled_plugin_slug, $plugins );
				if( $key ) {
					unset( $plugins[ $key ] );
				}
				break;

			// make sure tested plugin is activated for this request
			case 'all_plugins':
				if( ! in_array( $this->profiled_plugin_slug, $plugins ) ) {
					$plugins[] = $this->profiled_plugin_slug;
				}

				return $plugins;
				break;

		}

		return $plugins;

	}


}