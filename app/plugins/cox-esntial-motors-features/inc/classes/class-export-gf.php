<?php
/**
 *  Add export button to form entries.
 *
 * @package cox-esntial-motors-features
 */

namespace Cox_Esntial_Motors\Features\Inc;

use Cox_Esntial_Motors\Features\Inc\Traits\Singleton;

/**
 * Class Export Gravity Forms to add export button to entries.
 */
class Export_GF {



	use Singleton;

	/**
	 * Export field.
	 *
	 * @var string
	 */
	protected $export_field = 'field_id-3.3';

	/**
	 * Export field id.
	 *
	 * @var string
	 */
	protected $export_field_id = 3.3;

	/**
	 * API endpoint.
	 *
	 * @var string
	 */
	protected $api_endpoint = '/wp-json/spreadsheet/v1/route';

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
		add_filter( 'gform_entry_list_columns', array( $this, 'set_columns' ), 10, 2 );
		add_filter( 'gform_entries_column_filter', array( $this, 'change_column_data' ), 10, 5 );
	}

	/**
	 * Set export column.
	 *
	 * @param array $table_columns  :   The table columns.
	 * @param int   $form_id        :   The form id.
	 * @return array
	 */
	public function set_columns( $table_columns, $form_id ) {
		$table_columns[ $this->export_field ] = 'Exports';
		$column_selector                      = $table_columns['column_selector'];
		unset( $table_columns['column_selector'] );
		$table_columns['column_selector'] = $column_selector;
		return $table_columns;
	}

	/**
	 * Add export button column & button.
	 *
	 * @param string $value         :   The value.
	 * @param int    $form_id       :   The form id.
	 * @param int    $field_id      :   The field id.
	 * @param array  $entry             :   The entry.
	 * @param string $query_string  :   The query string.
	 * @return string
	 */
	public function change_column_data( $value, $form_id, $field_id, $entry, $query_string ) {

		if ( "$this->export_field_id" === $field_id ) {

			$url = get_site_url();

			if ( $url ) {

				$nonce = wp_create_nonce( 'wp_rest' );
				?>
				<a class="export-button" href="<?php echo esc_url( home_url( $this->api_endpoint ) ); ?>?_wpnonce=<?php echo esc_attr( $nonce ); ?>&form_id=<?php echo esc_attr( $form_id ); ?>&entry_id=<?php echo esc_attr( $entry['id'] ); ?>">
					<?php esc_html_e( 'Export', 'cox-esntial-motors-features' ); ?>
				</a>
				<?php
			} else {
				return esc_html__( 'Please Enable Export 😕!', 'cox-esntial-motors-features' );
			}
		} else {
			return $value;
		}
	}
}
