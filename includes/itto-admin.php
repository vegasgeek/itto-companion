<?php
/**
 * Admin functionality for ITTO Companion.
 *
 * @package itto-companion
 */

/**
 * Add admin menu item for ITTO Companion settings.
 *
 * Creates a new menu item under Settings in the WordPress admin
 * allowing users to configure the ITTO Companion plugin.
 */
function itto_companion_admin_menu() {
	add_options_page(
		'ITTO Companion Settings',
		'ITTO Companion',
		'manage_options',
		'itto-companion',
		'itto_companion_settings_page'
	);
}
add_action( 'admin_menu', 'itto_companion_admin_menu' );

/**
 * Register the settings for the ITTO Companion plugin.
 *
 * Registers a new setting for the ITTO Companion plugin,
 * allowing users to configure the API key for the ITTO service.
 */
function itto_companion_register_settings() {
	register_setting( 'itto_companion_settings', 'itto_api_key' );
}
add_action( 'admin_init', 'itto_companion_register_settings' );

/**
 * Render the settings page for the ITTO Companion plugin.
 *
 * Displays a form allowing users to configure the API key for the ITTO service.
 * Also includes a test connection button to verify the API key is working.
 */
function itto_companion_settings_page() {
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		
		<form method="post" action="options.php">
			<?php
			settings_fields( 'itto_companion_settings' );
			do_settings_sections( 'itto_companion_settings' );
			?>
			<table class="form-table">
				<tr>
					<th scope="row">
						<label for="itto_api_key"><?php esc_html_e( 'API Key', 'itto-companion' ); ?></label>
					</th>
					<td>
						<input type="text" 
								id="itto_api_key" 
								name="itto_api_key" 
								value="<?php echo esc_attr( get_option( 'itto_api_key' ) ); ?>" 
								class="regular-text">
					</td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>

		<hr>

		<h2><?php esc_html_e( 'Test Connection', 'itto-companion' ); ?></h2>
		<form id="itto-test-connection-form">
			<?php wp_nonce_field( 'itto_test_connection', 'itto_test_connection_nonce' ); ?>
			<button type="submit" 
					id="itto-test-connection-button" 
					class="button button-secondary" 
					<?php echo empty( get_option( 'itto_api_key' ) ) ? 'disabled' : ''; ?>>
				<?php esc_html_e( 'Test Connection', 'itto-companion' ); ?>
			</button>
		</form>
		<div id="itto-test-connection-result"></div>
	</div>
	<?php
}

/**
 * Enqueue admin scripts for the ITTO Companion plugin.
 *
 * Enqueues the itto.js file for the ITTO Companion plugin,
 * which contains the JavaScript code for the test connection button.
 *
 * @param string $hook The current admin page.
 * @return void
 */
function itto_companion_admin_scripts( $hook ) {
	if ( 'settings_page_itto-companion' !== $hook ) {
		return;
	}

	wp_enqueue_script(
		'itto-companion-admin',
		ITTO_COMPANION_URL . 'includes/itto.js',
		array( 'jquery' ),
		'1.0.0',
		true
	);

	wp_localize_script(
		'itto-companion-admin',
		'ittoCompanion',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'itto_test_connection' ),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'itto_companion_admin_scripts' );

/**
 * Handle the test connection AJAX request.
 *
 * Handles the AJAX request for testing the connection to the ITTO service.
 * Verifies the API key is set and sends a test request to the ITTO service.
 * Returns a JSON response indicating success or failure.
 */
function itto_companion_test_connection() {
	check_ajax_referer( 'itto_test_connection', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( 'Unauthorized' );
	}

	$api_key = get_option( 'itto_api_key' );
	if ( empty( $api_key ) ) {
		wp_send_json_error( 'API key not set. Save your API key before testing.' );
	}

	$response = wp_remote_post(
		'https://itto.vegasgeek.com/wp-json/itto/v1/test-connection',
		array(
			'method'    => 'POST',
			'body'      => array( 'api_key' => $api_key ),
			'sslverify' => false,
		)
	);

	if ( is_wp_error( $response ) ) {
		wp_send_json_error( 'Connection test failed. Please try again.' );  }

	$body = json_decode( wp_remote_retrieve_body( $response ) );
	if ( 'success' === $body->status ) {
		wp_send_json_success( 'Connection successful' );
	} else {
		wp_send_json_error( 'Connection failed. Please check your API key.' );
	}
}
add_action( 'wp_ajax_itto_companion_test_connection', 'itto_companion_test_connection' );
