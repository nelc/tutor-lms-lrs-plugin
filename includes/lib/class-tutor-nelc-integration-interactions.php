<?php
/**
 * Interactions functions file.
 *
 * @package WordPress tutor nelc integration/Includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Interactions functions class.
 */
class Tutor_NELC_Integration_Interactions {

	/**
	 * The name for the Interactions.
	 *
	 * @var     string
	 * @access  public
	 * @since   1.0.2
	 */
	public $endpoint;
	public $username;
	public $secret;
	public $platform;
	public $platformAr;
	public $platformEn;
	/**
	 * The array of interactions arguments
	 *
	 * @var     array
	 * @access  public
	 * @since   1.0.2
	 */
	public $headers;
	public $body;
	public $post_req;

	/**
	 * interactions constructor.
	 *
	 * @param string $interactions interactions variable nnam.
	 * @param array  $tax_args interactions additional args.
	 */
	public function __construct( $body = array() ) {

		if ( ! $body ) {
			return;
		}

		$this->endpoint = strval(get_option('lmtni_xapi_endpoint'));
		$this->username = strval(get_option('lmtni_xapi_username'));
		$this->secret = strval(get_option('lmtni_xapi_secret'));
		$this->platform = strval(get_option('lmtni_xapi_platform'));
		$this->platformAr = strval(get_option('lmtni_xapi_platform_ar_name'));
		$this->platformEn = strval(get_option('lmtni_xapi_platform_en_name'));
		$this->body = $body;

		$this->headers = array (
			'Content-type'=> 'Application/json',
			'Authorization' => 'Basic ' . base64_encode( $this->username . ':' . $this->secret ),
		);

		// // Register interactions.
		// add_action( 'init', array( $this, 'register_interactions' ) );
	}

}
