<?php

if (!defined('ABSPATH')) {
    exit;
}

final class Cloudari_BioEnergy_Model_Member_Repository {
    const ROLE_MEMBER = 'Member';

    public static function all() {
        $members = get_option(Cloudari_BioEnergy_Model_Settings::OPTION_USERS, array());
        if (!is_array($members)) {
            return array();
        }

        $normalized = array();
        foreach ($members as $username => $member) {
            if (!is_array($member)) {
                continue;
            }

            $username = self::normalize_username($username);
            if (!$username) {
                continue;
            }

            $member['role'] = self::ROLE_MEMBER;
            $normalized[$username] = $member;
        }

        return $normalized;
    }

    public static function normalize_username($username) {
        return sanitize_user(strtolower(trim((string) $username)), true);
    }

    public static function verify($username, $password) {
        $username = self::normalize_username($username);
        $members = self::all();

        if (!$username || empty($members[$username]['hash']) || !empty($members[$username]['disabled'])) {
            return false;
        }

        $valid = password_verify((string) $password, (string) $members[$username]['hash']);
        if ($valid && password_needs_rehash((string) $members[$username]['hash'], PASSWORD_BCRYPT, array('cost' => Cloudari_BioEnergy_Model_Settings::BCRYPT_COST))) {
            $members[$username]['hash'] = self::hash_password((string) $password);
            $members[$username]['updated_at'] = time();
            self::save($members);
        }

        return $valid;
    }

    public static function upsert($username, $password) {
        $username = self::normalize_username($username);
        $password = (string) $password;

        if (!$username || '' === $password) {
            return false;
        }

        $members = self::all();
        $created_at = isset($members[$username]['created_at']) ? (int) $members[$username]['created_at'] : time();
        $members[$username] = array(
                'hash' => self::hash_password($password),
                'role' => self::ROLE_MEMBER,
                'created_at' => $created_at,
                'updated_at' => time(),
                'disabled' => false,
        );

        self::save($members);
        return true;
    }

    public static function update_member($current_username, $new_username, $password = '') {
        $current_username = self::normalize_username($current_username);
        $new_username = self::normalize_username($new_username);
        $password = (string) $password;
        $members = self::all();

        if (!$current_username || !$new_username || empty($members[$current_username])) {
            return false;
        }

        if ($new_username !== $current_username && isset($members[$new_username])) {
            return false;
        }

        $member = $members[$current_username];
        unset($members[$current_username]);

        if ('' !== $password) {
            $member['hash'] = self::hash_password($password);
        }

        $member['role'] = self::ROLE_MEMBER;
        $member['updated_at'] = time();
        $member['disabled'] = false;
        $members[$new_username] = $member;

        self::save($members);
        return true;
    }

    public static function current_username() {
        $session = Cloudari_BioEnergy_Model_Session_Repository::current_member();
        if (!is_array($session) || empty($session['username'])) {
            return '';
        }

        return self::normalize_username($session['username']);
    }

    public static function normalize_roles() {
        $members = self::all();
        if ($members) {
            self::save($members);
        }
    }

    public static function delete_many(array $usernames) {
        $members = self::all();

        foreach ($usernames as $username) {
            unset($members[self::normalize_username($username)]);
        }

        self::save($members);
    }

    private static function save(array $members) {
        update_option(Cloudari_BioEnergy_Model_Settings::OPTION_USERS, $members, false);
    }

    private static function hash_password($password) {
        return password_hash((string) $password, PASSWORD_BCRYPT, array('cost' => Cloudari_BioEnergy_Model_Settings::BCRYPT_COST));
    }
}
