<?php
/**
 * Plugin Name: Proxyflare
 * Plugin URI: https://github.com/whitelabelltd/proxyflare
 * Description: Cloudflare functions without using a Global API Key. For use with Whitelabel Digital Sites
 * Version: 1.4.4
 * Author: Whitelabel Digital
 * Author URI: http://whitelabel.ltd
 * Licence: GPLv2 or later
 * Plugin Folder: proxyflare
 *
 * Copyright 2019 Whitelabel Digital ( whitelabel.ltd )
 */

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

define('PROXYFLARE_VERSION','1.4.4');
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
require_once __DIR__ . '/vendor/autoload.php';
try {
	$proxyflare_updater = Puc_v4_Factory::buildUpdateChecker(
		'https://github.com/whitelabelltd/proxyflare',
		__FILE__,
		'proxyflare'
	);
	$proxyflare_updater->setBranch('release');
} catch (Exception $e) {
	error_log('[Proxyflare] Updater Failed to Init ('.$e->getMessage().')');
	error_log('[Proxyflare]  - Code: '.$e->getCode());
	error_log('[Proxyflare]  - Trace: '.$e->getTraceAsString());
}


/**
 * Auto API Key Installer
 */
register_activation_hook( __FILE__, 'proxyflare_activate' );
function proxyflare_activate() {
	// Load API Keys If Found
	$file=plugin_dir_path( PROXYFLARE_FILE ) . 'api.json';
	if (file_exists($file)) {
		$keys = json_decode(file_get_contents($file),1);
		if (isset($keys['email']) && isset($keys['key'])) {
			proxyflare()->set('api_email',$keys['email']);
			proxyflare()->set('api_key',$keys['key']);
			// Remove the file
			@unlink($file);
		}
	}
}