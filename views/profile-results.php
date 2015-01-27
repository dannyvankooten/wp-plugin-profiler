<?php defined( 'ABSPATH' ) or exit; ?>

<div id="profile-results"></div>

<p><?php printf( __( 'Would you like to <a href="%s">run another profile</a> or <a href="%s">run this profile again</a>?', 'plugin-profiler' ), admin_url( 'tools.php?page=plugin-profiler' ), 'javascript:window.location.reload();' ); ?></p>