<?php

/**
 * Plugin Name:  Castlegate IT WP Password Protection
 * Plugin URI:   https://github.com/castlegateit/cgit-wp-password-protection
 * Description:  Password protect a WordPress site.
 * Version:      1.2.1
 * Requires PHP: 8.2
 * Author:       Castlegate IT
 * Author URI:   https://www.castlegateit.co.uk/
 * License:      MIT
 * Update URI:   https://github.com/castlegateit/cgit-wp-password-protection
 */

if (!defined('ABSPATH')) {
    wp_die('Access denied');
}

define('CGIT_WP_PASSWORD_PROTECTION_VERSION', '1.2.1');
define('CGIT_WP_PASSWORD_PROTECTION_PLUGIN_FILE', __FILE__);
define('CGIT_WP_PASSWORD_PROTECTION_PLUGIN_DIR', __DIR__);

// Restrict access to the site and/or print the site password form based on the
// current plugin settings.
add_action('init', function () {
    $is_admin  = is_admin();
    $is_wp_cli = (defined('WP_CLI') && WP_CLI);
    $is_cron   = (defined('DOING_CRON') && DOING_CRON);
    $is_rest =  is_rest_api_request();

    if ($is_admin || $is_wp_cli || $is_cron || $is_rest) {
        return;
    }

    $mode = get_option('cgit_wp_password_protection_mode');

    switch ($mode) {
        // Restrict site to logged in users
        case 'login':
            if (is_user_logged_in() || is_login()) {
                return;
            }

            wp_die(__('You must be logged in to view this site.'), __('Access Denied'), 403);

        // Password protect site
        case 'password':
        case 'login_or_password':
            // Allow access to users who have submitted the password
            if (isset($_COOKIE['cgit_wp_password_protection'])) {
                return;
            }

            // Allow access to logged in users
            if ($mode === 'login_or_password' && (is_user_logged_in() || is_login())) {
                return;
            }

            $password = $_POST['password'] ?? null;
            $submitted = isset($_POST['cgit_wp_password_protection_submit']);
            $error = null;

            if ($submitted && is_string($password)) {
                if (get_option('cgit_wp_password_protection_password') === $password) {
                    setcookie('cgit_wp_password_protection', 1, time() + (60 * 60 * 24), '/');
                    return;
                }

                $error = __('Incorrect password');
            }

            http_response_code(403);
            include CGIT_WP_PASSWORD_PROTECTION_PLUGIN_DIR . '/views/password-page.php';
            exit;
    }
});

// Show an admin notice when access to the site is restricted
add_action('admin_notices', function () {
    $message = match (get_option('cgit_wp_password_protection_mode')) {
        'login' => __('This site is currently restricted to logged in users.'),
        'password' => __('This site is currently password protected.'),
        'login_or_password' => __('This site is currently password protected.'),
        default => null,
    };

    if (!$message) {
        return;
    }

    include CGIT_WP_PASSWORD_PROTECTION_PLUGIN_DIR . '/views/status-notice.php';
});

// Show an admin notice when the plugin settings have been updated
add_action('admin_init', function () {
    $form_id = $_POST['form_id'] ?? null;
    $nonce = $_POST['nonce'] ?? null;

    if (
        !current_user_can('activate_plugins') ||
        $form_id !== 'cgit_wp_password_protection' ||
        !wp_verify_nonce($nonce, 'cgit_wp_password_protection_nonce')
    ) {
        return;
    }

    $mode = $_POST['mode'] ?? null;
    $password = $_POST['password'] ?? null;

    if (!in_array($mode, ['disabled', 'login', 'password', 'login_or_password'])) {
        $mode = 'disabled';
    }

    update_option('cgit_wp_password_protection_mode', $mode);
    update_option('cgit_wp_password_protection_password', $password);

    add_action('admin_notices', function () {
        include CGIT_WP_PASSWORD_PROTECTION_PLUGIN_DIR . '/views/settings-saved-notice.php';
    });
});

// Create the plugin settings page
add_action('admin_menu', function () {
    add_submenu_page(
        'options-general.php',
        __('Password Protection'),
        __('Password Protection'),
        'activate_plugins',
        'cgit-wp-password-protection',
        function () {
            $nonce = wp_create_nonce('cgit_wp_password_protection_nonce');
            $mode = get_option('cgit_wp_password_protection_mode');
            $password = get_option('cgit_wp_password_protection_password');

            if (($_POST['form_id'] ?? null) === 'cgit_wp_password_protection') {
                $mode = $_POST['mode'] ?? $mode;
                $password = $_POST['password'] ?? $password;
            }

            if (!$mode || !in_array($mode, ['disabled', 'login', 'password', 'login_or_password'])) {
                $mode = 'disabled';
            }

            if (!is_string($password)) {
                $password = '';
            }

            include CGIT_WP_PASSWORD_PROTECTION_PLUGIN_DIR . '/views/settings.php';
        }
    );
});

// Detect REST API endpoint URLs
function is_rest_api_request(): bool {
    // Get path part of current request (no query string)
    $request_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    if (!$request_path) {
        return false;
    }

    // Get WP installation path relative to domain root
    $home_path = parse_url(home_url(), PHP_URL_PATH);
    if (!$home_path) {
        $home_path = '/';
    }

    // Ensure both paths have a leading slash and no trailing slash
    $request_path = '/' . ltrim($request_path, '/');
    $home_path = '/' . trim($home_path, '/');

    if ($home_path !== '/' && str_starts_with($request_path, $home_path)) {
        // Strip the subdirectory prefix so we're comparing relative paths
        $request_path = substr($request_path, strlen($home_path));
        if (!$request_path) {
            $request_path = '/';
        }
    }

    // Get REST prefix (default is 'wp-json')
    $prefix = function_exists('rest_get_url_prefix') ? rest_get_url_prefix() : 'wp-json';

    // Must start with /wp-json or exactly /wp-json
    if (str_starts_with($request_path, '/' . $prefix . '/') || $request_path === '/'.$prefix) {
        return true;
    }

    // Check old WooCommerce endpoint. Must start with but not be equal to /wc-api/
    if (class_exists('WooCommerce')) {
        if (str_starts_with($request_path, '/wc-api/') && $request_path !== '/wc-api/') {
            return true;
        }
    }

    // Fallback: ?rest_route=/ style requests (plain permalinks)
    if (isset($_GET['rest_route']) && str_starts_with($_GET['rest_route'], '/')) {
        return true;
    }

    return false;
}