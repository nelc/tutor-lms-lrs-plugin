<?php
/**
 * xAPI sender class for tutorLmsLrsPlugin.
 * @package    tutorLmsLrsPlugin
 * @copyright  2025, Mohammed Hassan <betalamoud@gmail.com>
 * @license    MIT
 */

namespace tutorLmsLrsPlugin\includes\Interactions;

defined('ABSPATH') || exit;

use tutorLmsLrsPlugin\includes\Interactions\Registered;
use tutorLmsLrsPlugin\includes\Interactions\Initialized;
use tutorLmsLrsPlugin\includes\Interactions\Watched;
use tutorLmsLrsPlugin\includes\Interactions\Completed;
use tutorLmsLrsPlugin\includes\Interactions\CompletedUnit;
use tutorLmsLrsPlugin\includes\Interactions\Progressed;
use tutorLmsLrsPlugin\includes\Interactions\Attempted;
use tutorLmsLrsPlugin\includes\Interactions\CompletedCourse;
use tutorLmsLrsPlugin\includes\Interactions\Earned;
use tutorLmsLrsPlugin\includes\Interactions\Rated;

class XapiIntegration {
    private $endpoint;
    private $username;
    private $password;
    private $platformname;
    private $platformname_ar;
    private $platformname_en;

    public function __construct() {
        $this->endpoint = get_option('lmtni_xapi_endpoint');
        $this->username = get_option('lmtni_xapi_username');
        $this->password = get_option('lmtni_xapi_secret');
        $this->platformname = get_option('lmtni_xapi_platform');
        $this->platformname_ar = get_option('lmtni_xapi_platform_ar_name');
        $this->platformname_en = get_option('lmtni_xapi_platform_en_name');
    }

    public function sendXAPIRequest($data = []) {
        if (!$this->checkInternetConnection()) {
            return [
                'http_code' => 0,
                'response' => null,
                'data_sent' => null,
                'error' => 'No internet connection',
            ];
        }

        $url = $this->endpoint;
        $headers = array (
			'Content-type'=> 'Application/json',
            'X-Experience-API-Version'=> '1.0.3',
			'Authorization' => 'Basic ' . base64_encode( $this->username . ':' . $this->password ),
		);

        $args = array(
            'method'  => 'POST',
            'timeout' => 20,
            'headers' => $headers,
            'body'    => json_encode($data),
        );

        $response = wp_remote_post( $url, $args);

        if (is_wp_error($response)) {
            return [
                'http_code' => 0,
                'response' => null,
                'data_sent' => json_encode($data),
                'error' => $response->get_error_message(),
            ];
        }

        return [
            'http_code' => wp_remote_retrieve_response_code($response),
            'response' => wp_remote_retrieve_body($response),
            'data_sent' => json_encode($data),
        ];
    }

    private function checkInternetConnection($url = "www.google.com", $port = 80, $timeout = 5) {
        $connected = @fsockopen($url, $port, $errno, $errstr, $timeout);
        if ($connected) {
            fclose($connected);
            return true;
        }
        return false;
    }

    public function Registered($data) {
        return $this->sendXAPIRequest((new Registered())->Send($data));
    }

    public function Initialized($data) {
        return $this->sendXAPIRequest((new Initialized())->Send($data));
    }

    public function Watched($data) {
        return $this->sendXAPIRequest((new Watched())->Send($data));
    }

    public function CompletedUnit($data) {
        return $this->sendXAPIRequest((new CompletedUnit())->Send($data));
    }

    public function Completed($data) {
        return $this->sendXAPIRequest((new Completed())->Send($data));
    }

    public function Progressed($data) {
        return $this->sendXAPIRequest((new Progressed())->Send($data));
    }

    public function Attempted($data = []) {
        return $this->sendXAPIRequest((new Attempted())->Send($data));
    }

    public function CompletedCourse($data = []) {
        return $this->sendXAPIRequest((new CompletedCourse())->Send($data));
    }

    public function Earned($data = []) {
        return $this->sendXAPIRequest((new Earned())->Send($data));
    }

    public function Rated($data = []) {
        return $this->sendXAPIRequest((new Rated())->Send($data));
    }
}
