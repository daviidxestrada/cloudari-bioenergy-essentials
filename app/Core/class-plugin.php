<?php

if (!defined('ABSPATH')) {
    exit;
}

final class Cloudari_BioEnergy_Core_Plugin {
    public static function init() {
        Cloudari_BioEnergy_Core_Installer::register();
        Cloudari_BioEnergy_Controller_Auth::register();
        Cloudari_BioEnergy_Controller_Access::register();
        Cloudari_BioEnergy_Controller_Admin_Members::register();
        Cloudari_BioEnergy_Controller_Contact_Widgets::register();
        Cloudari_BioEnergy_Controller_Login::register();
        Cloudari_BioEnergy_Controller_Menu::register();
    }
}
