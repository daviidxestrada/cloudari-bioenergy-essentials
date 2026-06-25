<?php

if (!defined('ABSPATH')) {
    exit;
}

final class Cloudari_BioEnergy_Controller_Admin_Members {
    public static function register() {
        add_action('admin_menu', array(__CLASS__, 'register_menu'));
    }

    public static function register_menu() {
        $labels = self::labels();
        add_users_page(
            $labels['page_title'],
            $labels['menu_title'],
            'manage_options',
            'cloudari-bioenergy-essentials',
            array(__CLASS__, 'render')
        );
    }

    public static function render() {
        if (!current_user_can('manage_options')) {
            return;
        }

        $labels = self::labels();
        $notice = self::handle_post($labels);

        echo Cloudari_BioEnergy_Core_View::render(
            'admin/members-access.php',
            array(
                'notice' => $notice,
                'members' => Cloudari_BioEnergy_Model_Member_Repository::all(),
                'labels' => $labels,
            )
        );
    }

    private static function handle_post(array $labels) {
        if ('POST' !== (isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : '')) {
            return '';
        }

        check_admin_referer('cloudari_bioenergy_members');
        $action = isset($_POST['cloudari_bioenergy_action']) ? sanitize_key(wp_unslash((string) $_POST['cloudari_bioenergy_action'])) : '';

        if ('delete_member' === $action) {
            $username = isset($_POST['current_username']) ? Cloudari_BioEnergy_Model_Member_Repository::normalize_username(wp_unslash((string) $_POST['current_username'])) : '';
            if ($username) {
                Cloudari_BioEnergy_Model_Member_Repository::delete_many(array($username));
                return self::notice($labels['member_deleted']);
            }

            return self::notice($labels['invalid_user'], 'error');
        }

        $username = isset($_POST['member_username']) ? Cloudari_BioEnergy_Model_Member_Repository::normalize_username(wp_unslash((string) $_POST['member_username'])) : '';
        $password = isset($_POST['member_password']) ? (string) wp_unslash($_POST['member_password']) : '';

        if ('update_member' === $action) {
            $current_username = isset($_POST['current_username']) ? Cloudari_BioEnergy_Model_Member_Repository::normalize_username(wp_unslash((string) $_POST['current_username'])) : '';
            if (Cloudari_BioEnergy_Model_Member_Repository::update_member($current_username, $username, $password)) {
                return self::notice($labels['member_updated']);
            }

            return self::notice($labels['member_update_failed'], 'error');
        }

        if ('add_member' === $action) {
            if (Cloudari_BioEnergy_Model_Member_Repository::upsert($username, $password)) {
                return self::notice($labels['member_added']);
            }

            return self::notice($labels['member_add_failed'], 'error');
        }

        return '';
    }

    private static function notice($message, $type = 'success') {
        $class = 'error' === $type ? 'notice notice-error' : 'notice notice-success';
        return '<div class="' . esc_attr($class) . '"><p>' . esc_html($message) . '</p></div>';
    }

    private static function labels() {
        return array(
            'page_title' => 'BioEnergy Access',
            'menu_title' => 'BioEnergy Access',
            'intro' => 'Manage private members-area users. These accounts are not WordPress users.',
            'users_title' => 'Users',
            'add_title' => 'Add user',
            'username' => 'Username',
            'new_username' => 'Username',
            'new_password' => 'New password',
            'password' => 'Password',
            'created' => 'Created',
            'actions' => 'Actions',
            'save' => 'Save',
            'delete' => 'Delete',
            'add_user' => 'Add user',
            'no_members' => 'No users yet.',
            'leave_blank' => 'Leave blank to keep the current password.',
            'member_added' => 'User added.',
            'member_updated' => 'User updated.',
            'member_deleted' => 'User deleted.',
            'member_add_failed' => 'Could not add the user. Check username and password.',
            'member_update_failed' => 'Could not update the user. Check that the user exists and the new username is not already taken.',
            'invalid_user' => 'Invalid user.',
        );
    }
}
