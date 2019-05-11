<?php
namespace PROXYFLARE;

class API {

	private static $api_url = PROXYFLARE_API;

	/**
	 * Clears the Cloudflare Cache
	 * Need valid API Credentials and have the site pre-approved
	 * @return bool
	 */
	public static function clear_cache() {

		// Build API Url
		$domain_name = self::get_domain();
		$endpoint = 'cache_clear/'.$domain_name.'/';

		// Send the Request
		return self::send( $endpoint );

	}

	/**
	 * Gets the current domain
	 * @return string
	 */
	private static function get_domain() {
		$parse = parse_url( get_site_url() );
		return $parse['host'];
	}

	/**
	 * Get API Key
	 * @return bool|mixed
	 */
	private static function get_api_key() {
		return proxyflare()->get('api_key','');
	}

	/**
	 * Get API Email
	 * @return bool|mixed
	 */
	private static function get_api_email() {
		return proxyflare()->get('api_email','');
	}

	/**
	 * Very basic post request
	 * @param string $endpoint
	 * @return bool
	 */
	private static function send($endpoint='') {
		if (empty($endpoint)) {
			return false;
		}

		// Set API Url for Custom Domain
		if (defined('PROXYFLARE_API_DOMAIN')) {
			$endpoint_domain = self::get_domain();
			$endpoint = str_replace($endpoint_domain,PROXYFLARE_API_DOMAIN,$endpoint);
		}

		$url = self::$api_url.$endpoint;

		// Set Authentication Headers and Useragent
		$args = array(
			'headers' => array(
				'Authorization' => 'Basic ' . base64_encode( self::get_api_email() . ':' . self::get_api_key() ),
				'User-Agent'    => 'Proxyflare/'.PROXYFLARE_VERSION,
				'Content-Type'  => 'application/json',
				'X-Domain'      => self::get_domain()
			)
		);

		// Make the request
		$response = wp_safe_remote_post( $url, $args );

		// Check Response
		if ( !is_wp_error( $response ) ) {
			$response = json_decode( wp_remote_retrieve_body($response), true );
			if (isset($response['success']) && true == $response['success']) {
				return true;
			}
		}

		return false;
	}

}