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
			$this->results[] = $this->profile_step( $step );
		}
	}

	/**
	 * @param $step
	 *
	 * @return array
	 */
	private function profile_step( $step ) {

		// build url
		$data = array(
			'step' => $step,
			'slug' => $this->plugin_slug
		);

		$url = add_query_arg( $data, $this->url );

		$start = microtime( true );

		$response = wp_remote_get( $url,
			array(
				'headers' => array(
					'Accept-Encoding' => '*',
					'X-Plugin-Profiler-Action' => 'profile',
					'X-Plugin-Profiler-Signature' => hash_hmac( 'sha1', build_query( $data ) , AUTH_KEY )
				)
			)
		);

		// return 0 if an error occurred
		if( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) != 200 ) {
			return 0;
		}

		$time = microtime( true ) - $start;

		return round( $time, 3 );
	}

}