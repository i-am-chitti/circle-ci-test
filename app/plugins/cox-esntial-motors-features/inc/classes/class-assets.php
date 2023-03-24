<?php
/**
 * Assets class.
 *
 * @package cox-esntial-motors-features
 */

namespace Cox_Esntial_Motors\Features\Inc;

use Cox_Esntial_Motors\Features\Inc\Traits\Singleton;

/**
 * Class Assets
 */
class Assets {

	use Singleton;

	/**
	 * Construct method.
	 */
	protected function __construct() {
		$this->setup_hooks();
	}

	/**
	 * To setup action/filter.
	 *
	 * @return void
	 */
	protected function setup_hooks() {

		/**
		 * Action
		 */
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );

	}

	/**
	 * To enqueue scripts and styles.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {

		wp_register_script(
			'cox-esntial-motors-features-script',
			COX_ESNTIAL_MOTORS_FEATURES_URL . '/assets/build/js/main.js',
			[],
			filemtime( COX_ESNTIAL_MOTORS_FEATURES_PATH . '/assets/build/js/main.js' ),
			true
		);

		wp_register_style(
			'cox-esntial-motors-features-style',
			COX_ESNTIAL_MOTORS_FEATURES_URL . '/assets/build/css/main.css',
			[],
			filemtime( COX_ESNTIAL_MOTORS_FEATURES_PATH . '/assets/build/css/main.css' )
		);

		wp_enqueue_script( 'cox-esntial-motors-features-script' );
		wp_enqueue_style( 'cox-esntial-motors-features-style' );

	}

	/**
	 * To enqueue scripts and styles. in admin.
	 *
	 * @param string $hook_suffix Admin page name.
	 *
	 * @return void
	 */
	public function admin_enqueue_scripts( $hook_suffix ) {

		wp_register_script(
			'cox-esntial-motors-features-script',
			COX_ESNTIAL_MOTORS_FEATURES_URL . '/assets/build/js/admin.js',
			[],
			filemtime( COX_ESNTIAL_MOTORS_FEATURES_PATH . '/assets/build/js/admin.js' ),
			true
		);

		wp_register_style(
			'cox-esntial-motors-features-style',
			COX_ESNTIAL_MOTORS_FEATURES_URL . '/assets/build/css/admin.css',
			[],
			filemtime( COX_ESNTIAL_MOTORS_FEATURES_PATH . '/assets/build/css/admin.css' )
		);

		wp_enqueue_script( 'cox-esntial-motors-features-script' );
		wp_enqueue_style( 'cox-esntial-motors-features-style' );

	}
}
