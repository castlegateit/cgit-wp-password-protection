<?php

/**
 * Plugin Name:  Castlegate IT WP Password Protection
 * Plugin URI:   https://github.com/castlegateit/cgit-wp-password-protection
 * Description:  Password protect a WordPress site.
 * Version:      1.0.0
 * Requires PHP: 8.2
 * Author:       Castlegate IT
 * Author URI:   https://www.castlegateit.co.uk/
 * License:      MIT
 * Update URI:   https://github.com/castlegateit/cgit-wp-password-protection
 */

if (!defined('ABSPATH')) {
    wp_die('Access denied');
}

define('CGIT_WP_PASSWORD_PROTECTION_VERSION', '1.0.0');
define('CGIT_WP_PASSWORD_PROTECTION_PLUGIN_FILE', __FILE__);
define('CGIT_WP_PASSWORD_PROTECTION_PLUGIN_DIR', __DIR__);

// Restrict access to the site and/or print the site password form based on the
// current plugin settings.
add_action('init', function () {
    if (is_admin()) {
        return;
    }

    $mode = get_option('cgit_wp_password_protection_mode');

    // Restrict site to logged in users
    if ($mode === 'login') {
        if (!is_user_logged_in()) {
            wp_die('You must be logged in to view this site.', 'Access Denied', 403);
        }

        return;
    }

    // Password protect site
    if ($mode === 'password') {
        if (isset($_COOKIE['cgit_wp_password_protection'])) {
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

            $error = 'Incorrect password';
        }

        http_response_code(403);
        include CGIT_WP_PASSWORD_PROTECTION_PLUGIN_DIR . '/views/password-page.php';
        exit;
    }
});

// Show an admin notice when access to the site is restricted
add_action('admin_notices', function () {
    $message = match (get_option('cgit_wp_password_protection_mode')) {
        'login' => 'This site is currently restricted to logged in users.',
        'password' => 'This site is currently password protected.',
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

    if (!in_array($mode, ['disabled', 'login', 'password'])) {
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
        'Password Protection',
        'Password Protection',
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

            if (!$mode || !in_array($mode, ['disabled', 'login', 'password'])) {
                $mode = 'disabled';
            }

            if (!is_string($password)) {
                $password = '';
            }

            include CGIT_WP_PASSWORD_PROTECTION_PLUGIN_DIR . '/views/settings.php';
        }
    );
});
