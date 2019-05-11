<?php

namespace PROXYFLARE;

class PROXYFLARE {

	/**
	 * Minimum Capabilities needed to effect actions
	 * @var string
	 */
	private $cap_actions = 'edit_pages';

	/**
	 * Minimum Capabilities needed to edit options related to this plugin
	 * @var string
	 */
	private $cap_options = 'activate_plugins';

	/**
	 * DB Key Name
	 * @var string
	 */
	private $options_name = 'proxyflare_options';

	/**
	 * Holds Options
	 * @var array
	 */
	private $options = array();

	/**
	 * PROXYFLARE constructor.
	 */
	public function __construct() {
		/* Do Nothing Here */
	}

	/**
	 * Init Proxyflare
	 */
	public function init() {

	    // API URL
		if (!defined('PROXYFLARE_API')) {
			define('PROXYFLARE_API','https://proxyflare.wld.nz/api/v1/');
		}

		// Load Options
		$this->options_load();

		// Get Helpers
		$this->load_helpers(
			array(
				'API'
			)
		);

		// Add WP-Rocket Support
		if ( $this->activated() ) {
			add_action('after_rocket_clean_domain' , array( $this , 'clear_cache' ), 10, 3 );
		}

		// Add Admin Menu
		add_action('admin_menu', array( $this , 'admin_menu' ) );


		// Add Admin Menu Bar Item
		add_action('wp_loaded' , array( $this , 'admin_bar_check' ) );
		add_action( 'wp_ajax_proxyflare_cache_clear', array( $this , 'admin_ajax_cache_clear' ) );



	}

	/**
	 * Clears the cache after WP-Rocket has cleared theirs
	 * @param $root
	 * @param $lang
	 * @param $url
	 */
	public function wp_rocket_clear_cache( $root, $lang, $url ) {
		$this->clear_cache();
	}

	/**
	 * Clears the Cache
	 */
	public function clear_cache() {
		// Clear the Cloudflare Cache using the API
		API::clear_cache();
	}

	/**
	 * Checks if WP Rocket is Active
	 * @return bool
	 */
	private function wp_rocket_is_active() {
		return false;
	}



	/**
	 * Adds the Admin Bar 'Clear CF Cache' option if WP Rocket is not active
	 * @cap actions
	 */
	public function admin_bar_check() {
		if (true !== $this->wp_rocket_is_active() && $this->user_can_action() && $this->activated() ) {
			add_action('wp_before_admin_bar_render', array( $this , 'admin_bar' ) );
			add_action( 'admin_footer', array( $this , 'admin_footer' ) );
		}
	}

	/**
	 * Adds a Clear Cache Button to the Admin Bar
	 */
	public function admin_bar() {

		global $wp_admin_bar;

		$args = array(
			'id'     => 'proxyflare-cache-clear',
			'title'  => __( 'Clear CF Cache', 'proxyflare' ),
			'href'   => '#',
			'group'  => false,
		);
		$wp_admin_bar->add_menu( $args );
	}

	/**
	 * Adds the needed JS for the Clearing Cache function to work
	 */
	public function admin_footer() {
		$nonce = wp_create_nonce("proxyflare_cache_clear");
		?>
		<script type="text/javascript" >
            var obj_proxyflare = '';
            jQuery("#wp-admin-bar-proxyflare-cache-clear").on( "click", function() {
                if ( !jQuery(this).attr('data-running') ) {
                    jQuery(this).attr('data-running', true);
                    var old_text = jQuery(this).find('a').text();
                    jQuery(this).find('a').text('Clearing Cache...');
                    var data = {
                        'action': 'proxyflare_cache_clear',
                        'security': '<?php echo( $nonce ); ?>'
                    };
                    obj_proxyflare = this;
                    jQuery.post(ajaxurl, data, function (response) {
                        jQuery(obj_proxyflare).find('a').text(old_text);
                        jQuery(obj_proxyflare).removeAttr('data-running');
                        alert(response);
                    });
                }
            });
		</script>
		<?php
	}

	/**
	 * Simple Cache Clearing function
	 */
	public function admin_ajax_cache_clear() {
		error_log('admin_ajax_cache_clear');
		if ( check_ajax_referer( 'proxyflare_cache_clear', 'security', false ) == false ) {
			echo('Security Error');
			wp_die();
		}
		// Make sure the current user is allowed to do this
		if (!$this->cap_actions) {
			echo('Sorry you do not have permission to do this');
			wp_die();
        }
		// Clear the cache!
		if ( API::clear_cache() ) {
			$response = 'Cache Purged !';
		} else {
			$response = 'ERROR Clearing Cache';
		}
		echo $response;
		wp_die();
	}

	/**
	 * Add the Admin Options Page Menu
	 */
	public function admin_menu() {
		add_options_page( 'Proxyflare', 'Proxyflare', $this->cap_options, 'proxyflare', array( $this , 'admin_page' ) );
	}

	/**
	 * The Admin Options Page
	 */
	public function admin_page() {
		if (!$this->user_can_edit() ) {
			wp_die(__(
				'Sorry you do not have permission to edit this page',
				'proxyflare'
			));
		}

		// Include the Options Page
		include_once (plugin_dir_path( PROXYFLARE_FILE ) . 'includes/options.php');
	}

	/**
	 * Can the current user do any actions
	 * @return bool
	 */
	public function user_can_action() {
		return current_user_can( $this->cap_actions );
	}

	/**
	 * Can the current user edit / view the options page
	 * @return bool
	 */
	public function user_can_edit() {
		return current_user_can( $this->cap_options );
	}

	/**
	 * Retrieves the API Key
	 * @return bool
	 */
	public function get_api_key() {
		if ($this->cap_actions) {
			$options = get_option($this->options);
			if (isset($options['api_key'])) {
				return $options['api_key'];
			}
		}
		return false;
	}

	/**
	 * Load Options
	 * JSON in DB to unserializing risks
	 */
	private function options_load() {
		$options = get_option( $this->options_name );
		if ($options) {
			$options = json_decode($options,1);
			$this->options = $options;
		}
	}

	/**
	 * Save Options
	 * JSON in DB to unserializing risks
	 */
	private function options_save() {
		if (!empty($this->options)) {
			$options = $this->options;
			$options = json_encode( $options );
			update_option( $this->options_name , $options );
		}
	}

	/**
	 * Is the plugin activated with the API Credentials?
	 * @return bool
	 */
	private function activated() {
		$api = $this->get('api_key','');
		$email = $this->get('api_email','');
		if (!empty($api) && !empty($email)) {
			return true;
		}
		return false;
	}

	/**
	 * Loads the relevant option
	 * @param string $name
	 * @param bool $default false
	 *
	 * @return bool|mixed
	 */
	public function get($name='',$default=false) {
		if (!empty($name) && !empty($this->options) ) {
			if (array_key_exists($name,$this->options)) {
				return $this->options[$name];
			}
		}
		return $default;
	}

	/**
	 * Update Option
	 * @param string $name
	 * @param string $value optional
	 * @param bool $permanent defaults to true otherwise the option is discarded after request
	 */
	public function set($name='',$value='',$permanent=true) {
		if (!empty($name)) {
			$this->options[$name]=$value;
			if ($permanent) {
				$this->options_save();
			}
		}
	}

	/**
	 * Loads any needed Helpers
	 * @param array $helpers
	 */
	private function load_helpers($helpers = array() ) {

		$path = plugin_dir_path( PROXYFLARE_FILE ) . 'helpers/';
		foreach ($helpers as $helper) {
			if (file_exists($path.$helper.'.php')) {
				require_once($path.$helper.'.php');
			}
		}
	}
}