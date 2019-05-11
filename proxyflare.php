<?php
/**
 * Plugin Name: Proxyflare
 * Plugin URI: https://whitelabel.ltd
 * Description: Cloudflare functions without using a Global API Key. For use with Whitelabel Digital Sites
 * Version: 1.0
 * Author: Whitelabel Digital
 * Author URI: http://whitelabel.ltd
 * Licence: GPLv2 or later
 *
 * Copyright 2019 Whitelabel Digital ( whitelabel.ltd )
 */

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

define('PROXYFLARE_VERSION','1.0');
define('PROXYFLARE_FILE',__FILE__);

/**
 * The core plugin class
 */
require_once plugin_dir_path( PROXYFLARE_FILE ) . 'includes/class-proxyflare.php';

/**
 * Gets the main Class Instance
 * @return PROXYFLARE\PROXYFLARE
 */
function proxyflare() {

	// globals
	global $proxyflare;

	// initialize
	if( !isset($proxyflare) ) {
		$proxyflare = new \PROXYFLARE\PROXYFLARE();
		$proxyflare->init();
	}

	// return
	return $proxyflare;
}
proxyflare();

/**
 * Updater
 */
require_once 'updater/plugin-update-checker.php';
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
	'https://github.com/whitelabelltd/proxyflare',
	__FILE__,
	'proxyflare'
);
$myUpdateChecker->setBranch('release');