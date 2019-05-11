<?php
/**
 * Options Form
 *
 * simple form using the proxyflare options api
 * @todo tidy up code
 */

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
	if (isset($_POST['proxyflare_api_email']) && !empty($_POST['proxyflare_api_email'])) {
		proxyflare()->set('api_email',$_POST['proxyflare_api_email']);
	}

	// Save Updated Options - Email
	if (isset($_POST['proxyflare_api_key']) && !empty($_POST['proxyflare_api_key'])) {
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

// The Form
?>
<form method="post">

    <h1><?php _e('Proxyflare', 'proxyflare'); ?></h1>
    <?php echo($updated_message); ?>
    <h2><?php _e('API Credentials', 'proxyflare'); ?></h2>
    <?php _e('Enter your Proxyflare API details below. Contact Whitelabel Digital for your credentials', 'proxyflare'); ?>
    <table class="form-table">
        <tbody>
        <tr>
            <th scope="row"><?php _e('API Email', 'proxyflare'); ?></th>
            <td><input type="text" name="proxyflare_api_email" value="<?php echo( proxyflare()->get('api_email','') ); ?>">
            </td>
        </tr>
        <tr>
            <th scope="row"><?php _e('API Key', 'proxyflare'); ?></th>
            <td><input type="password" name="proxyflare_api_key" value="<?php echo( $password_field ); ?>">
            </td>
        </tr>
        </tbody>
    </table>
	<?php wp_nonce_field( $action ); ?>
    <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Save Changes', 'proxyflare'); ?>">
    </p>
</form>