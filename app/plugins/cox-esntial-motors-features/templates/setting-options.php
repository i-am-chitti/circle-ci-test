<?php
/**
 * Template for features plugin settings.
 *
 * @package cox-esntial-motors-features
 */

?>

<div class="wrap">
	<form action="options.php" method="post">
		<?php
		// Output security fields for the registered setting.
		settings_fields( 'settings_admin_menu_options' );

		// Output setting sections and their fields.
		do_settings_sections( 'settings_admin_menu_options' );

		// Output save settings button.
		submit_button( __( 'Save Settings', 'cox-esntial-motors-features' ) );
		?>
	</form>
</div>
