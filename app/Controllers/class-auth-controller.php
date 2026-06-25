<?php

if (!defined('ABSPATH')) {
    exit;
}

final class Cloudari_BioEnergy_Controller_Auth {
    public static function register() {
        add_action('init', array(__CLASS__, 'handle'), 1);
    }

    public static function handle() {
        if (isset($_GET['cloudari_bioenergy_logout'])) {
            self::logout();
            return;
        }

        if ('POST' === (isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : '') && isset($_POST['cloudari_bioenergy_login'])) {
            self::login();
        }
    }

    private static function logout() {
        Cloudari_BioEnergy_Controller_Access::disable_page_cache();
        nocache_headers();

        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash((string) $_GET['_wpnonce'])), 'cloudari_bioenergy_logout')) {
            wp_safe_redirect(Cloudari_BioEnergy_Model_Session_Repository::login_url('', array('cloudari_login' => 'invalid')));
            exit;
        }

        Cloudari_BioEnergy_Model_Session_Repository::logout();
        wp_safe_redirect(Cloudari_BioEnergy_Model_Session_Repository::login_url('', array('cloudari_logged_out' => '1')));
        exit;
    }

    private static function login() {
        Cloudari_BioEnergy_Controller_Access::disable_page_cache();
        nocache_headers();

        $redirect_to = isset($_POST['redirect_to']) ? esc_url_raw(wp_unslash((string) $_POST['redirect_to'])) : '';
        $login_url = Cloudari_BioEnergy_Model_Session_Repository::login_url($redirect_to);

        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash((string) $_POST['_wpnonce'])), 'cloudari_bioenergy_login')) {
            wp_safe_redirect(add_query_arg('cloudari_login', 'invalid', $login_url));
            exit;
        }

        $username = isset($_POST['member_username']) ? Cloudari_BioEnergy_Model_Member_Repository::normalize_username(wp_unslash((string) $_POST['member_username'])) : '';
        $password = isset($_POST['member_password']) ? (string) wp_unslash($_POST['member_password']) : '';

        if (!Cloudari_BioEnergy_Model_Member_Repository::verify($username, $password)) {
            wp_safe_redirect(add_query_arg('cloudari_login', 'failed', $login_url));
            exit;
        }

        Cloudari_BioEnergy_Model_Session_Repository::start($username);
        $fallback = Cloudari_BioEnergy_Model_Session_Repository::default_private_url();
        $redirect_to = wp_validate_redirect($redirect_to, $fallback);
        wp_safe_redirect(Cloudari_BioEnergy_Model_Session_Repository::private_url($redirect_to));
        exit;
    }
}
