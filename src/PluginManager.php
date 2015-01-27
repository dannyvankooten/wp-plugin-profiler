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

		if( ! $this->is_valid_request() ) {
			return;
		}

		// add filter to disable or enable plugins
		add_filter( 'option_active_plugins', array( $this, 'filter_active_plugins' ) );
	}

	/**
	 * Validates the request using a hash which should be present in the URL.
	 *
	 * @return bool
	 */
	private function is_valid_request() {

		// if secret is not given or empty, bail right away.
		if( ! isset( $_GET['_pp_secret'] ) || '' === $_GET['_pp_secret'] ) {
			return false;
		}

		// get given secret
		$secret = (string) $_GET['_pp_secret'];

		// generate expected secret
		$parameters = array(
			'step' => $this->step,
			'slug' => $this->profiled_plugin_slug
		);
		$expected_secret = hash_hmac( 'sha1', build_query( $parameters ) , AUTH_KEY );

		// compare hashes
		if( function_exists( 'hash_compare' ) ) {
			return hash_compare( $secret, $expected_secret );
		}

		return ( $secret === $expected_secret );
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