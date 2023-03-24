<?php
/**
 * Plugin manifest class.
 *
 * @package cox-esntial-motors-features
 */

namespace Cox_Esntial_Motors\Features\Inc;

use Cox_Esntial_Motors\Features\Inc\Plugin_Configs\Gravity_Forms;
use Cox_Esntial_Motors\Features\Inc\Traits\Singleton;

/**
 * Class Plugin
 */
class Plugin {

	use Singleton;

	/**
	 * Construct method.
	 */
	protected function __construct() {

		// Load plugin classes.
		Assets::get_instance();
		Settings::get_instance();
		$this->load_plugin_configs();
		Gravity_Forms::get_instance();
		Export_GF_API::get_instance();
		Export_GF::get_instance();
		Retailer::get_instance();
	}

	/**
	 * Load Plugin Configs.
	 */
	public function load_plugin_configs() {

		// Load all plugin configs.
	}
}
