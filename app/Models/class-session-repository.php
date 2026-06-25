<?php

if (!defined('ABSPATH')) {
    exit;
}

final class Cloudari_BioEnergy_Model_Session_Repository {
    private static $current_member = false;

    public static function start($username) {
        $token = self::random_token();
        $session = array(
            'username' => Cloudari_BioEnergy_Model_Member_Repository::normalize_username($username),
            'created_at' => time(),
            'expires_at' => time() + Cloudari_BioEnergy_Model_Settings::SESSION_TTL,
        );
        set_transient(
            self::session_key($token),
            $session,
            Cloudari_BioEnergy_Model_Settings::SESSION_TTL
        );
        self::set_cookie($token, time() + Cloudari_BioEnergy_Model_Settings::SESSION_TTL);
        self::$current_member = $session;
    }

    public static function logout() {
        $token = isset($_COOKIE[Cloudari_BioEnergy_Model_Settings::COOKIE_NAME])
            ? sanitize_text_field(wp_unslash((string) $_COOKIE[Cloudari_BioEnergy_Model_Settings::COOKIE_NAME]))
            : '';

        if ($token) {
            delete_transient(self::session_key($token));
        }

        self::set_cookie('', time() - YEAR_IN_SECONDS);
        self::$current_member = null;
    }

    public static function refresh_current_session() {
        $token = self::cookie_token();
        if (!$token) {
            return;
        }

        $session = self::current_member();
        if (!is_array($session)) {
            self::set_cookie('', time() - YEAR_IN_SECONDS);
            return;
        }

        $session['expires_at'] = time() + Cloudari_BioEnergy_Model_Settings::SESSION_TTL;
        set_transient(self::session_key($token), $session, Cloudari_BioEnergy_Model_Settings::SESSION_TTL);
        self::set_cookie($token, (int) $session['expires_at']);
        self::$current_member = $session;
    }

    public static function current_member() {
        if (false !== self::$current_member) {
            return self::$current_member;
        }

        $token = self::cookie_token();

        if (!$token || strlen($token) < 40) {
            self::$current_member = null;
            return null;
        }

        $session = get_transient(self::session_key($token));
        if (!is_array($session) || empty($session['username']) || (int) ($session['expires_at'] ?? 0) < time()) {
            self::$current_member = null;
            return null;
        }

        self::$current_member = $session;
        return $session;
    }

    public static function is_logged_in() {
        return null !== self::current_member();
    }

    public static function login_url($redirect_to = '', array $extra_args = array()) {
        $page_id = Cloudari_BioEnergy_Model_Settings::login_page_id();
        $url = $page_id ? get_permalink($page_id) : home_url('/' . Cloudari_BioEnergy_Model_Settings::login_slug() . '/');

        if ($redirect_to) {
            $extra_args['redirect_to'] = $redirect_to;
        }

        return add_query_arg($extra_args, $url);
    }

    public static function default_private_url() {
        $slugs = Cloudari_BioEnergy_Model_Settings::protected_slugs();
        $slug = $slugs ? $slugs[0] : 'eera-bioenergy-templates-logo';

        return home_url('/' . $slug . '/');
    }

    public static function private_url($url) {
        return add_query_arg('cloudari_private', '1', $url);
    }

    private static function cookie_token() {
        return isset($_COOKIE[Cloudari_BioEnergy_Model_Settings::COOKIE_NAME])
            ? sanitize_text_field(wp_unslash((string) $_COOKIE[Cloudari_BioEnergy_Model_Settings::COOKIE_NAME]))
            : '';
    }

    private static function random_token() {
        try {
            return bin2hex(random_bytes(32));
        } catch (Exception $exception) {
            return wp_generate_password(64, false, false);
        }
    }

    private static function session_key($token) {
        return Cloudari_BioEnergy_Model_Settings::SESSION_PREFIX . hash_hmac('sha256', (string) $token, wp_salt('auth'));
    }

    private static function set_cookie($value, $expires) {
        $name = Cloudari_BioEnergy_Model_Settings::COOKIE_NAME;
        $path = defined('COOKIEPATH') && COOKIEPATH ? COOKIEPATH : '/';
        $domain = defined('COOKIE_DOMAIN') ? COOKIE_DOMAIN : '';
        $secure = is_ssl();

        if (PHP_VERSION_ID >= 70300) {
            setcookie(
                $name,
                (string) $value,
                array(
                    'expires' => (int) $expires,
                    'path' => $path,
                    'domain' => $domain,
                    'secure' => $secure,
                    'httponly' => true,
                    'samesite' => 'Lax',
                )
            );
        } else {
            setcookie($name, (string) $value, (int) $expires, $path . '; samesite=Lax', $domain, $secure, true);
        }

        $_COOKIE[$name] = (string) $value;
    }
}
