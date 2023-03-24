<?php
/**
 * Add Retailer List to Retailers Page.
 *
 * @package Cox_Essential_Motors_Features
 */

namespace Cox_Esntial_Motors\Features\Inc;

use WP_List_Table;

/**
 * Class Retailer List.
 */
class Retailer_List extends WP_List_Table {

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
	 * Retailer Per Page.
	 *
	 * @var int.
	 */
	protected $retailer_per_page;

	/**
	 * Assign Value.
	 *
	 * @return void.
	 */
	protected function assign_value() {
		$this->retailer_form_id        = Settings::get_instance()->get_gf_form_id();
		$this->retailer_email_field_id = Settings::get_instance()->get_gf_email_field_id();
		$this->unique_link_page        = '';
		$this->retailer_per_page       = get_user_meta( get_current_user_id(), 'retailer_per_page', true );
	}

	/**
	 * Prepare Items.
	 *
	 * @return void.
	 */
	public function prepare_items() {

		$this->assign_value();
		$per_page = $this->retailer_per_page;
		/**
		 * We are extending the class WP_List_Table so we haven`t use nonce thats why disabling PHPCS.
		 */
		// phpcs:disable
		$order   = ( isset( $_GET['order'] ) ) ? sanitize_text_field( wp_unslash( $_GET['order'] ) ) : 'asc';
		$search  = ( isset( $_REQUEST['s'] ) ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : false;
		$orderby = ( isset( $_GET['orderby'] ) ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : '';
		// phpcs:enable
		$this->disable_retailer();

		$search_criteria = [
			'field_filters' => [
				[
					'key'      => 'resume_token',
					'value'    => '',
					'operator' => 'is not',

				],
				[
					'key'      => $this->retailer_email_field_id,
					'value'    => $search,
					'operator' => 'contains',
				],

			],
		];

		$total_count = \GFAPI::count_entries(
			$this->retailer_form_id,
			$search_criteria
		);

		$current_page = $this->get_pagenum();
		$sortable     = $this->get_sortable_columns();
		$this->items  = \GFAPI::get_entries(
			$this->retailer_form_id,
			$search_criteria,
			[

				'key'       => $orderby,
				'direction' => strtoupper( $order ),

			],
			[
				'offset'    => ( $current_page - 1 ) * (int) $per_page,
				'page_size' => $per_page,
			]
		);

		$this->set_pagination_args(
			array(
				'total_items' => $total_count,
				'per_page'    => $per_page,
			)
		);

		$this->_column_headers = [ $this->get_columns(), [], $sortable ];
	}

	/**
	 * Get Columns.
	 */
	public function get_columns() {
		$columns = [
			'id'                             => 'ID',
			'form_id'                        => 'Form ID',
			"$this->retailer_email_field_id" => 'Email',
			'resume_url'                     => 'Resume URL',
			'resume_token'                   => 'Resume Token',
			'disable'                        => 'Disable / Enable link',
		];

		return $columns;
	}

	/**
	 * Get Sortable Columns.
	 *
	 * @param array  $item Item.
	 * @param string $column_name Column Name.
	 *
	 * @return string.
	 */
	public function column_default( $item, $column_name ) {

		$self = empty( $_SERVER['self'] ) ? '' : sanitize_text_field( wp_unslash( $_SERVER['self'] ) );

		switch ( $column_name ) {
			case 'id':
			case "$this->retailer_email_field_id":
			case 'resume_token':
			case 'form_id':
				return $item[ $column_name ];
			case 'resume_url':
				return ! empty( $item['resume_token'] ) ? '<input type="text" class="selector_input" value=' . esc_url( home_url( $this->unique_link_page . '?gf_token=' . $item['resume_token'] ) ) . ' readonly /><button class="copyToClipboard button-primary">Copy</button> <a href="' . esc_url( home_url( $this->unique_link_page . '?gf_token=' . $item['resume_token'] ) ) . '" class="token-link dashicons dashicons-admin-links"></a>' : '';
			case 'disable':
				return "<form method='post' action='" . $self . "?page=cox_retailer'>" . wp_nonce_field( 'cox_retailer_disable', 'cox_retailer_disable_nonce' ) . "<input type='hidden' name='disable' value='" . $item['resume_token'] . '_' . $item['id'] . "'><input type='submit' value='Disable' class='button button-primary'></form>";
			default:
				return __( 'Invalid column name', 'cox-esntial-motors-features' );
		}
	}

	/**
	 * Get Sortable Columns.
	 *
	 * @return array.
	 */
	public function get_sortable_columns() {
		$sortable_columns = array(
			"$this->retailer_email_field_id" => array( "$this->retailer_email_field_id", false ),
		);

		return $sortable_columns;
	}

	/**
	 * Disable Retailer link.
	 *
	 * @return void.
	 */
	public function disable_retailer() {

		$disable = ( isset( $_REQUEST['disable'] ) ) ? sanitize_text_field( wp_unslash( $_REQUEST['disable'] ) ) : false;
		$nonce   = ( isset( $_REQUEST['cox_retailer_disable_nonce'] ) ) ? sanitize_text_field( wp_unslash( $_REQUEST['cox_retailer_disable_nonce'] ) ) : false;

		if ( $disable ) {
			if ( ! wp_verify_nonce( $nonce, 'cox_retailer_disable' ) ) {
				die( 'Nonce not verified' );
			}
			$key   = $disable;
			$key   = explode( '_', $key );
			$token = $key[0];
			$id    = $key[1];

			\GFFormsModel::delete_draft_submission( $token );

			$entry = \GFAPI::get_entry( $id );

			$entry['resume_token']                     = '';
			$entry['resume_url']                       = '';
			$entry['partial_entry_id']                 = '';
			$entry['partial_entry_percent']            = 100;
			$entry['required_fields_percent_complete'] = 100;
			\GFAPI::update_entry( $entry );
		}
	}
}
