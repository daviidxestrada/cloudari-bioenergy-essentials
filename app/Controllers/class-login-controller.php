<?php

if (!defined('ABSPATH')) {
    exit;
}

final class Cloudari_BioEnergy_Controller_Login {
    public static function register() {
        add_shortcode('cloudari_bioenergy_login', array(__CLASS__, 'render'));
        add_filter('body_class', array(__CLASS__, 'body_class'));
        add_filter('hello_elementor_page_title', array(__CLASS__, 'hide_hello_theme_title'));
        add_action('wp_head', array(__CLASS__, 'print_title_styles'));
    }

    public static function render() {
        $redirect_to = isset($_GET['redirect_to'])
            ? esc_url_raw(rawurldecode(wp_unslash((string) $_GET['redirect_to'])))
            : home_url('/eera-bioenergy-templates-logo/');

        if (Cloudari_BioEnergy_Model_Session_Repository::is_logged_in()) {
            return Cloudari_BioEnergy_Core_View::render(
                'public/signed-in.php',
                array(
                    'logout_url' => wp_nonce_url(
                        add_query_arg('cloudari_bioenergy_logout', '1', Cloudari_BioEnergy_Model_Session_Repository::login_url()),
                        'cloudari_bioenergy_logout'
                    ),
                )
            );
        }

        return Cloudari_BioEnergy_Core_View::render(
            'public/login-form.php',
            array(
                'action_url' => Cloudari_BioEnergy_Model_Session_Repository::login_url($redirect_to),
                'redirect_to' => $redirect_to,
                'message' => self::message(),
            )
        );
    }

    public static function body_class($classes) {
        if (self::is_login_page()) {
            $classes[] = 'cloudari-bioenergy-login-page';
        }

        return $classes;
    }

    public static function hide_hello_theme_title($display) {
        return self::is_login_page() ? false : $display;
    }

    public static function print_title_styles() {
        if (!self::is_login_page()) {
            return;
        }
        ?>
        <style>
            .cloudari-bioenergy-login-page .page-header,
            .cloudari-bioenergy-login-page .entry-header,
            .cloudari-bioenergy-login-page .entry-title,
            .cloudari-bioenergy-login-page .page-title,
            .cloudari-bioenergy-login-page .wp-block-post-title {
                display: none !important;
            }
        </style>
        <?php
    }

    private static function is_login_page() {
        if (!is_singular('page')) {
            return false;
        }

        $page_id = Cloudari_BioEnergy_Model_Settings::login_page_id();
        if ($page_id && (int) get_queried_object_id() === (int) $page_id) {
            return true;
        }

        return Cloudari_BioEnergy_Model_Settings::login_slug() === get_post_field('post_name', get_queried_object_id());
    }

    private static function message() {
        if (isset($_GET['cloudari_login'])) {
            return '<div class="cloudari-login-message cloudari-login-error">Invalid username or password.</div>';
        }

        if (isset($_GET['cloudari_logged_out'])) {
            return '<div class="cloudari-login-message">You have been signed out.</div>';
        }

        return '';
    }
}
