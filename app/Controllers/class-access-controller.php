<?php

if (!defined('ABSPATH')) {
    exit;
}

final class Cloudari_BioEnergy_Controller_Access {
    public static function register() {
        add_action('init', array(__CLASS__, 'mark_session_requests_dynamic'), 0);
        add_action('init', array(__CLASS__, 'refresh_private_session'), 2);
        add_action('admin_init', array(__CLASS__, 'sync_private_seo_settings'));
        add_action('template_redirect', array(__CLASS__, 'protect_current_page'), 1);
        add_action('wp_head', array(__CLASS__, 'print_private_robots_meta'), 1);
        add_filter('robots_txt', array(__CLASS__, 'filter_robots_txt'), 20, 2);
        add_filter('rest_prepare_page', array(__CLASS__, 'protect_rest_page'), 10, 3);
        add_filter('rank_math/frontend/robots', array(__CLASS__, 'filter_rank_math_robots'));
        add_filter('rank_math/sitemap/enable_caching', '__return_false');
        add_filter('rank_math/sitemap/posts_to_exclude', array(__CLASS__, 'filter_rank_math_sitemap_post_ids'));
        add_filter('rank_math/sitemap/get_posts/where', array(__CLASS__, 'filter_rank_math_sitemap_where'), 10, 2);
        add_filter('rank_math/sitemap/post_count/where', array(__CLASS__, 'filter_rank_math_sitemap_where'), 10, 2);
        add_filter('rank_math/sitemap/entry', array(__CLASS__, 'filter_rank_math_sitemap_entry'), 10, 3);
        add_filter('wp_sitemaps_posts_query_args', array(__CLASS__, 'exclude_from_sitemaps'), 10, 2);
        add_action('pre_get_posts', array(__CLASS__, 'exclude_from_public_queries'));
    }

    public static function mark_session_requests_dynamic() {
        if (
            isset($_COOKIE[Cloudari_BioEnergy_Model_Settings::COOKIE_NAME])
            || isset($_GET['cloudari_bioenergy_logout'])
            || isset($_GET['cloudari_private'])
            || isset($_POST['cloudari_bioenergy_login'])
            || self::request_looks_private()
        ) {
            self::send_private_headers();
        }
    }

    public static function refresh_private_session() {
        if (isset($_COOKIE[Cloudari_BioEnergy_Model_Settings::COOKIE_NAME])) {
            Cloudari_BioEnergy_Model_Session_Repository::refresh_current_session();
        }
    }

    public static function protect_current_page() {
        if (!is_singular('page')) {
            return;
        }

        $post = get_queried_object();
        if ($post instanceof WP_Post && (int) $post->ID === Cloudari_BioEnergy_Model_Settings::login_page_id()) {
            self::send_private_headers();
            if (Cloudari_BioEnergy_Model_Session_Repository::is_logged_in() && isset($_GET['redirect_to'])) {
                $redirect_to = esc_url_raw(rawurldecode(wp_unslash((string) $_GET['redirect_to'])));
                if ($redirect_to) {
                    $fallback = Cloudari_BioEnergy_Model_Session_Repository::private_url(Cloudari_BioEnergy_Model_Session_Repository::default_private_url());
                    wp_safe_redirect(wp_validate_redirect(Cloudari_BioEnergy_Model_Session_Repository::private_url($redirect_to), $fallback));
                    exit;
                }
            }
            return;
        }

        if (!Cloudari_BioEnergy_Model_Protected_Page_Repository::is_protected($post)) {
            return;
        }

        self::send_private_headers();

        if (!Cloudari_BioEnergy_Model_Session_Repository::is_logged_in()) {
            wp_safe_redirect(Cloudari_BioEnergy_Model_Session_Repository::login_url(get_permalink($post)));
            exit;
        }
    }

    public static function protect_rest_page($response, $post, $request) {
        if (!Cloudari_BioEnergy_Model_Protected_Page_Repository::is_protected($post) || Cloudari_BioEnergy_Model_Session_Repository::is_logged_in()) {
            return $response;
        }

        self::send_private_headers();
        $data = $response->get_data();
        if (isset($data['content']['rendered'])) {
            $data['content']['rendered'] = '<p>Sign in to view this members-only content.</p>';
        }
        if (isset($data['excerpt']['rendered'])) {
            $data['excerpt']['rendered'] = '';
        }
        $response->set_status(401);
        $response->set_data($data);

        return $response;
    }

    public static function sync_private_seo_settings() {
        $ids = self::private_page_ids_for_seo();
        if (!$ids) {
            return;
        }

        foreach ($ids as $id) {
            $robots = get_post_meta($id, 'rank_math_robots', true);
            if (!is_array($robots)) {
                $robots = array();
            }

            $robots = array_values(array_diff($robots, array('index', 'follow')));
            foreach (array('noindex', 'nofollow', 'noarchive') as $directive) {
                if (!in_array($directive, $robots, true)) {
                    $robots[] = $directive;
                }
            }

            update_post_meta($id, 'rank_math_robots', array_values(array_unique($robots)));
            do_action('rank_math/sitemap/invalidate_object_type', 'post', $id);
        }

        self::sync_rank_math_sitemap_exclusions($ids);
        self::sync_physical_robots_file();
    }

    public static function print_private_robots_meta() {
        if (self::current_page_is_private_or_login()) {
            echo '<meta name="robots" content="noindex, nofollow, noarchive">' . "\n";
        }
    }

    public static function filter_robots_txt($output, $public) {
        return self::merge_robots_content($output);
    }

    private static function merge_robots_content($output) {
        $lines = preg_split('/\r\n|\r|\n/', trim((string) $output));
        $lines = array_values(array_filter(array_map('trim', $lines), 'strlen'));

        if (!$lines) {
            $lines = array('User-agent: *');
        }

        if (!preg_grep('/^Disallow:\s*\/wp-admin\/?$/i', $lines)) {
            $lines[] = 'Disallow: /wp-admin/';
        }

        if (!preg_grep('/^Allow:\s*\/wp-admin\/admin-ajax\.php$/i', $lines)) {
            $lines[] = 'Allow: /wp-admin/admin-ajax.php';
        }

        $sitemap = 'Sitemap: ' . home_url('/sitemap_index.xml');
        $lines = array_values(array_filter($lines, function ($line) {
            return 0 !== stripos($line, 'Sitemap:');
        }));
        $lines[] = $sitemap;

        return implode("\n", array_unique($lines)) . "\n";
    }

    public static function filter_rank_math_robots($robots) {
        if (!self::current_page_is_private_or_login()) {
            return $robots;
        }

        unset($robots['index'], $robots['follow']);
        $robots['noindex'] = 'noindex';
        $robots['nofollow'] = 'nofollow';
        $robots['noarchive'] = 'noarchive';

        return $robots;
    }

    public static function filter_rank_math_sitemap_post_ids($ids) {
        return array_values(array_unique(array_merge((array) $ids, self::private_page_ids_for_seo())));
    }

    public static function filter_rank_math_sitemap_where($where, $post_types) {
        if (!self::rank_math_sitemap_query_targets_pages($post_types)) {
            return $where;
        }

        $ids = array_filter(array_map('absint', self::private_page_ids_for_seo()));
        if (!$ids) {
            return $where;
        }

        return $where . ' AND p.ID NOT IN (' . implode(',', $ids) . ')';
    }

    public static function filter_rank_math_sitemap_entry($url, $type, $object) {
        if ('post' !== $type || !($object instanceof WP_Post)) {
            return $url;
        }

        if (
            Cloudari_BioEnergy_Model_Protected_Page_Repository::is_protected($object)
            || (int) $object->ID === Cloudari_BioEnergy_Model_Settings::login_page_id()
        ) {
            return false;
        }

        return $url;
    }

    public static function exclude_from_sitemaps($args, $post_type) {
        if ('page' !== $post_type) {
            return $args;
        }

        $ids = self::private_page_ids_for_seo();
        if ($ids) {
            $args['post__not_in'] = array_values(array_unique(array_merge($args['post__not_in'] ?? array(), $ids)));
        }

        return $args;
    }

    public static function exclude_from_public_queries($query) {
        if (is_admin() || Cloudari_BioEnergy_Model_Session_Repository::is_logged_in() || !$query->is_main_query()) {
            return;
        }

        if (!$query->is_search() && !$query->is_archive() && !$query->is_home()) {
            return;
        }

        $ids = Cloudari_BioEnergy_Model_Protected_Page_Repository::ids();
        if ($ids) {
            $query->set('post__not_in', array_values(array_unique(array_merge((array) $query->get('post__not_in'), $ids))));
        }
    }

    public static function disable_page_cache() {
        if (!defined('DONOTCACHEPAGE')) {
            define('DONOTCACHEPAGE', true);
        }
    }

    public static function send_private_headers() {
        self::disable_page_cache();

        if (function_exists('do_action')) {
            do_action('litespeed_control_set_nocache', 'cloudari_bioenergy_private');
        }

        if (headers_sent()) {
            return;
        }

        nocache_headers();
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0, private', false);
        header('Pragma: no-cache', false);
        header('Expires: Wed, 11 Jan 1984 05:00:00 GMT', false);
        header('X-Robots-Tag: noindex, nofollow, noarchive', false);
        header('Surrogate-Control: no-store', false);
        header('Vary: Cookie', false);
    }

    private static function current_page_is_private_or_login() {
        if (!is_singular('page')) {
            return false;
        }

        $post = get_queried_object();
        if ($post instanceof WP_Post && Cloudari_BioEnergy_Model_Protected_Page_Repository::is_protected($post)) {
            return true;
        }

        $page_id = Cloudari_BioEnergy_Model_Settings::login_page_id();
        if ($page_id && (int) get_queried_object_id() === (int) $page_id) {
            return true;
        }

        return Cloudari_BioEnergy_Model_Settings::login_slug() === get_post_field('post_name', get_queried_object_id());
    }

    private static function private_page_ids_for_seo() {
        $ids = Cloudari_BioEnergy_Model_Protected_Page_Repository::ids();
        $login_page_id = Cloudari_BioEnergy_Model_Settings::login_page_id();

        if (!$login_page_id) {
            $login_page = get_page_by_path(Cloudari_BioEnergy_Model_Settings::login_slug());
            if ($login_page instanceof WP_Post) {
                $login_page_id = (int) $login_page->ID;
            }
        }

        if ($login_page_id) {
            $ids[] = (int) $login_page_id;
        }

        return array_values(array_unique(array_filter(array_map('absint', $ids))));
    }

    private static function sync_rank_math_sitemap_exclusions($ids) {
        $options = get_option('rank-math-options-sitemap', array());
        if (!is_array($options)) {
            return;
        }

        $existing = wp_parse_id_list($options['exclude_posts'] ?? '');
        $merged = array_values(array_unique(array_merge($existing, $ids)));
        sort($merged);

        if ($merged !== $existing) {
            $options['exclude_posts'] = implode(',', $merged);
            update_option('rank-math-options-sitemap', $options, false);
        }
    }

    private static function sync_physical_robots_file() {
        if (!defined('ABSPATH')) {
            return;
        }

        $path = ABSPATH . 'robots.txt';
        if (!file_exists($path) || !is_readable($path) || !is_writable($path)) {
            return;
        }

        $current = (string) file_get_contents($path);
        $updated = self::merge_robots_content($current);
        if ($updated !== $current) {
            file_put_contents($path, $updated, LOCK_EX);
        }
    }

    private static function rank_math_sitemap_query_targets_pages($post_types) {
        if ('page' === $post_types) {
            return true;
        }

        return is_array($post_types) && in_array('page', $post_types, true);
    }

    private static function request_looks_private() {
        $request_uri = isset($_SERVER['REQUEST_URI']) ? (string) wp_unslash($_SERVER['REQUEST_URI']) : '';
        if ('' === $request_uri) {
            return false;
        }

        $path = wp_parse_url($request_uri, PHP_URL_PATH);
        $parts = array_values(array_filter(explode('/', trim((string) $path, '/'))));
        $slugs = array_values(array_filter(array_map('sanitize_title', $parts)));

        return (bool) array_intersect($slugs, Cloudari_BioEnergy_Model_Settings::protected_slugs());
    }
}
