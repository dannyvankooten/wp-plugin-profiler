<?php

namespace PluginProfiler;

class Profiler {

	/**
	 * @var int
	 */
	private $number_of_requests = 10;

	/**
	 * @var int
	 */
	private $number_of_active_plugins = 0;

	/**
	 * @var string
	 */
	private $plugin_slug = '';

	/**
	 * @var string
	 */
	private $plugin_name = '';

	/**
	 * @var
	 */
	private $plugin_version = '';

	/**
	 * @var string
	 */
	private $url = '';

	/**
	 * @var array
	 */
	private $steps = array(
		'no_plugins',
		'only_profiled_plugin',
		'all_plugins_minus_profiled',
		'all_plugins'
	);

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'init' ) );
	}

	/**
	 * @return bool
	 */
	public function init() {

		// only run for authorized users
		if( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		if( isset( $_GET['n'] ) ) {
			$this->number_of_requests = absint( $_GET['n'] );
		}

		if( isset( $_GET['slug'] ) ) {
			$this->plugin_slug = $_GET['slug'];
		} else {
			_doing_it_wrong( 'Profiler::init', 'You should provide a plugin slug via the URL `slug` parameter.', Plugin::VERSION );
			return false;
		}

		$this->url = ( isset( $_GET['url'] ) ) ? $_GET['url'] : home_url();

		// count number of active plugins
		$this->number_of_active_plugins = count( get_option('active_plugins') );

		// get plugin name & version
		if( ! function_exists( 'get_plugin_data' ) ) {
			require_once ABSPATH . '/wp-admin/includes/plugin.php';
		}

		$data = get_plugin_data( WP_PLUGIN_DIR . '/' . $this->plugin_slug );
		$this->plugin_name = $data['Name'];
		$this->plugin_version = $data['Version'];

		// run benchmark
		$this->run_profile();

		return true;
	}

	/**
	 * Benchmark each step
	 */
	public function run_profile() {

		set_time_limit( 0 );

		$results = array();
		foreach( $this->steps as $step ) {
			$results[ $step ] = $this->profile_step( $step );
		}

		?>
		<style>

			h2,
			p,
			table {
				font-family: Verdana, sans-serif;
			}

			table {
				border-collapse: collapse;
			}

			th.name {
				text-align: center;
				padding: 12px 24px;
				font-size: 16px;
			}

			th,
			td {
				text-align: left;
				border: 1px solid #efefef;
				padding: 6px 12px;
			}

			tr.total th,
			tr.total td {
				border-top: 2px solid black;
			}
		</style>

		<table>
			<tr>
				<th colspan="2" class="name">
					No plugins
				</th>
				<th colspan="2" class="name">
					<?php printf( 'Only %s.', $this->plugin_name ); ?>
				</th>
				<th colspan="2" class="name">
					<?php printf( 'All active plugins, minus %s.', $this->plugin_name ); ?>
				</th>
				<th colspan="2" class="name">
					<?php printf( 'All active plugins (%s)', $this->number_of_active_plugins ); ?>
				</th>
			</tr>
			<tr>
				<?php foreach( $results as $r ) { ?>
					<th>Description</th>
					<th>Time <em>(seconds)</em></th>
				<?php } ?>
			</tr>
			<?php for( $i = 0; $i < $this->number_of_requests; $i++ ) { ?>
				<tr>
					<?php foreach( $results as $result ) { ?>
						<td>Request <?php echo $i + 1; ?></td>
						<td><?php echo number_format( $result['times'][$i], 4 ); ?></td>
					<?php } ?>
				</tr>
			<?php } ?>
			<tr class="total">
				<?php foreach( $results as $result ) { ?>
					<th>Total time</th>
					<td><?php echo $result['total_time']; ?></td>
				<?php } ?>
			</tr>
			<tr class="total">
				<?php foreach( $results as $result ) { ?>
					<th>Avg time</th>
					<td><?php echo $result['average_time']; ?></td>
				<?php } ?>
			</tr>
		</table>
		<?php
		$avg_percentage = ( ( $results['only_profiled_plugin']['average_time'] / $results['no_plugins']['average_time'] ) + ( $results['all_plugins']['average_time'] / $results['all_plugins_minus_profiled']['average_time'] ) ) / 2 * 100 - 100;
		$avg_seconds = ( ( $results['only_profiled_plugin']['average_time'] - $results['no_plugins']['average_time'] ) + ( $results['all_plugins']['average_time'] - $results['all_plugins_minus_profiled']['average_time'] ) ) / 2;
		?>
		<p><?php printf( 'On average, %s added <strong>%s&#37;</strong> (%s seconds) for each request to <strong>%s</strong>. This is not taking any additional requests into account, just the time it took to generate the HTML.', $this->plugin_name . ' v' . $this->plugin_version, round( $avg_percentage, 2 ), round( $avg_seconds, 4 ), $this->url ); ?></p>
		<?php

		exit; // todo: make this pretty
	}

	/**
	 * @param $step
	 *
	 * @return array
	 */
	public function profile_step( $step ) {

		// array of times
		$times = array();

		// build url
		$url = add_query_arg( array( '_pp_profiling' => 1, 'step' => $step, 'slug' => $this->plugin_slug ), $this->url );

		// test each step X times
		for( $i = 0; $i < $this->number_of_requests; $i++ ) {
			$start = microtime( true );
			wp_remote_get( $url );
			$times[] = microtime( true ) - $start;
		}

		// total time
		$total_time = array_sum( $times );

		// calculate average time
		$average_time = $total_time / count( $times );

		return array(
			'times' => $times,
			'average_time' => number_format( $average_time, 4 ),
			'total_time' => number_format( $total_time, 4 )
		);
	}


}