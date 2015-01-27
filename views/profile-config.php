<?php defined( 'ABSPATH' ) or exit; ?>
<form method="post">
	<input type="hidden" name="_pp" value="1" />

	<table class="form-table">
		<tr valign="top">
			<th scope="row">
				<?php _e( 'Select the plugin to profile.', 'plugin-profiler' ); ?>
			</th>
			<td>
				<select name="slug" class="regular-text">
					<?php foreach( get_plugins() as $slug => $plugin ) { ?>
						<option value="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $plugin['Name'] ); ?></option>
					<?php } ?>
				</select>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">
				<?php _e( 'URL to benchmark.', 'plugin-profiler' ); ?>
			</th>
			<td>
				<input type="url" name="url" class="regular-text" value="<?php echo esc_attr( $profiler->url ); ?>" />
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">
				<?php _e( 'Number of requests.', 'plugin-profiler' ); ?>
			</th>
			<td>
				<input type="number" name="n" min="1" step="1" value="<?php echo esc_attr( $profiler->number_of_requests ); ?>" />
			</td>
		</tr>
	</table>

	<?php submit_button( __( 'Run Profile', 'plugin-profiler' ) ); ?>
</form>