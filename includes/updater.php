<?php
/**
 * GitHub update integration for Cloudari BioEnergy Essentials.
 *
 * @package CloudariBioEnergyEssentials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cloudari_bioenergy_puc = __DIR__ . '/../plugin-update-checker/plugin-update-checker.php';

if ( file_exists( $cloudari_bioenergy_puc ) ) {
	require_once $cloudari_bioenergy_puc;

	$cloudari_bioenergy_update_checker = \YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
		'https://github.com/daviidxestrada/cloudari-bioenergy-essentials',
		dirname( __DIR__ ) . '/cloudari-bioenergy-essentials.php',
		'cloudari-bioenergy-essentials'
	);

	$cloudari_bioenergy_update_checker->setBranch( 'main' );
	$cloudari_bioenergy_update_checker->getVcsApi()->enableReleaseAssets( '/\.zip($|[?&#])/i' );
}
