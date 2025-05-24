<?php
/**
 * Plugin Name: ITTO Companion
 * Plugin URI: https://itto.vegasgeek.com
 * Description: Companion plugin for Is This Thing On? monitoring service
 * Version: 1.0.4
 * Author: VegasGeek
 * Author URI: https://vegasgeek.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: itto-companion
 *
 * @package itto-companion
 */

define( 'ITTO_COMPANION_VERSION', '1.0.4' );
define( 'ITTO_COMPANION_PATH', plugin_dir_path( __FILE__ ) );
define( 'ITTO_COMPANION_URL', plugin_dir_url( __FILE__ ) );

// Include admin functionality.
require_once ITTO_COMPANION_PATH . 'includes/itto-admin.php';
