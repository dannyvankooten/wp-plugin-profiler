<?php

namespace PluginProfiler\AJAX;

use PluginProfiler\Profiler;

class Listener {

	/**
	 *
	 */
	public function __construct() {
		add_action( 'wp_ajax_plugin_profiler', array( $this, 'respond' ) );
	}

	/**
	 *
	 */
	public function respond() {

		$profiler = new Profiler();
		$profiler->run();

		wp_send_json( $profiler->results );
		exit;
	}

}