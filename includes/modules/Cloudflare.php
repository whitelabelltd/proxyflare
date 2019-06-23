<?php


namespace PROXYFLARE;


class Cloudflare {

	/**
	 * Only load when the following plugins are not activate
	 *
	 * wp-rocket does only do it when their Cloudflare module is activated
	 * @var array
	 */
	private $plugins = array(
		'cloudflare/cloudflare.php'
	);

	/**
	 * Cloudflare constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this , 'maybe_set_real_ip' ), 1 );
	}

	/**
	 * Maybe set real IP
	 * checks for certain plugins and conditions
	 */
	public function maybe_set_real_ip() {
		$run =true;
		if (!function_exists('is_plugin_active')) {
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		foreach ($this->plugins as $plugin) {
			if (\is_plugin_active($plugin) ) {
				$run = false;
			}
		}

		if ($this->wp_rocket_cloudflare_enabled()) {
			$run = false;
		}

		if ($run) {
			$this->set_real_ip();
		}
	}

	/**
	 * Check if WP Rocket has the Cloudflare Module running
	 * @return bool|void
	 */
	private function wp_rocket_cloudflare_enabled() {
		if (function_exists('rocket_set_real_ip_cloudflare')) {
			return true;
		}
		return;
	}

	/**
	 * Set Real IP from CloudFlare
	 *
	 * @since 2.8.16 Uses CloudFlare API v4 to get CloudFlare IPs
	 * @since 2.5.4
	 * @source cloudflare.php - https://wordpress.org/plugins/cloudflare/
	 */
	private function set_real_ip() {

		$is_cf = ( isset( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) ? true : false;
		if ( ! $is_cf ) {
			return;
		}

		// only run this logic if the REMOTE_ADDR is populated, to avoid causing notices in CLI mode.
		if ( isset( $_SERVER['REMOTE_ADDR'] ) ) {

			// Grab the Current Cloudflare Address Range
			$cf_ips_values = $this->get_ips();
			if ( false == $cf_ips_values || empty($cf_ips_values) ) {
				return;
			}

			// Check if the we getting a IPv4 or IPv6 Address
			if ( strpos( $_SERVER['REMOTE_ADDR'], ':' ) === false ) {
				$cf_ip_ranges = $cf_ips_values['ipv4'] ?? '';

				// IPv4: Update the REMOTE_ADDR value if the current REMOTE_ADDR value is in the specified range.
				foreach ( $cf_ip_ranges as $range ) {
					if ( IP::ipv4_in_range( $_SERVER['REMOTE_ADDR'], $range ) ) {
						if ( $_SERVER['HTTP_CF_CONNECTING_IP'] ) {
							$_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_CF_CONNECTING_IP'];
						}
						break;
					}
				}
			}
			else {

				// IPv6: Update the REMOTE_ADDR value if the current REMOTE_ADDR value is in the specified range.
				$cf_ip_ranges = $cf_ips_values['ipv6'];
				$ipv6 = IP::get_ipv6_full( $_SERVER['REMOTE_ADDR'] );
				foreach ( $cf_ip_ranges as $range ) {
					if ( IP::ipv6_in_range( $ipv6, $range ) ) {
						if ( $_SERVER['HTTP_CF_CONNECTING_IP'] ) {
							$_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_CF_CONNECTING_IP'];
						}
						break;
					}
				}
			}
		}
	}


	/**
	 * Get Cloudflare IPs.
	 * saves them in the cache for 48 hours if it finds them successfully
	 * @return array|bool|mixed
	 */
	private function get_ips() {

		$cache_name = 'proxyflare_cloudflare_ips';

		// Check Cache before making the request
		$ips = get_transient( $cache_name );
		if ( false === $ips ) {

			// Set the API URL
			$url = 'https://api.cloudflare.com/client/v4/ips';

			// Set API Call Details
			$args = array(
				'headers' => array(
					'User-Agent' => 'Proxyflare/' . PROXYFLARE_VERSION,
				)
			);
			// Make the request
			$response = wp_safe_remote_get( $url, $args );

			// Check Response
			if ( ! is_wp_error( $response ) ) {
				proxyflare()->log( ' - Cloudflare IP Get Result (' . wp_remote_retrieve_response_code( $response ) . '): ' . wp_remote_retrieve_body( $response ) );
				$response = json_decode( wp_remote_retrieve_body( $response ), true );
				if ( isset( $response['success'] ) && true == $response['success'] ) {
					$ips = array(
						'ipv4' => $response['result']['ipv4_cidrs'] ?? '',
						'ipv6' => $response['result']['ipv6_cidrs'] ?? ''
					);
					// Save Cache for 48 hours
					set_transient( $cache_name, $ips, 2 * DAY_IN_SECONDS );
				}
			} else {
				proxyflare()->log( ' - API Error (' . wp_remote_retrieve_response_code( $response ) . '): ' . wp_remote_retrieve_body( $response ) );

				return false;
			}

			return false;

		} else {
			return $ips;
		}
	}
}

new Cloudflare();