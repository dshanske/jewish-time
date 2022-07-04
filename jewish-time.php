<?php
/**
 * Plugin Name: Jewish Time
 * Plugin URI: https://github.com/dshanske/tempus-fugit
 * Description: Hebrew/Jewish Time Enhancements for WordPress
 * Author: David Shanske
 * Author URI: https://david.shanske.com
 * Text Domain: jewish-time
 * Version: 0.0.1
 */

register_activation_hook( __FILE__, array( 'Jewish_Time_Plugin', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Jewish_Time_Plugin', 'deactivate' ) );
add_action( 'upgrader_process_complete', array( 'Jewish_Time_Plugin', 'upgrader_process_complete' ), 10, 2 );

add_action( 'plugins_loaded', array( 'Jewish_Time_Plugin', 'plugins_loaded' ) );
add_action( 'init', array( 'Jewish_Time_Plugin', 'init' ) );

class Jewish_Time_Plugin {

	public static function plugins_loaded() {
		require_once plugin_dir_path( __FILE__ ) . '/includes/class-hebrew-date.php';
	}

	public static function init() {
	}

	public static function upgrader_process_complete( $upgrade_object, $options ) {
		$current_plugin_path_name = plugin_basename( __FILE__ );
		if ( ( 'update' === $options['action'] ) && ( 'plugin' === $options['type'] ) ) {
			foreach ( $options['plugins'] as $each_plugin ) {
				if ( $each_plugin === $current_plugin_path_name ) {
					flush_rewrite_rules();
				}
			}
		}
	}

	public static function activate() {
		flush_rewrite_rules();
	}

	public static function deactivate() {
		flush_rewrite_rules();
	}
}


