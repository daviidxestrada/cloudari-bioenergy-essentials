<?php

if (!defined('ABSPATH')) {
    exit;
}

final class Cloudari_BioEnergy_Model_Settings {
    const OPTION_VERSION = 'cloudari_bioenergy_essentials_version';
    const OPTION_SLUGS = 'cloudari_bioenergy_protected_slugs';
    const OPTION_USERS = 'cloudari_bioenergy_members';
    const OPTION_LOGIN_PAGE_ID = 'cloudari_bioenergy_login_page_id';
    const OPTION_LOGIN_SLUG = 'cloudari_bioenergy_login_slug';
    const COOKIE_NAME = 'cloudari_bioenergy_session';
    const SESSION_PREFIX = 'cloudari_bioenergy_session_';
    const SESSION_TTL = 43200;
    const BCRYPT_COST = 12;

    public static function default_slugs() {
        return array(
            'eera-bioenergy-templates-logo',
            'core-documents',
            'joint-programme-steering-committee-meetings',
            'management-board-meetings',
            'technology-watch',
            'collaborative-project-generation',
        );
    }

    public static function protected_slugs() {
        $slugs = get_option(self::OPTION_SLUGS, self::default_slugs());
        if (!is_array($slugs)) {
            $slugs = preg_split('/[\r\n,]+/', (string) $slugs);
        }

        $slugs = array_filter(array_map('sanitize_title', $slugs));

        return array_values(array_unique(apply_filters('cloudari_bioenergy_protected_slugs', $slugs)));
    }

    public static function sanitize_slug_lines($raw) {
        $parts = preg_split('/[\r\n,]+/', (string) $raw);
        return array_values(array_unique(array_filter(array_map('sanitize_title', $parts ? $parts : array()))));
    }

    public static function login_slug() {
        $slug = sanitize_title((string) get_option(self::OPTION_LOGIN_SLUG, 'members-login'));
        return $slug ? $slug : 'members-login';
    }

    public static function login_page_id() {
        return (int) get_option(self::OPTION_LOGIN_PAGE_ID, 0);
    }

    public static function set_login_page_id($page_id) {
        update_option(self::OPTION_LOGIN_PAGE_ID, (int) $page_id, false);
    }
}
