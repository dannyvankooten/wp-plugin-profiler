<?php

use PluginProfiler\Plugin;

defined( 'ABSPATH' ) or exit; ?>
<div class="wrap" id="profiler">

	<h1>Plugin Profiler</h1>

	<?php
	if( empty( $profiler->results ) ) {
		require __DIR__ . '/profile-config.php';
	} else {
		require __DIR__ . '/profile-results.php';
	}
	?>

</div>