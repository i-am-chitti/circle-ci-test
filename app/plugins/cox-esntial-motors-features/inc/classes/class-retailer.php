<?php
/**
 * Add Retailer Page to Generate Unique Link.
 *
 * This files contains some WIP code in commented format.
 *
 * @package Cox_Essential_Motors_Features
 */

namespace Cox_Esntial_Motors\Features\Inc;

use Cox_Esntial_Motors\Features\Inc\Traits\Singleton;

/**
 * Retailer class.
 */
class Retailer {

	use Singleton;

	/**
	 * Retailer Form ID.
	 *
	 * @var int
	 */
	protected $retailer_form_id;

	/**
	 * Retailer Email Field ID.
	 *
	 * @var int
	 */
	protected $retailer_email_field_id;

	/**
	 * Unique Link Page.
	 *
	 * @var string
	 */
	protected $unique_link_page;

	/**
	 * Construct method.
	 *
	 * @return void.
	 */
	protected function __construct() {
		// Load class.
		$this->assign_value();
		$this->setup_hooks();
	}

	/**
	 * Assign Value.
	 *
	 * @return void.
	 */
	protected function assign_value() {
		$this->retailer_form_id        = Settings::get_instance()->get_gf_form_id();
		$this->retailer_email_field_id = Settings::get_instance()->get_gf_email_field_id();
		$this->unique_link_page        = '';
	}

	/**
	 * Setup Hooks.
	 *
	 * @return void.
	 */
	protected function setup_hooks() {

		if ( ! class_exists( 'GFAPI' ) ) {
			return;
		}

		$form = \GFAPI::get_form( $this->retailer_form_id );

		if ( empty( $form ) ) {
			return;
		}

		$field = \GFAPI::get_field( $form, $this->retailer_email_field_id );

		if ( empty( $field ) || 'email' !== $field->type ) {
			return;
		}

		add_filter( 'set_screen_option_retailer_per_page', [ $this, 'set_screen' ], 10, 3 );
		add_action( 'admin_menu', [ $this, 'add_retailer_page' ] );
		add_action( 'admin_init', [ $this, 'register_retailer_settings' ] );
	}

	/**
	 * Admin Notices.
	 *
	 * @return void.
	 */
	public function email_validation() {

		if ( empty( $_POST['cox_retailer'] ) && ! empty( $_POST['cox_retailer_nonce'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Missing
			?>
			<div class="notice notice-warning is-dismissible">
				<p><?php esc_html_e( 'Please fill the email field.', 'cox-esntial-motors-features' ); ?></p>
			</div>
			<?php
		}

		if ( ! empty( $_POST['cox_retailer'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Missing
			$retailer = empty( $_POST['cox_retailer'] ) ? '' : sanitize_text_field( wp_unslash( $_POST['cox_retailer'] ) ); //phpcs:ignore WordPress.Security.NonceVerification.Missing

			if ( ! is_email( $retailer ) ) {
				?>
				<div class="notice notice-error is-dismissible">
					<p><?php esc_html_e( 'Please enter a valid email.', 'cox-esntial-motors-features' ); ?></p>
				</div>
				<?php
			}
		}
	}

	/**
	 * Add Retailer Page.
	 *
	 * @return void.
	 */
	public function add_retailer_page() {
		$hook = add_menu_page(
			'Retailer',
			'Retailer',
			'manage_options',
			'cox_retailer',
			[ $this, 'render_retailer_page' ],
			'dashicons-admin-users',
			6
		);

		add_action( "load-$hook", [ $this, 'screen_option' ] );
	}

	/**
	 * Render Retailer Page.
	 *
	 * @return void.
	 */
	public function render_retailer_page() {

		$form_id        = $this->retailer_form_id;
		$email_field_id = $this->retailer_email_field_id;
		$self           = empty( $_SERVER['self'] ) ? '' : sanitize_text_field( wp_unslash( $_SERVER['self'] ) );
		/**
		 * Form to get email for unique link generation.
		 *
		 * ***************************************************
		 *
		 * Below are the post variables :-
		 *
		 * cox_retailer : email.
		 * cox_retailer_link_expiration : link expiration date.
		 *
		 * ***************************************************
		 */

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Retailer', 'cox-esntial-motors-features' ); ?></h1>
			<?php $this->email_validation(); ?>
			<form method="post" action="<?php echo esc_url( $self . '?page=cox_retailer' ); ?>">
				<?php
					wp_nonce_field( 'cox_retailer', 'cox_retailer_nonce' );
					settings_fields( 'cox_retailer' );
					do_settings_sections( 'cox_retailer' );
					submit_button( 'Get Link' );
				?>
			</form>
		</div>
		<?php

		/**
		 * Handle Logic after form submit. Mainly to generate unique link.
		 *
		 * ***************************************************************
		 *
		 * Below are the post variables :-
		 *
		 * cox_retailer : email.
		 * cox_retailer_link_expiration : link expiration date.
		 *
		 * ***************************************************************
		 */

		if ( ! empty( $_POST['cox_retailer'] ) ) {

			$cox_retailer       = empty( $_POST['cox_retailer'] ) ? '' : sanitize_email( wp_unslash( $_POST['cox_retailer'] ) );
			$cox_retailer_nonce = empty( $_POST['cox_retailer_nonce'] ) ? '' : sanitize_text_field( wp_unslash( $_POST['cox_retailer_nonce'] ) );

			if ( ! wp_verify_nonce( $cox_retailer_nonce, 'cox_retailer' ) ) {
				die( 'Nonce not verified' );
			}

			if ( is_email( $cox_retailer ) ) {

				// check entry with email.
				$entry = ( \GFAPI::get_entries(
					$form_id,
					[
						'field_filters' => [
							[
								'key'   => "$email_field_id",
								'value' => $cox_retailer,
							],
						],

					]
				) );

				if ( ! empty( $entry ) ) {
					$entry = $entry[0];
				}

				if ( ! empty( $entry ) && ! empty( $entry['resume_token'] ) ) {
					$token = $entry['resume_token'];
					?>
					<h3 class="notice notice-info cox-notice is-dismissible">
						<?php esc_html_e( 'Previously generated URL.', 'cox-esntial-motors-features' ); ?>
					</h3>
					<?php
				} else {
					$token = self::generate_unique_link( $cox_retailer, $entry ?? [] );
					?>
					<h3 class="notice notice-success cox-notice is-dismissible">
						<?php esc_html_e( 'URL generated.', 'cox-esntial-motors-features' ); ?>
					</h3>
					<?php
				}

				?>
				<p>
					<?php esc_html_e( 'URL:', 'cox-esntial-motors-features' ); ?>
					<input class="selector_input" type="text" value="<?php echo esc_url( home_url( $this->unique_link_page . '?gf_token=' . $token ) ); ?>" readonly />
					<button class="copyToClipboard button-primary"><?php esc_html_e( 'Copy', 'cox-esntial-motors-features' ); ?></button>
					<a href="<?php echo esc_url( home_url( $this->unique_link_page . '?gf_token=' . $token ) ); ?>" class="token-link dashicons dashicons-admin-links" target="_blank"></a>
				</p>
				<?php
			}
		}

		/**
		 * Retailer List. It uses WP_List_Table class.
		 *
		 * *******************************************
		 */

		$table = new Retailer_List();
		$table->prepare_items();

		?>
		<br>
		<hr class="section_break">
		<br>
		<div class="wrap retailer_list">
			<div id="icon-users" class="icon32"></div>
			<h2><?php esc_html_e( 'Retailer List', 'cox-esntial-motors-features' ); ?></h2>
			<form method="post" name="form_search_retailer_email" action="<?php echo esc_url( $self . '?page=cox_retailer' ); ?>">
				<?php $table->search_box( 'Search Email', 'search_id' ); ?>
			</form>
			<?php $table->display(); ?>
		</div>
		<?php
	}

	/**
	 * Register Retailer Settings.
	 *
	 * @return void.
	 */
	public function register_retailer_settings() {
		register_setting( 'cox_retailer', 'cox_retailer' );

		add_settings_section(
			'cox_retailer_section',
			'',
			[],
			'cox_retailer'
		);

		add_settings_field(
			'cox_retailer_field',
			__( 'Retailer Email', 'cox-essential-motors-features' ),
			[ $this, 'render_retailer_field' ],
			'cox_retailer',
			'cox_retailer_section'
		);
	}

	/**
	 * Render Retailer Field.
	 *
	 * @return void.
	 */
	public function render_retailer_field() {
		?>
		<input type="email" name="cox_retailer" placeholder="Email" />
		<?php
	}

	/**
	 * Render Retailer Link Expiration input field.
	 *
	 * @return void.
	 */
	public function render_retailer_link_expiration() {
		?>
		<input type="datetime-local" name="cox_retailer_link_expiration">
		<?php
	}

	/**
	 * Handle link generation.
	 *
	 * @param string $email Email address.
	 * @param array  $entry Entry.
	 *
	 * @return string Unique Token.
	 */
	public function generate_unique_link( $email, $entry ) {

		[$resume_token, $source_url, $ip] = $this->generate_token( $email );

		$entry = $this->get_entry( $email, $entry, $resume_token, $source_url, $ip );

		$this->create_draft_submission( $entry );

		if ( empty( $entry['id'] ) ) {
			\GFAPI::add_entry( $entry );
		} else {
			\GFAPI::update_entry( $entry, $entry['id'] );
		}

		return $resume_token;
	}

	/**
	 * Screen option for list of entries per page.
	 *
	 * @return void.
	 */
	public function screen_option() {
		$args = array(
			'label'   => 'Number of items per page:',
			'default' => 10,
			'option'  => 'retailer_per_page',

		);

		add_screen_option( 'per_page', $args );
	}

	/**
	 * Set screen option.
	 *
	 * @param string $status Status.
	 * @param string $option Option.
	 * @param string $value Value.
	 *
	 * @return int.
	 */
	public function set_screen( $status, $option, $value ) {
		return (int) $value;
	}

	/**
	 * Generate Gravity form token using native gravity form class.
	 *
	 * @param array $entry Entry.
	 *
	 * @return array.
	 */
	public function generate_token( $entry ) {
		$form         = \GFAPI::get_form( $this->retailer_form_id );
		$ip           = rgars( $form, 'personalData/preventIP' ) ? '' : \GFFormsModel::get_ip();
		$source_url   = esc_url_raw( \GFFormsModel::get_current_page_url() );
		$unique_id    = \GFFormsModel::get_form_unique_id( $this->retailer_form_id );
		$resume_token = \GFFormsModel::save_draft_submission( $form, $entry, $entry, 1, [], $unique_id, $ip, $source_url, '' );

		return [ $resume_token, $source_url, $ip ];
	}

	/**
	 * Create draft submission of gravity form.
	 *
	 * @param array $entry Entry.
	 *
	 * @return void.
	 */
	public function create_draft_submission( $entry ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
		$submission                     = $wpdb->get_row( $wpdb->prepare( 'SELECT submission FROM wp_gf_draft_submissions WHERE uuid = %s', $entry['resume_token'] ) );
		$submission                     = json_decode( $submission->submission, true );
		$submission['submitted_values'] = $entry;
		$submission['partial_entry']    = $entry;
		$submission                     = wp_json_encode( $submission );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
		$wpdb->update(
			'wp_gf_draft_submissions',
			array( 'submission' => $submission ),
			array( 'uuid' => $entry['resume_token'] )
		);
	}

	/**
	 * Generate processes entry.
	 *
	 * @param string $email Email address.
	 * @param array  $entry Entry.
	 * @param string $resume_token Resume token.
	 * @param string $source_url Source URL.
	 * @param string $ip IP address.
	 *
	 * @return array.
	 */
	public function get_entry( $email, $entry, $resume_token, $source_url, $ip ) {

		$form_id        = $this->retailer_form_id;
		$email_field_id = $this->retailer_email_field_id;

		$entry['ip']                               = $ip;
		$entry['form_id']                          = $form_id;
		$entry['source_url']                       = $source_url;
		$entry['resume_url']                       = home_url( $this->unique_link_page . '?gf_token=' . $resume_token );
		$entry['date_created']                     = $entry['date_created'] ?? gmdate( 'Y-m-d H:i:s' );
		$entry['resume_token']                     = $resume_token;
		$entry[ $email_field_id ]                  = $email;
		$entry['partial_entry_id']                 = \GFFormsModel::get_uuid();
		$entry['partial_entry_percent']            = 5;
		$entry['required_fields_percent_complete'] = 0;

		return $entry;
	}
}
