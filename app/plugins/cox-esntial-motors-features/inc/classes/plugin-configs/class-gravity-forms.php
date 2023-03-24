<?php
/**
 * Gravity form plugin config.
 *
 * @package cox-esntial-motors-features
 */

namespace Cox_Esntial_Motors\Features\Inc\Plugin_Configs;

use \Cox_Esntial_Motors\Features\Inc\Settings;
use \Cox_Esntial_Motors\Features\Inc\Traits\Singleton;

/**
 * GF form config class.
 */
class Gravity_Forms {

	use Singleton;

	/**
	 * Form entry.
	 *
	 * @var array
	 */
	private $entry = array();

	/**
	 * Allowed image extensions
	 *
	 * @var array
	 */
	const ALLOWED_IMAGE_EXT = array( 'png', 'jpg', 'webp', 'gif' );

	/**
	 * If has form
	 *
	 * @var bool
	 */
	private $has_form = false;

	/**
	 * Resume token
	 *
	 * @var string
	 */
	private $resume_token = '';

	/**
	 * If gf_token is provided but the associated draft entry is invalid ie token has expired.
	 *
	 * @var bool
	 */
	private $is_draft_invalid = false;

	/**
	 * Entry id.
	 *
	 * @var int
	 */
	private $entry_id = 0;

	/**
	 * Draft entry prgoress for required fields
	 *
	 * @var string
	 */
	private $draft_entry_required_fields_progress = 0;

	/**
	 * Construct method.
	 */
	final protected function __construct() {

		$this->setup_hooks();

	}

	/**
	 * To register action/filters.
	 *
	 * @return void
	 */
	protected function setup_hooks() {

		/**
		 * Filters
		 */
		add_filter( 'gform_tooltips', array( $this, 'add_identifier_tooltip' ) );
		add_filter( 'gform_field_content', array( $this, 'modify_input_field_html' ), 10, 2 );
		add_filter( 'gform_incomplete_submissions_expiration_days', array( $this, 'modify_expiration_duration' ) );

		/**
		 * Actions
		 */
		add_action( 'gform_loaded', array( $this, 'get_entry_by_retailer' ) );
		add_action( 'gform_field_standard_settings', array( $this, 'add_custom_field_identifier_setting' ), 10, 2 );
		add_action( 'gform_editor_js', array( $this, 'add_editor_script' ) );

	}

	/**
	 * Modify expiration duration.
	 *
	 * @param int $expiration_days Expiration duration.
	 */
	public function modify_expiration_duration( $expiration_days ) {
		$expiration_days = 120;
		return $expiration_days;
	}

	/**
	 * Modify input field HTML and add identifier attribute.
	 *
	 * @param string   $field_content Field HTML content.
	 * @param GF_Field $field GF form field instance.
	 *
	 * @return string
	 */
	public function modify_input_field_html( $field_content, $field ) {
		if ( ! empty( $field->customFieldIdentifier ) ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			return str_replace( 'type=', sprintf( "data-uid='%s' type=", $field->customFieldIdentifier ), $field_content ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		}
		return $field_content;
	}

	/**
	 * Add editor script
	 */
	public function add_editor_script() {
		?>
			<script type="text/javascript">
				for (i in fieldSettings) {
					fieldSettings[i] += ', .custom_section_setting';
					fieldSettings[i] += ', .custom_identifier_setting';
					fieldSettings[i] += ', .custom_section_description_setting';
					fieldSettings[i] += ', .custom_static_text_section_setting';
					fieldSettings[i] += ', .custom_translation_entry_setting';
				}

				//binding to the load field settings event to initialize the identifier field.
				jQuery(document).on( 'gform_load_field_settings' , function(event, field, form){
					jQuery( '#field_custom_section_value' ).val( rgar( field, 'customSection' ) );
					jQuery( '#field_custom_identifier_value' ).val( rgar( field, 'customFieldIdentifier' ) );
					jQuery( '#field_custom_section_description_value' ).val( rgar( field, 'customSectionDescription' ) );
					jQuery( '#field_custom_static_text_section_value' ).val( rgar( field, 'customStaticTextSection' ) );
					jQuery( '#field_custom_translation_entry_value' ).val( rgar( field, 'customTranslationEntry' ) );
				});
			</script>
		<?php
	}

	/**
	 * Add identifier tooltip.
	 *
	 * @param Array $tooltips A list of tooltip content.
	 *
	 * @return Array
	 */
	public function add_identifier_tooltip( $tooltips ) {
		$tooltips['form_field_custom_identifier_value'] = sprintf( '<h6>%s</h6><p>%s</p>', __( 'Identifier', 'cox-esntial-motors-features' ), __( 'Enter a unique value to identify this field in frontend.', 'cox-esntial-motors-features' ) );
		return $tooltips;
	}

	/**
	 * Add unique data attribute.
	 *
	 * @param int $position Position of the field.
	 * @param int $form_id Form ID.
	 *
	 * @return void
	 */
	public function add_custom_field_identifier_setting( $position, $form_id ) {

		if ( 1350 === $position ) {
			?>
				<li class="custom_identifier_setting field_setting">
					<label for="field_custom_identifier_value">
						<?php esc_html_e( 'Custom Field Identifier', 'cox-esntial-motors-features' ); ?>
						<?php gform_tooltip( 'form_field_custom_identifier_value' ); ?>
					</label>
					<input type="text" id="field_custom_identifier_value" oninput="SetFieldProperty('customFieldIdentifier', this.value);" />
				</li>

				<li class="custom_section_setting field_setting">
					<!-- Section 	 -->
					<label for="field_custom_section_value">
						<?php esc_html_e( 'Section', 'cox-esntial-motors-features' ); ?>
						<?php gform_tooltip( 'form_field_custom_section_value' ); ?>
					</label>
					<input type="text" id="field_custom_section_value" oninput="SetFieldProperty('customSection', this.value);" />
				</li>

				<li class="custom_section_description_setting field_setting">
					<!-- Section Description 	 -->
					<label for="field_custom_section_description_value">
						<?php esc_html_e( 'Section Description', 'cox-esntial-motors-features' ); ?>
						<?php gform_tooltip( 'form_field_custom_section_description_value' ); ?>
					</label>
					<input type="text" id="field_custom_section_description_value" oninput="SetFieldProperty('customSectionDescription', this.value);" />
				</li>

				<li class="custom_static_text_section_setting field_setting" >
					<!-- Static Text Section -->
					<label for ="field_custom_static_text_section_value">
						<?php esc_html_e( 'Static Text Section', 'cox-esntial-motors-features' ); ?>
						<?php gform_tooltip( 'form_field_custom_static_text_section_value' ); ?>
					</label>
					<input type="text" id="field_custom_static_text_section_value" oninput="SetFieldProperty('customStaticTextSection', this.value);" />
				</li>

				<li class="custom_translation_entry_setting field_setting">
					<!-- TranslationEntry -->
					<label for ="field_custom_translation_entry_value">
						<?php esc_html_e( 'Translation Entry', 'cox-esntial-motors-features' ); ?>
						<?php gform_tooltip( 'form_field_custom_translation_entry_value' ); ?>
					</label>
					<input type="text" id="field_custom_translation_entry_value" oninput="SetFieldProperty('customTranslationEntry', this.value);" />
				</li>

			<?php
		}

	}

	/**
	 * Get field attributes
	 *
	 * @param string $key Unique field identifier.
	 *
	 * @return string
	 */
	public function get_field_attributes( $key ) {
		return 'data-gfield="' . $key . '"';
	}

	/**
	 * Get entry submitted by the retailer.
	 *
	 * @param string $retailer Email id.
	 *
	 * @return Array
	 */
	public function get_entry_by_retailer( $retailer = '' ) {

		if ( isset( $_GET['retailer'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$retailer = sanitize_text_field( wp_unslash( $_GET['retailer'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		$resume_token = '';

		if ( isset( $_GET['gf_token'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$resume_token = sanitize_text_field( wp_unslash( $_GET['gf_token'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		if ( ! empty( $retailer ) || ! empty( $resume_token ) ) {
			$form_id = Settings::get_instance()->get_gf_form_id();

			$form = \GFAPI::get_form( $form_id );

			if ( ! $form ) {
				return array();
			}

			$temp_file_upload_dir = \RGFormsModel::get_upload_url( $form_id ) . '/tmp/';

			// Assoc array containing custom identifier id to field Id mapping.
			$formatted_entry_fields = array();

			// Assoc array containing custom identifier id to the field value(user provided value) mapping.
			$formatted_entry = array();

			if ( ! empty( $form['fields'] ) ) {
				foreach ( $form['fields'] as $field ) {

					$formatted_entry_fields[ $field->customFieldIdentifier ] = // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
						array(
							'id'   => $field->id,
							'type' => $field->type,
						);
				}
			}

			// If resume token available, fetch draft submissions.
			if ( ! empty( $resume_token ) ) {

				$draft_entry = \GFFormsModel::get_draft_submission_values( $resume_token );

				$entries = \GFAPI::get_entry_ids(
					$form_id,
					array(
						'status'        => 'active',
						'field_filters' => array(
							array(
								'key'   => 'resume_token',
								'value' => $resume_token,
							),
						),
					)
				);

				if ( count( $entries ) === 1 ) {
					$this->entry_id = intval( $entries[0] );
				}

				if ( $this->entry_id ) {
					$this->draft_entry_required_fields_progress = gform_get_meta( $this->entry_id, 'required_fields_percent_complete' );
				}

				if ( $draft_entry ) {

					$this->has_form     = true;
					$this->resume_token = $resume_token;

					if ( array_key_exists( 'submission', $draft_entry ) ) {
						$draft_entry = json_decode( $draft_entry['submission'], true );

						foreach ( $formatted_entry_fields as $formatted_entry_field_key => $formatted_entry_field_property ) {
							$formatted_entry_field_id   = $formatted_entry_field_property['id'];
							$formatted_entry_field_type = $formatted_entry_field_property['type'];

							if ( 'fileupload' === $formatted_entry_field_type ) {

								$draft_entry_field_files = $draft_entry['files'];

								if ( count( $draft_entry_field_files ) > 0 ) {
									$file_key = 'input_' . $formatted_entry_field_id;

									if ( array_key_exists( $file_key, $draft_entry_field_files ) ) {

										$num_of_files = count( $draft_entry_field_files[ $file_key ] );
										if ( $num_of_files > 0 ) {

											$temp_file_name = $draft_entry_field_files[ $file_key ][0]['temp_filename'];
											$temp_file_url  = $temp_file_upload_dir . $temp_file_name;

											if ( $num_of_files > 1 ) {

												$max_img_dimension     = 0;
												$max_img_dimension_url = $temp_file_url;
												foreach ( $draft_entry_field_files[ $file_key ] as $current_file_arr ) {
													$file_name      = $current_file_arr['temp_filename'];
													$file_extension = pathinfo( $file_name, PATHINFO_EXTENSION );

													if ( in_array( $file_extension, self::ALLOWED_IMAGE_EXT, true ) ) {
														$temp_file_url    = $temp_file_upload_dir . $file_name;
														$image_properties = wp_getimagesize( $temp_file_url );
														if ( $image_properties ) {
															list($width, $height)  = $image_properties;
															$current_img_dimension = $width * $height;
															if ( $current_img_dimension > $max_img_dimension ) {
																$max_img_dimension     = $current_img_dimension;
																$max_img_dimension_url = $temp_file_url;
															}
														}
													}
												}
												$temp_file_url = $max_img_dimension_url;
											}

											$formatted_entry[ $formatted_entry_field_key ] = $temp_file_url;
										}
									}
								}
							} elseif ( in_array( $formatted_entry_field_type, array( 'text', 'textarea', 'email', 'website' ), true ) ) {

								if ( array_key_exists( 'partial_entry', $draft_entry ) ) {

									if ( array_key_exists( $formatted_entry_field_id, $draft_entry['partial_entry'] ) ) {
										$formatted_entry[ $formatted_entry_field_key ] = $draft_entry['partial_entry'][ $formatted_entry_field_id ];
									}
								}
							}
						}
					}
				} else {
					$this->is_draft_invalid = true;
				}
			} else {
				// fetch submitted submission by the email. This will require to look up in GF entry.
				$email_field_id = null;

				$search_criteria = array();

				$search_criteria['field_filters'][]       = array(
					'key'   => strval( $email_field_id ),
					'value' => $retailer,
				);
				$search_criteria['field_filters']['mode'] = 'any';

				$entries = \GFAPI::get_entry_ids( $form_id, $search_criteria );

				if ( ! empty( $entries ) ) {

					$entry = $entries[0];

					$result = \GFAPI::get_entry( (int) $entry );

					$formatted_entry = array();
					foreach ( $formatted_entry_fields as $formatted_entry_field_key => $formatted_entry_field_value ) {
						$formatted_entry[ $formatted_entry_field_key ] = $result[ $formatted_entry_field_value ];
					}
				}
			}
			$this->entry = $formatted_entry;
		}

		return array();
	}

	/**
	 * Get form field value.
	 *
	 * @param string $key Unique identifier of the field.
	 * @return string
	 */
	public function get_field_value( $key = '' ) {

		if ( array_key_exists( $key, $this->entry ) ) {
			return $this->entry[ $key ];
		}

		return '';
	}

	/**
	 * Get form field CSS classes.
	 *
	 * @return string
	 */
	public function get_field_css_classes() {
		return 'gf-live-preview ';
	}

	/**
	 * Get if page has form when $_GET['gf_token'] is a valid token
	 *
	 * @return bool
	 */
	public function has_gf_form() {
		return $this->has_form;
	}

	/**
	 * Check if draft entry is valid.
	 *
	 * @return bool
	 */
	public function is_draft_entry_invalid() {
		return $this->is_draft_invalid;
	}

	/**
	 * Get gf_token
	 *
	 * @return string
	 */
	public function get_resume_token_query_param() {
		return $this->resume_token;
	}

	/**
	 * Get required field progress percentage
	 *
	 * @return string
	 */
	public function get_required_field_progress_precentage() {
		return $this->draft_entry_required_fields_progress;
	}
}
