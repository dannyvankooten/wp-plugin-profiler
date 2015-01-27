<?php

namespace PluginProfiler;

class Profiler {

	/**
	 * Number of requests to make during the benchmark
	 *
	 * @var int
	 */
	public $number_of_requests = 10;

	/**
	 * @var int
	 */
	public $number_of_active_plugins = 0;

	/**
	 * Slug of the plugin that's being benchmarked
	 *
	 * @var string
	 */
	private $plugin_slug = '';

	/**
	 * @var string
	 */
	public $plugin_name = '';

	/**
	 * Version of the plugin that is being benchmarked
	 *
	 * @var
	 */
	public $plugin_version = '';

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
	 * @var float
	 */
	public $percentage_difference = 0;

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

		set_time_limit( 0 );

		//$this->get_info();

		// fill results
		foreach( $this->steps as $step ) {
			$this->results[ $step ] = $this->profile_step( $step );
		}

		// calculate percentage difference between steps
		//$this->percentage_difference = $this->calculate_percentage_difference( $this->results );
	}

	/**
	 * Get some info about the environment we're running in.
	 */
	private function get_info() {

		// count number of active plugins
		$this->number_of_active_plugins = count( get_option('active_plugins') );

		// get plugin name & version
		if( ! function_exists( 'get_plugin_data' ) ) {
			require_once ABSPATH . '/wp-admin/includes/plugin.php';
		}

		$data = get_plugin_data( WP_PLUGIN_DIR . '/' . $this->plugin_slug );
		$this->plugin_name = $data['Name'];
		$this->plugin_version = $data['Version'];
	}

	/**
	 * @param $step
	 *
	 * @return array
	 */
	private function profile_step( $step ) {

		// array of times
		$times = array();

		// build url
		$url = $this->generate_profile_url( $step );

		// test each step X times
		//for( $i = 0; $i < $this->number_of_requests; $i++ ) {
			$start = microtime( true );

			wp_remote_get( $url,
				array(
					'headers' => array(
						'Accept-Encoding' => '*'
					)
				)
			);

			$time = microtime( true ) - $start;
		//}

		// total time
		//$total_time = array_sum( $times );

		// calculate average time
		//$average_time = $total_time / count( $times );
		return round( $time, 3 );

//		return array(
//			'times' => $times,
//			'average_time' => number_format( $average_time, 4 ),
//			'total_time' => number_format( $total_time, 4 )
//		);
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

	/**
	 * @param $results
	 *
	 * @return float
	 */
	private function calculate_percentage_difference( $results ) {

		$percentage_difference = ( ( $results['only_profiled_plugin']['average_time'] / $results['no_plugins']['average_time'] )
		                           + ( $results['all_plugins']['average_time'] / $results['all_plugins_minus_profiled']['average_time'] ) )
		                            / 2 * 100 - 100;

		if( $percentage_difference < 0 ) {
			return 0;
		}

		return round( $percentage_difference, 2 );
	}


}