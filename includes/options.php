<?php
/**
 * Options Form
 *
 * simple form using the proxyflare options api
 * @todo tidy up code
 */

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

$action = 'proxyflare_options';
$mask = '**************';
$password_field = '';
$updated_message = '';

if ( !proxyflare()->user_can_edit() ) {
	wp_die('Unauthorized user');
}

if( 'POST' == $_SERVER['REQUEST_METHOD'] ) {

	check_admin_referer( $action );

	// Save Updated Options - Email
	if (isset($_POST['proxyflare_api_email'])) {
		proxyflare()->set('api_email',$_POST['proxyflare_api_email']);
	}

	// Save Updated Options - Email
	if (isset($_POST['proxyflare_api_key'])) {
	    // Avoid the mask being saved...
	    if ($mask !== $_POST['proxyflare_api_key']) {
		    proxyflare()->set('api_key',$_POST['proxyflare_api_key']);
        }
	}

	// Update Message
	$updated_message = '<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible">
        <p><strong>'.__('Proxyflare settings updated!','proxyflare').'</strong></p>
        <button type="button" class="notice-dismiss"><span class="screen-reader-text">'.__('Dismiss this notice.','proxyflare').'</span></button>
    </div>';

}

// API Key, avoid text unmasking. Should really be a standard...
if (!empty(proxyflare()->get('api_key',''))) {
	$password_field = $mask;
}

// Image Locations
$logo_path = plugin_dir_url( PROXYFLARE_FILE ) . 'assets/images/';

// The Form
?>
<form method="post">

    <style type="text/css" id="proxyflare-css">
        .wp-core-ui .button-proxyflare {
            background: #f1943e;
            border-color: #f39441 #F38020 #F38020;
            box-shadow: 0 1px 0 #F38020;
            color: #fff;
            text-decoration: none;
            text-shadow: unset;
        }
        .wp-core-ui .button-proxyflare.focus, .wp-core-ui .button-proxyflare.hover, .wp-core-ui .button-proxyflare:focus, .wp-core-ui .button-proxyflare:hover {
            background: #f3a052;
            border-color: #f39441;
            color: #fff;
        }
    </style>


    <p>&nbsp;</p>
    <img src="<?php echo($logo_path); ?>logo-wp-options.png" srcset="<?php echo($logo_path); ?>logo-wp-options@2x.png 2x" alt="Proxyflare">
    <?php echo($updated_message); ?>
    <h2><?php _e('Real IP Addresses', 'proxyflare'); ?></h2>
    <p><?php _e('This plugin will also restore the visitor IP address if running behind cloudflare automatically so there is no need to run the Cloudflare plugin or other similar plugin', 'proxyflare'); ?></p>
    <h2><?php _e('API Credentials', 'proxyflare'); ?></h2>
    <?php _e('Enter your Proxyflare API details below.', 'proxyflare'); ?>
    <br><small><?php _e('Contact Whitelabel Digital for your credentials', 'proxyflare'); ?></small>
    <table class="form-table">
        <tbody>
        <tr>
            <th scope="row"><?php _e('Proxyflare API Email', 'proxyflare'); ?></th>
            <td><input type="text" name="proxyflare_api_email" value="<?php echo( esc_html( proxyflare()->get('api_email','') ) ); ?>">
            </td>
        </tr>
        <tr>
            <th scope="row"><?php _e('Proxyflare API Key', 'proxyflare'); ?></th>
            <td><input type="password" name="proxyflare_api_key" value="<?php echo( esc_html( $password_field ) ); ?>">
            </td>
        </tr>
        </tbody>
    </table>
	<?php wp_nonce_field( $action ); ?>
    <p class="submit"><input type="submit" name="submit" id="submit" class="button button-proxyflare" value="<?php _e('Save Changes', 'proxyflare'); ?>">
    </p>
</form>

<p>&nbsp;</p>
<h2><?php _e('Cache Clearing', 'proxyflare'); ?></h2>
<?php

if (proxyflare()->activated()) {

    // Testing Clearing Cache Button
	$action_test = 'proxyflare_cache_clear_test';
	$nonce_test  = wp_create_nonce( $action_test );

	echo('<p>'.__('You can clear the cache using the button below, it automatically clears when using WP-Rocket.<br>If not using WP-Rocket you can clear the cache on any page using the admin-bar', 'proxyflare').'</p>');
	?>
    <div id="proxyflare_cache_clear_result" style="display: none" class="notice notice-info"><p id="proxyflare_cache_clear_result_text"></p></div>
    <br>
    <a href="#" class="button" id="proxyflare_button_test"><?php _e( 'Clear Cache', 'proxyflare' ); ?></a>
    <script type="text/javascript">
        var obj_proxyflare_test = '';
        jQuery("#proxyflare_button_test").on("click", function () {
            if ( !jQuery( this ).attr('disabled') ) {
                var old_text = jQuery(this).text();
                jQuery(this).text('Clearing Cache...');
                jQuery(this).attr('disabled', true );
                jQuery('#proxyflare_cache_clear_result').hide();
                var data = {
                    'action': '<?php echo( $action_test ); ?>',
                    'security': '<?php echo( $nonce_test ); ?>'
                 };
                obj_proxyflare_test = this;
                jQuery.post(ajaxurl, data, function (response) {
                    jQuery(obj_proxyflare_test).text(old_text);
                    jQuery(obj_proxyflare_test).attr('disabled', false );
                    jQuery('#proxyflare_cache_clear_result').fadeIn();
                    jQuery('#proxyflare_cache_clear_result_text').text(response);
                });
            }
        });
    </script>
	<?php
} else {
	_e('Please enter credentials above to activate the functionality of this plugin', 'proxyflare');
}