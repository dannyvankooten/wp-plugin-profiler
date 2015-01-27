<?php

use PluginProfiler\Plugin;

defined( 'ABSPATH' ) or exit; ?>
<div class="wrap" id="profiler">

	<h1>Plugin Profiler</h1>

	<?php

	if( ! $this->is_mu_plugin ) { ?>
	<div class="error">
		<p><?php printf( __( 'Since Plugin Profiler needs control over which plugins are loaded during the benchmark requests, it needs to be installed as a <code>mu-plugin</code> for it to work.', 'plugin-profiler' ) ); ?></p>
	</div>
	<?php
	}

	if( empty( $profiler->results ) ) {
		require __DIR__ . '/profile-config.php';
	} else {
		require __DIR__ . '/profile-results.php';
	}
	?>

</div>