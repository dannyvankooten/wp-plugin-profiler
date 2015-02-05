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

		// check if request has the correct signature
		if( ! $this->is_valid_request() ) {
			http_response_code( 403 );
			exit;
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
		if( ! isset( $_SERVER['HTTP_X_PLUGIN_PROFILER_SIGNATURE'] ) || '' === $_GET['HTTP_X_PLUGIN_PROFILER_SIGNATURE'] ) {
			return false;
		}

		// get given secret
		$given_secret = (string) $_SERVER['HTTP_X_PLUGIN_PROFILER_SIGNATURE'];

		// generate expected secret
		$parameters = array(
			'step' => $this->step,
			'slug' => $this->profiled_plugin_slug
		);
		$expected_secret = hash_hmac( 'sha1', build_query( $parameters ) , AUTH_KEY );

		// compare hashes
		if( function_exists( 'hash_equals' ) ) {
			return hash_equals( $expected_secret, $given_secret );
		}

		return ( $given_secret === $expected_secret );
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
			case 'only_slug':
				return array( $this->profiled_plugin_slug );
				break;

			// deactivate profiled plugin for this request
			case 'all_but_slug':
				$key = array_search( $this->profiled_plugin_slug, $plugins );
				if( $key ) {
					unset( $plugins[ $key ] );
				}
				return $plugins;
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