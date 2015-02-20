<?php
/*
Plugin Name: Plugin Profiler
Description: Profile your plugins.
Version: 1.1.2
Author: Danny van Kooten
Author URI: https://dannyvankooten.com
License: GPL v3
GitHub Plugin URI: https://github.com/dannyvankooten/wp-plugin-profiler

Copyright (C) 2014-2015, Danny van Kooten, hi@dannyvankooten.com

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

namespace PluginProfiler;

class Plugin {

	/**
	 * @const string
	 */
	const VERSION = '1.1.2';

	/**
	 * @const string
	 */
	const FILE = __FILE__;

	/**
	 * @const string
	 */
	const DIR = __DIR__;

	/**
	 * @var Plugin
	 */
	private static $instance;

	/**
	 * @var
	 */
	public $profiler;

	/**
	 * @return Plugin
	 */
	public static function instance() {
		if( ! self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Constructor
	 */
	private function __construct() {
		require __DIR__ . '/vendor/autoload.php';

		$this->route();
	}

	/**
	 * Instantiate classes based on the request data.
	 */
	public function route() {


		if( isset( $_SERVER['HTTP_X_PLUGIN_PROFILER_ACTION'] ) && 'profile' === $_SERVER['HTTP_X_PLUGIN_PROFILER_ACTION'] ) {
			new PluginManager();
		}

		if( is_admin() ) {
			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
				new AJAX\Listener();
			} else {
				new Admin\Manager();
			}
		}

	}

}

Plugin::instance();
