<?php
/**
 * Plugin Name: Cox Esntial Motors Features
 * Description: All backend functionality will take place in this plugin. Like, registering post type, taxonomy, widget and meta box.
 * Plugin URI:  https://rtcamp.com
 * Author:      rtCamp
 * Author URI:  https://rtcamp.com
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Version:     1.0
 * Text Domain: cox-esntial-motors-features
 *
 * @package cox-esntial-motors-features
 */

define( 'COX_ESNTIAL_MOTORS_FEATURES_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'COX_ESNTIAL_MOTORS_FEATURES_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );

// phpcs:disable WordPressVIPMinimum.Files.IncludingFile.UsingCustomConstant
require_once COX_ESNTIAL_MOTORS_FEATURES_PATH . '/inc/helpers/autoloader.php';
require_once COX_ESNTIAL_MOTORS_FEATURES_PATH . '/inc/helpers/custom-functions.php';
// phpcs:enable WordPressVIPMinimum.Files.IncludingFile.UsingCustomConstant
if ( file_exists( COX_ESNTIAL_MOTORS_FEATURES_PATH . '/vendor/autoload.php' ) ) {
	require_once COX_ESNTIAL_MOTORS_FEATURES_PATH . '/vendor/autoload.php';
}

/**
 * To load plugin manifest class.
 *
 * @return void
 */
function cox_esntial_motors_features_plugin_loader() {
	\Cox_Esntial_Motors\Features\Inc\Plugin::get_instance();
}

cox_esntial_motors_features_plugin_loader();
