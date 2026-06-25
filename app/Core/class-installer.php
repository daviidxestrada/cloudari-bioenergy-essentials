<?php

if (!defined('ABSPATH')) {
    exit;
}

final class Cloudari_BioEnergy_Core_Installer {
    public static function register() {
        add_action('init', array(__CLASS__, 'maybe_upgrade'), 0);
    }

    public static function activate() {
        self::install_defaults();
    }

    public static function maybe_upgrade() {
        if (get_option(Cloudari_BioEnergy_Model_Settings::OPTION_VERSION) !== CLOUDARI_BIOENERGY_ESSENTIALS_VERSION) {
            self::install_defaults();
        }
    }

    public static function install_defaults() {
        if (false === get_option(Cloudari_BioEnergy_Model_Settings::OPTION_SLUGS, false)) {
            update_option(Cloudari_BioEnergy_Model_Settings::OPTION_SLUGS, Cloudari_BioEnergy_Model_Settings::default_slugs(), false);
        }

        if (false === get_option(Cloudari_BioEnergy_Model_Settings::OPTION_USERS, false)) {
            update_option(Cloudari_BioEnergy_Model_Settings::OPTION_USERS, array(), false);
        }
        Cloudari_BioEnergy_Model_Member_Repository::normalize_roles();

        if (false === get_option(Cloudari_BioEnergy_Model_Settings::OPTION_LOGIN_SLUG, false)) {
            update_option(Cloudari_BioEnergy_Model_Settings::OPTION_LOGIN_SLUG, 'members-login', false);
        }

        self::remove_legacy_role_access();
        self::ensure_login_page();
        update_option(Cloudari_BioEnergy_Model_Settings::OPTION_VERSION, CLOUDARI_BIOENERGY_ESSENTIALS_VERSION, false);
    }

    public static function ensure_login_page() {
        $page_id = Cloudari_BioEnergy_Model_Settings::login_page_id();
        if ($page_id && 'page' === get_post_type($page_id)) {
            self::hide_login_page_title((int) $page_id);
            return $page_id;
        }

        $slug = Cloudari_BioEnergy_Model_Settings::login_slug();
        $existing = get_page_by_path($slug);
        if ($existing instanceof WP_Post) {
            Cloudari_BioEnergy_Model_Settings::set_login_page_id((int) $existing->ID);
            self::hide_login_page_title((int) $existing->ID);
            return (int) $existing->ID;
        }

        $page_id = wp_insert_post(
            array(
                'post_title' => 'Members Area Login',
                'post_name' => $slug,
                'post_content' => '[cloudari_bioenergy_login]',
                'post_status' => 'publish',
                'post_type' => 'page',
                'comment_status' => 'closed',
                'ping_status' => 'closed',
            ),
            true
        );

        if (!is_wp_error($page_id)) {
            Cloudari_BioEnergy_Model_Settings::set_login_page_id((int) $page_id);
            self::hide_login_page_title((int) $page_id);
            return (int) $page_id;
        }

        return 0;
    }

    private static function hide_login_page_title($page_id) {
        $settings = get_post_meta($page_id, '_elementor_page_settings', true);
        if (!is_array($settings)) {
            $settings = array();
        }

        $settings['hide_title'] = 'yes';
        update_post_meta($page_id, '_elementor_page_settings', $settings);
    }

    private static function remove_legacy_role_access() {
        foreach (array('administrator', 'editor', 'bioenergy_member') as $role_name) {
            $role = get_role($role_name);
            if ($role && $role->has_cap('cloudari_access_bioenergy_members_area')) {
                $role->remove_cap('cloudari_access_bioenergy_members_area');
            }
        }

        if (get_role('bioenergy_member')) {
            remove_role('bioenergy_member');
        }
    }
}
