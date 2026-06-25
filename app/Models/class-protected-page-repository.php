<?php

if (!defined('ABSPATH')) {
    exit;
}

final class Cloudari_BioEnergy_Model_Protected_Page_Repository {
    public static function is_protected($post) {
        return ($post instanceof WP_Post)
            && 'page' === $post->post_type
            && in_array($post->post_name, Cloudari_BioEnergy_Model_Settings::protected_slugs(), true);
    }

    public static function ids() {
        $ids = array();

        foreach (Cloudari_BioEnergy_Model_Settings::protected_slugs() as $slug) {
            $page = get_page_by_path($slug);
            if ($page instanceof WP_Post) {
                $ids[] = (int) $page->ID;
            }
        }

        return $ids;
    }

    public static function url_is_protected($url) {
        $url_slugs = self::slugs_from_url($url);
        if (!$url_slugs) {
            return false;
        }

        return (bool) array_intersect($url_slugs, Cloudari_BioEnergy_Model_Settings::protected_slugs());
    }

    private static function slugs_from_url($url) {
        $url = trim((string) $url);
        if ('' === $url || '#' === $url || 0 === stripos($url, 'mailto:') || 0 === stripos($url, 'tel:')) {
            return array();
        }

        $host = wp_parse_url($url, PHP_URL_HOST);
        $home_host = wp_parse_url(home_url('/'), PHP_URL_HOST);
        if ($host && $home_host && 0 !== strcasecmp($host, $home_host)) {
            return array();
        }

        $path = wp_parse_url($url, PHP_URL_PATH);
        if (!$path && '/' === substr($url, 0, 1)) {
            $path = $url;
        }

        $parts = array_values(array_filter(explode('/', trim((string) $path, '/'))));
        return array_values(array_filter(array_map('sanitize_title', $parts)));
    }
}
