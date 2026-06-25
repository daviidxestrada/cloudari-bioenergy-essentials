<?php
/**
 * Plugin Name: Cloudari BioEnergy Essentials
 * Description: Adds isolated bcrypt login and members-area access control for the EERA Bioenergy Elementor kit.
 * Version: 1.4.13
 * Author: Cloudari
 * Text Domain: cloudari-bioenergy-essentials
 * Update URI: https://github.com/daviidxestrada/cloudari-bioenergy-essentials
 */

if (!defined('ABSPATH')) {
    exit;
}

define('CLOUDARI_BIOENERGY_ESSENTIALS_VERSION', '1.4.13');
define('CLOUDARI_BIOENERGY_ESSENTIALS_FILE', __FILE__);
define('CLOUDARI_BIOENERGY_ESSENTIALS_DIR', plugin_dir_path(__FILE__));
define('CLOUDARI_BIOENERGY_ESSENTIALS_VIEW_DIR', CLOUDARI_BIOENERGY_ESSENTIALS_DIR . 'app/Views/');

require_once CLOUDARI_BIOENERGY_ESSENTIALS_DIR . 'includes/updater.php';

/**
 * Maps the legacy recovered-document URLs to their exact Media Library files.
 */
function cloudari_bioenergy_recovered_document_map() {
    $uploads = content_url('/uploads/2026/05/');

    return array(
        'publications-brochure-and-flyer-brochure-eera-bioenergy-2023.pdf' => $uploads . 'Brochure-EERA-Bioenergy-2023.pdf',
        'publications-brochure-and-flyer-flyer-eera-bioenergy-2023.pdf' => $uploads . 'Flyer-EERA-Bioenergy-2023.pdf',
        'publications-eera-bioenergy-newsletters-eebionews-15-spring-summer2021.pdf' => $uploads . 'eebionews_15_spring_summer2021.pdf',
        'publications-eera-bioenergy-newsletters-eebionews-16-autumn-winter2021.pdf' => $uploads . 'eebionews_16_autumn_winter2021.pdf',
        'publications-eera-bioenergy-newsletters-eebionews-17-spring-summer2022.pdf' => $uploads . 'eebionews_17_spring_summer2022.pdf',
        'publications-eera-bioenergy-newsletters-eebionews-18-autumn-winter2022.pdf' => $uploads . 'eebionews_18_autumn_winter2022.pdf',
        'publications-eera-bioenergy-newsletters-eebionews-19-spring-summer2023.pdf' => $uploads . 'eebionews_19_spring_summer2023.pdf',
        'publications-eera-bioenergy-newsletters-eebionews-20-autumn-winter2023.pdf' => $uploads . 'eebionews_20_autumn_winter2023.pdf',
        'publications-eera-bioenergy-newsletters-eebionews-21-spring-summer2024.pdf' => $uploads . 'eebionews_21_spring_summer2024.pdf',
        'publications-eera-bioenergy-newsletters-eebionews-22-autumn-winter2024.pdf' => $uploads . 'eebionews_22_autumn_winter2024.pdf',
        'publications-eera-bioenergy-newsletters-eebionews-23-spring-summer2025.pdf' => $uploads . 'eebionews_23_spring_summer2025.pdf',
        'publications-eera-bioenergy-newsletters-eebionews-24-autumn-winter2025.pdf' => $uploads . 'eebionews_24_autumn_winter2025.pdf',
        'publications-position-papers-eera-bioenergy-feedback-eu-bioeconomy-strategy-23-06-2025.pdf' => $uploads . 'EERA-Bioenergy-feedback-EU-Bioeconomy-Strategy-23.06.2025.pdf',
        'publications-position-papers-eera-bioenergy-fp10-position-paper-17-07-2025.pdf' => $uploads . 'EERA-Bioenergy-FP10-position-paper-17.07.2025.pdf',
        'publications-position-papers-eera-bioenergy-r-d-i-gaps-2024-executive-summary.pdf' => $uploads . 'EERA-Bioenergy-RDI-Gaps-2024-Executive-Summary.pdf',
        'publications-position-papers-eera-bioenergy-r-d-i-gaps-2024.pdf' => $uploads . 'EERA-Bioenergy-RDI-Gaps-2024.pdf',
        'publications-press-releases-eera-bioenergy-press-release-jun-2024.pdf' => $uploads . 'EERA-Bioenergy-Press-release-JUN-2024.pdf',
        'publications-press-releases-eera-bioenergy-press-release-mar-2019.pdf' => $uploads . 'EERA-Bioenergy-Press-release-MAR-2019.pdf',
        'publications-press-releases-eera-bioenergy-press-release-nov-2019.pdf' => $uploads . 'EERA-Bioenergy-Press-release-NOV-2019.pdf',
        'publications-sria-2020-eera-bioenergy-sria-2020.pdf' => $uploads . 'EERA-Bioenergy-SRIA-2020.pdf',
    );
}

/**
 * Replace legacy plugin asset links while rendering posts and Elementor pages.
 */
function cloudari_bioenergy_replace_recovered_document_links($content) {
    $legacy_base = content_url('/plugins/cloudari-bioenergy-essentials/assets/recovered/');
    $legacy_path = '/wp-content/plugins/cloudari-bioenergy-essentials/assets/recovered/';

    foreach (cloudari_bioenergy_recovered_document_map() as $filename => $media_url) {
        $content = str_replace(array($legacy_base . $filename, $legacy_path . $filename), $media_url, $content);
        $content = str_replace(
            array('href="' . $media_url . '"', "href='" . $media_url . "'"),
            array(
                'href="' . $media_url . '" target="_blank" rel="noopener noreferrer"',
                "href='" . $media_url . "' target='_blank' rel='noopener noreferrer'",
            ),
            $content
        );
    }

    return $content;
}
add_filter('the_content', 'cloudari_bioenergy_replace_recovered_document_links', 20);

/**
 * Preserve shared/bookmarked legacy URLs with permanent redirects.
 */
function cloudari_bioenergy_redirect_recovered_documents() {
    $request_path = isset($_SERVER['REQUEST_URI']) ? wp_parse_url(wp_unslash($_SERVER['REQUEST_URI']), PHP_URL_PATH) : '';
    $legacy_path = '/wp-content/plugins/cloudari-bioenergy-essentials/assets/recovered/';

    if (strpos($request_path, $legacy_path) !== 0) {
        return;
    }

    $filename = basename($request_path);
    $documents = cloudari_bioenergy_recovered_document_map();

    if (isset($documents[$filename])) {
        wp_safe_redirect($documents[$filename], 301);
        exit;
    }
}
add_action('template_redirect', 'cloudari_bioenergy_redirect_recovered_documents', 1);

$cloudari_bioenergy_files = array(
    'app/Models/class-settings.php',
    'app/Models/class-member-repository.php',
    'app/Models/class-session-repository.php',
    'app/Models/class-protected-page-repository.php',
    'app/Core/class-view.php',
    'app/Core/class-installer.php',
    'app/Controllers/class-auth-controller.php',
    'app/Controllers/class-access-controller.php',
    'app/Controllers/class-admin-members-controller.php',
    'app/Controllers/class-login-controller.php',
    'app/Controllers/class-menu-controller.php',
    'app/Core/class-plugin.php',
);

foreach ($cloudari_bioenergy_files as $cloudari_bioenergy_file) {
    require_once CLOUDARI_BIOENERGY_ESSENTIALS_DIR . $cloudari_bioenergy_file;
}

register_activation_hook(CLOUDARI_BIOENERGY_ESSENTIALS_FILE, array('Cloudari_BioEnergy_Core_Installer', 'activate'));
Cloudari_BioEnergy_Core_Plugin::init();
