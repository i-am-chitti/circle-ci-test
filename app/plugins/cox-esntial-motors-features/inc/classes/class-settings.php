<?php
/**
 * This file contains settings of plugin.
 *
 * @package    cox-esntial-motors-features
 */

namespace Cox_Esntial_Motors\Features\Inc;

use \Cox_Esntial_Motors\Features\Inc\Traits\Singleton;

/**
 * Class Setting
 */
class Settings {

	use Singleton;

	/**
	 * Constructor Method.
	 */
	protected function __construct() {
		$this->setup_hooks();
	}

	/**
	 * Attach hooks.
	 */
	protected function setup_hooks() {
		/**
		 * Actions
		 */
		add_action( 'admin_menu', [ $this, 'add_options_page' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
	}

	/**
	 * Add all submenu pages here.
	 *
	 * @return void
	 */
	public function add_options_page(): void {
		add_submenu_page(
			'options-general.php',
			__( 'Esntial Motors Feature Settings', 'cox-esntial-motors-features' ),
			__( 'Esntial Motors Features Settings', 'cox-esntial-motors-features' ),
			'manage_options',
			'cox-esntial-motors-features-settings',
			array( $this, 'render_setting_html' )
		);
	}

	/**
	 * To render settings page HTML content
	 *
	 * @return void
	 */
	public function render_setting_html() {
		cox_esntial_motors_features_template( 'setting-options', array(), true );
	}

	/**
	 * To register settings settings option group, add sections and fields.
	 *
	 * @return void
	 */
	public function register_settings() {

		register_setting( 'settings_admin_menu_options', 'cox_esntial_motors_features_gf_id' );
		register_setting( 'settings_admin_menu_options', 'cox_esntial_motors_features_gf_email_field_id' );

		add_settings_section(
			'cox_esntial_motors_features_setting_section',
			__( 'Esntial Motors Features Settings', 'cox-esntial-motors-features' ),
			array( $this, 'settings_section_callback' ),
			'settings_admin_menu_options'
		);

		// GF ID setting field.
		add_settings_field(
			'cox_esntial_motors_features_setting_gf_id_field',
			__( 'Gravity Form ID', 'cox-esntial-motors-features' ),
			array( $this, 'setting_field_gf_form_callback' ),
			'settings_admin_menu_options',
			'cox_esntial_motors_features_setting_section',
		);

		add_settings_field(
			'cox_esntial_motors_features_setting_gf_email_field_id_field',
			__( 'Email Field ID', 'cox-esntial-motors-features' ),
			array( $this, 'setting_field_gf_email_field_id_callback' ),
			'settings_admin_menu_options',
			'cox_esntial_motors_features_setting_section',
		);
	}

	/**
	 * HTML below settings page title.
	 *
	 * @return void
	 */
	public function settings_section_callback() {
		printf( '<p>%s</p>', esc_html__( 'Customize Plugin Settings', 'cox-esntial-motors-features' ) );
	}

	/**
	 * HTML input box for setting's GF ID input field.
	 *
	 * @return void
	 */
	public function setting_field_gf_form_callback() {
		$gf_form_id = get_option( 'cox_esntial_motors_features_gf_id' );
		?>
		<input type="text" id="cox-esntial-motors-features-gf-form-id" name="cox_esntial_motors_features_gf_id" value="<?php echo esc_attr( $gf_form_id ); ?>" />
		<?php
	}

	/**
	 * HTML input box for setting's GF Email Field ID input field.
	 *
	 * @return void
	 */
	public function setting_field_gf_email_field_id_callback() {
		$gf_email_field_id = get_option( 'cox_esntial_motors_features_gf_email_field_id' );
		?>
		<input type="text" id="cox-esntial-motors-features-gf-email-field-id" name="cox_esntial_motors_features_gf_email_field_id" value="<?php echo esc_attr( $gf_email_field_id ); ?>" />
		<?php
	}

	/**
	 * Get form id.
	 *
	 * @return int
	 */
	public function get_gf_form_id() {
		return get_option( 'cox_esntial_motors_features_gf_id' );
	}

	/**
	 * Get form email field id.
	 *
	 * @return int
	 */
	public function get_gf_email_field_id() {
		return get_option( 'cox_esntial_motors_features_gf_email_field_id' );
	}
}
