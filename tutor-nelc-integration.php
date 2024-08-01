<?php
/**
 * Plugin Name: NELC Integration
 * Version: 1.0.0
 * Plugin URI: https://wa.me/00201062332549
 * Description: Tutor NELC Integration wordprees plugin, It was launched specifically to link with the National Center for E-Learning in Saudi Arabia, so that the tool sends all the activities of the trainees, starting from registering for the course until obtaining the certificate.
 * Author: Mahmoud Hassan
 * Author URI: https://wa.me/00201062332549
 * Requires at least: 4.0
 * Tested up to: 4.0
 *
 * Text Domain: tutor-nelc-integration
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author Mahmoud Hassan
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load plugin class files.
require_once 'includes/class-tutor-nelc-integration.php';
require_once 'includes/class-tutor-nelc-integration-settings.php';

// Load plugin libraries.
require_once 'includes/lib/class-tutor-nelc-integration-admin-api.php';
require_once 'includes/lib/class-tutor-nelc-integration-browser.php';
require_once 'includes/lib/class-tutor-nelc-integration-statements.php';
require_once 'includes/lib/class-tutor-nelc-integration-interactions.php';
require_once 'includes/lib/tutor-nelc-integration-hooks.php';

/**
 * Returns the main instance of tutor_nelc_integration to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object tutor_nelc_integration
 */
function tutor_nelc_integration() {
	$instance = tutor_nelc_integration::instance( __FILE__, '1.0.0' );

	if ( is_null( $instance->settings ) ) {
		$instance->settings = tutor_nelc_integration_Settings::instance( $instance );
	}

	return $instance;
}

tutor_nelc_integration();

add_action('wp_ajax_tutor_notify_action', 'tutor_notify_action_callback');
add_action('wp_ajax_nopriv_tutor_notify_action', 'tutor_notify_action_callback');
function tutor_notify_action_callback() {

	$uuid = get_user_meta(get_current_user_id(), 'tutor_nelc_xapi_notify_action', true);
    //$message = $meta_data;
	
	if ( is_valid_uuid($uuid) ) {
		delete_user_meta(get_current_user_id(), 'tutor_nelc_xapi_notify_action');
		wp_send_json_success( __('The report has been sent to NELC', 'tutor-nelc-xapi') );
	} else {
		delete_user_meta(get_current_user_id(), 'tutor_nelc_xapi_notify_action');
		wp_send_json_error( __('The report was not sent to NELC', 'tutor-nelc-xapi') );
	}

	//delete_user_meta(get_current_user_id(), 'tutor_nelc_xapi_notify_action');
}


function is_valid_uuid($uuid) {
    $uuid_pattern = '/^\["\w{8}-\w{4}-\w{4}-\w{4}-\w{12}"\]$/';

    return (bool) preg_match($uuid_pattern, $uuid);
}

function add_btn_to_footer()
{
	?><button id="tutor_notify_action_check" style="display: none;"></button><?php
}
add_action('wp_footer', 'add_btn_to_footer');

add_action('wp_head', 'nelc_xapi_ajaxurl');
function nelc_xapi_ajaxurl() {

   echo '<script type="text/javascript">
           var ajaxurl = "' . admin_url('admin-ajax.php') . '";
         </script>';
}

add_action('wp_footer', 'tutor_notify_action_check');
function tutor_notify_action_check() {
	$meta_data = get_user_meta(get_current_user_id(), 'tutor_nelc_xapi_notify_action', true);
	if ( $meta_data) {

		?>
		<script>
			console.log('NELC: Notice found')
			send_notf = true;
			setInterval(() => {
				if( document.querySelector('#tutor_notify_action_check') && send_notf ){
					document.querySelector('#tutor_notify_action_check').click();
					send_notf = false;
				}
			}, 1000);
		</script>
		<?php
	}else{
		?><script>console.log('NELC: No notification found')</script><?php
	}

}
