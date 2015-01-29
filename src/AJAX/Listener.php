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

		// clear output, some plugins might have thrown errors by now.
		if( ob_get_level() > 0 ) {
			ob_end_clean();
		}

		wp_send_json( $profiler->results );
		exit;
	}

}