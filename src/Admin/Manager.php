<?php

namespace PluginProfiler\Admin;

use PluginProfiler\Plugin,
	PluginProfiler\Profiler,
	Pimple\Container;

class Manager {

	const ACCESS_CAP = 'manage_options';

	/**
	 * @var
	 */
	private $container;

	/**
	 * @var bool
	 */
	private $is_mu_plugin = false;

	/**
	 * Constructor
	 */
	public function __construct( ) {

		$this->is_mu_plugin = ! did_action( 'muplugins_loaded' );

		$this->container = new Container();
		$this->container['profiler'] = function( $c ) {
			return new Profiler();
		};

		add_action( 'admin_init', array( $this, 'init' ) );
		add_action( 'admin_menu', array( $this, 'menu' ) );
	}

	/**
	 * @return bool
	 */
	public function init() {

		// only run for administrators
		if( ! current_user_can( self::ACCESS_CAP ) ) {
			return false;
		}

		add_filter( 'admin_enqueue_scripts', array( $this, 'load_assets' ) );
	}

	/**
	 * Register menu pages
	 */
	public function menu() {
		add_management_page( __( 'Plugin Profiler', 'plugin-profiler' ), __( 'Plugin Profiler', 'plugin-profiler' ), self::ACCESS_CAP, 'plugin-profiler', array( $this, 'show_settings_page' ) );
	}

	/**
	 * Load assets if we're on the settings page of this plugin
	 *
	 * @return bool
	 */
	public function load_assets() {

		if( ! isset( $_GET['page'] ) || $_GET['page'] !== 'plugin-profiler' ) {
			return false;
		}

		$min = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		wp_enqueue_style( 'plugin-profiler-admin', $this->asset_url( "/css/admin{$min}.css" ) );

		wp_enqueue_script( 'es5-polyfill', 'https://cdnjs.cloudflare.com/ajax/libs/es5-shim/4.0.3/es5-shim.min.js' );
		wp_enqueue_script( 'mithril', $this->asset_url( "/js/third-party/mithril{$min}.js" ), array(), Plugin::VERSION, true );
		wp_enqueue_script( 'chart', $this->asset_url( "/js/third-party/Chart{$min}.js" ), array(), Plugin::VERSION, true );
		wp_enqueue_script( 'plugin-profiler-admin', $this->asset_url( "/js/admin{$min}.js" ), array( 'mithril', 'chart' ), Plugin::VERSION, true );

		$profiler = $this->container['profiler'];
		$data = array(
			'config' => array(
				'url' => $profiler->url,
				'plugins' => array_map( function( $a ) { return sprintf( '%s v%s', $a['Name'], $a['Version'] ); }, get_plugins() )
			),
			'ajaxurl' => admin_url( 'admin-ajax.php' )
		);
		wp_localize_script( 'plugin-profiler-admin', 'wpp', $data );

		return true;
	}

	/**
	 * Outputs the settings page
	 */
	public function show_settings_page() {
		$profiler = $this->container['profiler'];
		require Plugin::DIR . '/views/profile.php';
	}

	/**
	 * Helper function, prints out URL to `/assets` directory in plugin folder.
	 *
	 * @param $url
	 *
	 * @return string
	 */
	protected function asset_url( $url ) {
		return plugins_url( '/assets' . $url, Plugin::FILE );
	}





}