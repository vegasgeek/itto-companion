<?php
/**
 * Admin functionality for ITTO Companion.
 *
 * @package itto-companion
 */

declare(strict_types=1);

/**
 * Add the admin menu item
 */
function itto_companion_admin_menu(): void {
    add_options_page(
        'ITTO Companion',
        'ITTO Companion',
        'manage_options',
        'itto-companion',
        'itto_companion_settings_page'
    );
}
add_action('admin_menu', 'itto_companion_admin_menu');

/**
 * Register settings
 */
function itto_companion_admin_init(): void {
    register_setting('itto_companion', 'itto_companion_api_url');
    register_setting('itto_companion', 'itto_companion_site_hash', [
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
    ]);

    add_settings_section(
        'itto_companion_main',
        'ITTO Connection Settings',
        'itto_companion_section_callback',
        'itto_companion'
    );

    add_settings_field(
        'itto_companion_api_url',
        'ITTO API URL',
        'itto_companion_api_url_callback',
        'itto_companion',
        'itto_companion_main'
    );

    add_settings_field(
        'itto_companion_site_hash',
        'Site Hash',
        'itto_companion_site_hash_callback',
        'itto_companion',
        'itto_companion_main'
    );
}
add_action('admin_init', 'itto_companion_admin_init');

/**
 * Render the settings page
 */
function itto_companion_settings_page(): void {
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form action="options.php" method="post">
            <?php
            settings_fields('itto_companion');
            do_settings_sections('itto_companion');
            submit_button('Save Settings');
            ?>
        </form>
        <hr>
        <h2>Test Connection</h2>
        <p>Click the button below to test the connection to the ITTO service.</p>
        <button type="button" class="button" id="itto-test-connection">Test Connection</button>
        <div id="itto-test-result"></div>
    </div>
    <?php
}

/**
 * Settings section description
 */
function itto_companion_section_callback(): void {
    echo '<p>Configure your connection to the ITTO monitoring service.</p>';
}

/**
 * API URL field callback
 */
function itto_companion_api_url_callback(): void {
    $value = get_option('itto_companion_api_url', '');
    ?>
    <input type="url" id="itto_companion_api_url" 
           name="itto_companion_api_url" 
           value="<?php echo esc_attr($value); ?>" 
           class="regular-text">
    <p class="description">The URL of your ITTO monitoring service (e.g., https://itto.test)</p>
    <?php
}

/**
 * Site hash field callback
 */
function itto_companion_site_hash_callback(): void {
    $value = get_option('itto_companion_site_hash', '');
    ?>
    <input type="text" id="itto_companion_site_hash" 
           name="itto_companion_site_hash" 
           value="<?php echo esc_attr($value); ?>" 
           class="regular-text" 
           pattern="[a-zA-Z0-9]{12}"
           maxlength="12">
    <p class="description">The Site Hash provided by your ITTO service administrator</p>
    <?php
}

/**
 * Enqueue admin scripts
 */
function itto_companion_admin_enqueue_scripts(string $hook): void {
    if ($hook !== 'settings_page_itto-companion') {
        return;
    }

    wp_enqueue_script(
        'itto-companion-admin',
        plugins_url('assets/js/admin.js', dirname(__FILE__)),
        ['jquery'],
        ITTO_COMPANION_VERSION,
        true
    );

    wp_localize_script('itto-companion-admin', 'itto_companion', [
        'nonce' => wp_create_nonce('itto_companion'),
        'apiUrl' => get_option('itto_companion_api_url'),
        'siteHash' => get_option('itto_companion_site_hash'),
    ]);
}
add_action('admin_enqueue_scripts', 'itto_companion_admin_enqueue_scripts'); 