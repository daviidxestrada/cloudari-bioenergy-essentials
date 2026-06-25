<?php

if (!defined('ABSPATH')) {
    exit;
}

final class Cloudari_BioEnergy_Core_View {
    public static function render($template, array $data = array()) {
        $path = CLOUDARI_BIOENERGY_ESSENTIALS_VIEW_DIR . ltrim((string) $template, '/');

        if (!file_exists($path)) {
            return '';
        }

        ob_start();
        extract($data, EXTR_SKIP);
        include $path;
        return (string) ob_get_clean();
    }
}
