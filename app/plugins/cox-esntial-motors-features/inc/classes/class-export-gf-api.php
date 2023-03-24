<?php
/**
 * Custom End Point to export Gravity from Data.
 *
 * @package cox-esntial-motors-features
 */

namespace Cox_Esntial_Motors\Features\Inc;

use Cox_Esntial_Motors\Features\Inc\Traits\Singleton;

/**
 * Class Export_GF_API provide endpoint to export Gravity from data.
 */
class Export_GF_API extends \WP_REST_Controller {

	use Singleton;

	/**
	 * Version.
	 *
	 * @var string
	 */
	protected $version = '1';

	/**
	 * Namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'spreadsheet/v';

	/**
	 * Base.
	 *
	 * @var string
	 */
	protected $base = 'route';

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
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register the routes for the objects of the controller.
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace . $this->version,
			'/' . $this->base,
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
				),
			)
		);
	}

	/**
	 * Get a collection of items
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return void.
	 */
	public function get_items( $request ) {

		$args = $request->get_params();
		Spreadsheet::export( absint( $args['form_id'] ), absint( $args['entry_id'] ) );
	}


	/**
	 * Check if a given request has access to get items
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function get_items_permissions_check( $request ) {

		return in_array( 'administrator', (array) wp_get_current_user()->roles, true );
	}

	/**
	 * Check if a given request has access to get a specific item
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function get_item_permissions_check( $request ) {
		return $this->get_items_permissions_check( $request );
	}
}
