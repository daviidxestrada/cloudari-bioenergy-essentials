<?php
/**
 * Plugin Name: Cloudari BioEnergy Essentials
 * Description: Adds isolated bcrypt login and members-area access control for the EERA Bioenergy Elementor kit.
 * Version: 1.4.8
 * Author: Cloudari
 * Update URI: https://github.com/daviidxestrada/cloudari-bioenergy-essentials
 *
 * @package CloudariBioEnergyEssentials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/includes/updater.php';

/*
 * The live plugin code still needs to be imported from eerabioenergy.eu.
 * This repository currently contains the GitHub update wiring and vendored
 * Plugin Update Checker dependency only.
 */
