<?php
namespace PROXYFLARE;

use LayerShifter\TLDExtract\Exceptions\Exception;
use LayerShifter\TLDExtract\Exceptions\RuntimeException;

class API {

	/**
	 * URL for the API Server
	 * @var string
	 */
	private static $api_url = 'https://proxyflare.wld.nz/api/v1/';

	/**
	 * Clears the Cloudflare Cache
	 * Need valid API Credentials and have the site pre-approved
	 * @return bool
	 */
	public static function clear_cache() {

		// Get the domain name
		$domain_name = self::get_domain();
		if (!$domain_name) {
			return false;
		}

		// Build Endpoint Url
		$endpoint = sprintf('cache_clear/%s/', $domain_name );

		// Send the Request
		return self::send( $endpoint );
	}

	/**
	 * Gets the current domain
	 * @param bool $bypass_root_check
	 *
	 * @return bool|string
	 */
	private static function get_domain($bypass_root_check=false) {
		return self::get_site_url($bypass_root_check);
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
	 *
	 * @return bool
	 */
	private static function send($endpoint='') {
		if (empty($endpoint)) {
			return false;
		}
		proxyflare()->log('Starting API Call');

		// Set API Url for Custom Domain
		if (defined('PROXYFLARE_API_DOMAIN')) {
			$endpoint_domain = self::get_domain(true);
			$endpoint = str_replace($endpoint_domain,self::get_root_domain(PROXYFLARE_API_DOMAIN),$endpoint);
		}

		$url = self::get_api_url().$endpoint;

		proxyflare()->log(' + URL: '.$url);

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
			proxyflare()->log(' - API Response ('.wp_remote_retrieve_response_code($response).'): '.wp_remote_retrieve_body($response) );
			$response = json_decode( wp_remote_retrieve_body($response), true );
			if (isset($response['success']) && true == $response['success']) {
				proxyflare()->log(' + Success');
				return true;
			}
		} else {
			proxyflare()->log(' - API Error ('.wp_remote_retrieve_response_code($response).'): '.wp_remote_retrieve_body($response) );
		}

		return false;
	}

	/**
	 * Gets the API URL
	 */
	public static function get_api_url() {
		$url = self::$api_url;
		if (defined('PROXYFLARE_API')) {
			$url = PROXYFLARE_API;
		}
		return $url;
	}

	/**
	 * Gets the local site url
	 * @param bool $bypass_root_check
	 *
	 * @return bool|string|null
	 */
	private static function get_site_url($bypass_root_check=false) {
		$parse = parse_url( get_site_url() );

		if (defined('PROXYFLARE_API_DOMAIN')) {
			$parse['host'] = PROXYFLARE_API_DOMAIN;
		}

		if (true === $bypass_root_check) {
			if (self::is_staging_domain($parse['host'])) {
				return false;
			}
			return $parse['host'];
		}

		// Root Domain
		try {
			$domain = self::get_root_domain( $parse['host'] );
			if (false === $domain) {
				return false;
			}
		} catch (Exception $e) {
			proxyflare()->log('ERROR: '.$e->getMessage());
			return false;
		}

		// Remove www
		$domain = str_replace('www.','',$domain);

		// Check for Staging Domains
		if (self::is_staging_domain($domain)) {
			proxyflare()->log(sprintf('Cache Not Cleared: Is Staging Domain (%s)', $domain ) );
			return false;
		}

		return $domain;
	}

	/**
	 * Checks if the domain is a staging domain
	 * @param string $domain
	 * @return bool
	 */
	public static function is_staging_domain($domain='') {
		if ($domain) {
			$list = array(
				'wpengine.com',
				'mywpengine.com',
				'flywheelsites.com',
				'flywheelstaging.com',
				'cloudwaysapps.com',
			);

			foreach ( $list as $list_item ) {
				if ( strpos( $domain, $list_item ) !== false ) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Gets the domain from the API URL
	 * @return bool|string|null
	 * @throws RuntimeException
	 */
	public static function get_api_domain() {
		$url = self::get_api_url();
		$parsed_url = @parse_url( $url );
		if ( ! $parsed_url || empty( $parsed_url['host'] ) ) {
			return false;
		}

		// Root Domain
		$domain = self::get_root_domain( $parsed_url['host'] );
		if (false === $domain) {
			return false;
		}

		return $domain;
	}

	/**
	 * Gets the root domain
	 * @param string $domain
	 *
	 * @return bool|string|null
	 * @throws RuntimeException
	 */
	public static function get_root_domain( $domain = '' ) {
		$extract = new \LayerShifter\TLDExtract\Extract();
		$result = $extract->parse( $domain );

		$root = $result->getRegistrableDomain();

		if ($root) {
			return $root;
		}

		return false;
	}

}