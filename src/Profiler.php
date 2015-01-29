<?php

namespace PluginProfiler;

class Profiler {


	/**
	 * Slug of the plugin that's being benchmarked
	 *
	 * @var string
	 */
	private $plugin_slug= '';

	/**
	 * The URL to point the requests to
	 *
	 * @var string
	 */
	public $url = '';

	/**
	 * Array of the various steps to benchmark
	 *
	 * @var array
	 */
	private $steps = array(
		'no_plugins',
		'only_slug',
		'all_but_slug',
		'all_plugins'
	);

	/**
	 * Array that holds the profiler results
	 *
	 * @var array
	 */
	public $results = array();

	/**
	 * Constructor
	 */
	public function __construct() {

		if( isset( $_REQUEST['slug'] ) && '' !== $_REQUEST['slug'] ) {
			$this->plugin_slug = $_REQUEST['slug'];
		}

		if( isset( $_REQUEST['url'] ) && '' !== $_REQUEST['url'] ) {
			$this->url = $_REQUEST['url'];
		} else {
			$this->url = home_url();
		}
	}

	/**
	 * Benchmark each step
	 */
	public function run() {

		// allow requests to self
		add_filter( 'http_request_host_is_external', '__return_true' );

		set_time_limit( 0 );

		// fill results
		foreach( $this->steps as $step ) {
			$this->results[ $step ] = $this->profile_step( $step );
		}
	}

	/**
	 * @param $step
	 *
	 * @return array
	 */
	private function profile_step( $step ) {

		// build url
		$url = $this->generate_profile_url( $step );

		$start = microtime( true );

		wp_remote_get( $url,
			array(
				'headers' => array(
					'Accept-Encoding' => '*'
				)
			)
		);

		$time = microtime( true ) - $start;

		return round( $time, 3 );
	}

	/**
	 * Generates the URL to which the profile request is sent.
	 *
	 * @param $step
	 * @return string
	 */
	private function generate_profile_url( $step ) {

		$parameters = array(
			'step' => $step,
			'slug' => $this->plugin_slug
		);

		// generate secret for config parameters
		$parameters['_pp_secret'] = hash_hmac( 'sha1', build_query( $parameters ) , AUTH_KEY );
		$parameters['_pp_profiling'] = 1;

		// return URL with secret parameter in it
		return add_query_arg(  $parameters, $this->url );
	}


}