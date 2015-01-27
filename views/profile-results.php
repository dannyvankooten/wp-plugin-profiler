<?php defined( 'ABSPATH' ) or exit; ?>

<p><?php printf( 'On average, %s added <strong>%s&#37;</strong> to each request to <strong>%s</strong>. This is not taking any additional requests into account, just the time it took to generate the HTML.', $profiler->plugin_name . ' v' . $profiler->plugin_version, round( $profiler->percentage_difference, 2 ), $profiler->url ); ?></p>

<table class="results">
	<thead>
		<tr class="titles">
			<th colspan="2">
				No plugins
			</th>
			<th colspan="2">
				<?php printf( 'Only %s.', $profiler->plugin_name ); ?>
			</th>
			<th colspan="2">
				<?php printf( 'All but %s.', $profiler->plugin_name ); ?>
			</th>
			<th colspan="2">
				<?php printf( 'All active plugins (%s)', $profiler->number_of_active_plugins ); ?>
			</th>
		</tr>
		<tr>
			<?php foreach( $profiler->results as $r ) { ?>
				<th>Description</th>
				<th>Time <em>(s)</em></th>
			<?php } ?>
		</tr>
	</thead>
	<tbody class="totals">
		<tr>
			<?php foreach( $profiler->results as $result ) { ?>
				<th>Avg time</th>
				<td><?php echo $result['average_time']; ?></td>
			<?php } ?>
		</tr>
		<tr>
			<?php foreach( $profiler->results as $result ) { ?>
				<th>Total time</th>
				<td><?php echo $result['total_time']; ?></td>
			<?php } ?>
		</tr>
	</tbody>
	<tbody class="details">
		<tr>
			<th colspan="8">Details</th>
		</tr>
		<?php for( $i = 0; $i < $profiler->number_of_requests; $i++ ) { ?>
			<tr>
				<?php foreach( $profiler->results as $result ) { ?>
					<td>Request <?php echo $i + 1; ?></td>
					<td><?php echo number_format( $result['times'][$i], 4 ); ?></td>
				<?php } ?>
			</tr>
		<?php } ?>
	</tbody>
</table>

<p><?php printf( __( 'Would you like to <a href="%s">run another profile</a> or <a href="%s">run this profile again</a>?', 'plugin-profiler' ), admin_url( 'tools.php?page=plugin-profiler' ), 'javascript:window.location.reload();' ); ?></p>