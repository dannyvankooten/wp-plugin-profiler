<?php

use PluginProfiler\Plugin;

defined( 'ABSPATH' ) or exit; ?>
<div class="wrap" id="profiler">

	<h1>Plugin Profiler</h1>

	<?php if( ! $this->is_mu_plugin ) { ?>
	<div class="error">
		<p>
			<?php printf( __( 'Since Plugin Profiler needs control over which plugins are loaded during the benchmark requests, it needs to be installed as a <code>mu-plugin</code> for it to work.', 'plugin-profiler' ) ); ?>
			<?php printf( __( 'Have a look at the <a href="%s">installation instructions</a> for some more detailed instructions.', 'plugin-profiler' ), 'https://wordpress.org/plugins/plugin-profiler/installation/' ); ?>
		</p>
	</div>
	<?php } ?>

	<div id="profiler-mount"></div>


</div>