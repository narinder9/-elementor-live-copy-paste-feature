<?php
/**
 * Plugin Name: Copy Paste Addon For Timeline Widget
 * Description: Best timeline widget for Elementor page builder to showcase your personal or business stories in beautiful vertical or horizontal timeline layouts with many preset styles. <strong>[Elementor Addon]</strong>
 * Plugin URI:  https://cooltimeline.com
 * Version:     1.0
 * Author:      Cool Plugins
 * Author URI:  https://coolplugins.net
 * Text Domain: ccpd
 * Elementor tested up to: 3.6.8
 * Elementor Pro tested up to: 3.7.8
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( defined( 'CCPD_VERSION' ) ) {
	return;
}
define( 'CCPD_VERSION', '1.0' );
define( 'CCPD__FILE', __FILE__ );
define( 'CCPD_DIR_PATH', plugin_dir_path( CCPD__FILE ) );
define( 'CCPD_DIR_URL', plugin_dir_url( CCPD__FILE ) );
register_activation_hook( CCPD__FILE, array( 'Cool_Copy_Paste_Addon', 'ccpd_pro_activate' ) );
register_deactivation_hook( CCPD__FILE, array( 'Cool_Copy_Paste_Addon', 'ccpd_pro_deactivate' ) );
/**
 * Class Cool_Copy_Paste_Addon
 */
final class Cool_Copy_Paste_Addon {
	/**
	 * Plugin instance.
	 *
	 * @var Cool_Copy_Paste_Addon
	 * @access private
	 */
	private static $instance = null;
	/**
	 * Get plugin instance.
	 *
	 * @return Cool_Copy_Paste_Addon
	 * @static
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	/**
	 * Constructor.
	 *
	 * @access private
	 */
	private function __construct() {
		add_action( 'plugins_loaded', array( $this, 'ccpd_load_addon' ) );
	}
	function ccpd_load_addon() {
		// Load plugin file
		require_once CCPD_DIR_PATH . '/includes/ccpd-main.php';
		// Run the plugin
		CCPD_Main::instance();
	}
	/**
	 * Run when activate plugin.
	 */
	public static function ccpd_pro_activate() {
		update_option( 'ccpd-type', 'PRO' );
		update_option( 'ccpd_activation_time', date( 'Y-m-d h:i:s' ) );
		update_option( 'ccpd-pro-v', CCPD_VERSION );
	}
	/**
	 * Run when deactivate plugin.
	 */
	public static function ccpd_pro_deactivate() {
	}
}
function Cool_Copy_Paste_Addon() {
	return Cool_Copy_Paste_Addon::get_instance();
}
$GLOBALS['Cool_Copy_Paste_Addon'] = Cool_Copy_Paste_Addon();
