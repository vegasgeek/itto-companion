<?php
/**
 * Plugin Name: ITTO Companion
 * Plugin URI: https://itto.test
 * Description: Companion plugin for Is This Thing On monitoring service
 * Version: 1.0.3
 * Author: Your Name
 * Author URI: https://itto.test
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: itto-companion
 *
 * @package itto-companion
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

define('ITTO_COMPANION_VERSION', '1.0.3');
define('ITTO_COMPANION_PATH', plugin_dir_path(__FILE__));

/**
 * Send a check-in ping to the ITTO service.
 *
 * @param string $event_hash The event hash from ITTO service.
 * @return bool True if check-in was successful, false otherwise.
 */
function itto_send_checkin(string $event_hash): bool {
    if (empty($event_hash)) {
        error_log('[ITTO Companion] Event hash is empty');
        return false;
    }

    $api_url = get_option('itto_companion_api_url', '');
    $site_hash = get_option('itto_companion_site_hash', '');
    
    if (empty($api_url)) {
        error_log('[ITTO Companion] API URL not configured');
        return false;
    }

    if (empty($site_hash)) {
        error_log('[ITTO Companion] Site Hash not configured');
        return false;
    }

    // Remove trailing slash if present
    $api_url = rtrim($api_url, '/');
    
    // Special handling for .test domains
    if (str_contains($api_url, '.test')) {
        // For .test domains, ensure we have http:// or https://
        if (!preg_match('~^https?://~i', $api_url)) {
            $api_url = 'http://' . $api_url;
        }
    } else {
        // For production domains, validate URL strictly
        if (!filter_var($api_url, FILTER_VALIDATE_URL)) {
            error_log('[ITTO Companion] Invalid API URL format: ' . $api_url);
            return false;
        }
    }

    try {
        // Construct the full endpoint URL
        $endpoint_url = sprintf(
            '%s/wp-json/itto/v1/event/%s?site_hash=%s',
            $api_url,
            urlencode($event_hash),
            urlencode($site_hash)
        );

        $response = wp_remote_get(
            $endpoint_url,
            [
                'headers' => [
                    'User-Agent' => 'ITTO-Companion/' . ITTO_COMPANION_VERSION,
                ],
                'timeout' => 15,
                'sslverify' => !str_contains($api_url, '.test'),
                'blocking' => true,
                'reject_unsafe_urls' => false,
            ]
        );

        if (is_wp_error($response)) {
            error_log(sprintf(
                '[ITTO Companion] Check-in failed for event %s: %s',
                $event_hash,
                $response->get_error_message()
            ));
            return false;
        }

        $code = wp_remote_retrieve_response_code($response);
        if ($code !== 200) {
            error_log(sprintf(
                '[ITTO Companion] Check-in failed for event %s with code %d',
                $event_hash,
                $code
            ));
            return false;
        }

        return true;
    } catch (Exception $e) {
        error_log(sprintf(
            '[ITTO Companion] Unexpected error during check-in for event %s: %s',
            $event_hash,
            $e->getMessage()
        ));
        return false;
    }
}

// Include admin functionality
require_once ITTO_COMPANION_PATH . 'includes/admin.php';
