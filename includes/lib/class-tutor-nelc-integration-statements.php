<?php
/**
 * Statements functions file.
 *
 * @package NELC Integration/Includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Statements functions class.
 */
class Tutor_NELC_Integration_Statements {

	/**
	 * The name for the Statements.
	 *
	 * @var     string
	 * @access  public
	 * @since   1.0.2
	 */
	public $statment;
	/**
	 * The array of statment arguments
	 *
	 * @var     array
	 * @access  public
	 * @since   1.0.2
	 */
	public $statment_args;

	/**
	 * statment constructor.
	 *
	 * @param string $statment statment variable nnam.
	 * @param array  $tax_args statment additional args.
	 */
	public function __construct( $statment = '', $tax_args = array() ) {

		if ( ! $statment || ! $tax_args ) {
			return;
		}

		$this->statment = $statment;
		//$this->statment_args = $tax_args;
        $browser = Browser::getBrowser();
        $br_os = $browser["platform"] ?? '';
        $br_name = $browser["name"] ?? '';
        $br_ver = $browser['version'] ?? '';

        $plt_lang = str_contains(get_locale(), 'ar') ? 'ar-SA' : 'en-US';
        $platform = get_option( 'lmtni_xapi_platform' );
        $platformAr = get_option( 'lmtni_xapi_platform_ar_name' );
        $platformEn = get_option( 'lmtni_xapi_platform_en_name' );
        
        switch ( $this->statment ) {
            case "registered":
    
                $this->statment_args =
                array(
                    'actor' => array(
                                'name' => $tax_args['name'],
                                'mbox'  => 'mailto:'.$tax_args['email'],
                                'objectType' => 'Agent',
                            ),
                    'verb' => array(
                                'id' => 'http://adlnet.gov/expapi/verbs/registered',
                                'display' => array('en-US' => 'registered') 
                            ),
                    'object' => array(
                                    'id'=> $tax_args['courseId'],
                                    'definition' => array(
                                        'name' => array("$plt_lang" => $tax_args['courseName']),
                                        'description' => array("$plt_lang" => $tax_args['courseDesc']),
                                        'type' => 'https://w3id.org/xapi/cmi5/activitytype/course'
                                    ),
                                    'objectType' => 'Activity',
                                ),
                    'context' => array(
                                    'instructor' => array(
                                        'name' => $tax_args['instructor'],
                                        'mbox' => 'mailto:'.$tax_args['inst_email'],
                                    ),
                                    'platform' => strval($platform),
                                    'language' => "$plt_lang",
                                    "extensions" => array(
                                        "https://nelc.gov.sa/extensions/platform" => array(
                                            "name" => array(
                                                "ar-SA" => strval($platformAr),
                                                "en-US" => strval($platformEn)
                                            )
                                        )
                                    )
                                ),
                    'timestamp' => date('Y-m-d\TH:i:s'.substr((string)microtime(), 1, 4).'\Z')
                );
    
                break;
            case "initialized":
                $this->statment_args =
                array(
                    'actor' => array(
                                'name' => $tax_args['name'],
                                'mbox'  => 'mailto:'.$tax_args['email'],
                                'objectType' => 'Agent',
                            ),
                    'verb' => array(
                                'id' => 'http://adlnet.gov/expapi/verbs/initialized',
                                'display' => array('en-US' => 'initialized') 
                            ),
                    'object' => array(
                                    'id'=> $tax_args['courseId'],
                                    'definition' => array(
                                        'name' => array( strval($plt_lang) => $tax_args['courseName'] ),
                                        'description' => array( strval($plt_lang) => $tax_args['courseDesc'] ),
                                        'type' => 'https://w3id.org/xapi/cmi5/activitytype/course'
                                    ),
                                    'objectType' => 'Activity',
                                ),
                    'context' => array(
                                    'instructor' => array(
                                        'name' => $tax_args['instructor'],
                                        'mbox' => 'mailto:'.$tax_args['inst_email'],
                                    ),
                                    'platform' => strval($platform),
                                    'language' => strval($plt_lang),
                                    "extensions" => array(
                                        "https://nelc.gov.sa/extensions/platform" => array(
                                            "name" => array(
                                                "ar-SA" => strval($platformAr),
                                                "en-US" => strval($platformEn)
                                            )
                                        )
                                    )
                                ),
                    'timestamp' => date('Y-m-d\TH:i:s'.substr((string)microtime(), 1, 4).'\Z')
                );

                break;
            case "watched":
                $this->statment_args =
                array(
                    'actor' => array(
                                'name' => $tax_args['name'],
                                'mbox'  => 'mailto:'.$tax_args['email'],
                                'objectType' => 'Agent',
                            ),
                    'verb' => array(
                                'id' => 'https://w3id.org/xapi/acrossx/verbs/watched',
                                'display' => array("en-US" => "watched") 
                            ),
                    'object' => array(
                                    'id'=> $tax_args['lessonUrl'],
                                    'definition' => array(
                                        'name' => array(strval($plt_lang) => $tax_args['lessonName']),
                                        'description' => array(strval($plt_lang) => $tax_args['lessonDesc']),
                                        'type' => 'https://w3id.org/xapi/video/activity-type/video'
                                    ),
                                    'objectType' => 'Activity',
                                ),
                    'context' => array(
                                    'instructor' => array(
                                        'name' => $tax_args['instructor'],
                                        'mbox' => 'mailto:'.$tax_args['inst_email'],
                                    ),
                                    'platform' => strval($platform),
                                    'language' => strval($plt_lang),
                                    'extensions' => array (
                                        "http://id.tincanapi.com/extension/browser-info" => array(
                                            "code_name" => $br_os,
                                            "name" => $br_name,  
                                            "version" => $br_ver
                                        )
                                    ),
                                    'contextActivities' => array(
                                        'parent' => array(
                                            array (
                                                'id' => $tax_args['courseId'],
                                                'definition' => array(  
                                                    'name' => array(strval($plt_lang) => $tax_args['courseName']),
                                                    'description' => array( strval($plt_lang) => $tax_args['courseDesc'] ),
                                                    'type' => 'https://w3id.org/xapi/cmi5/activitytype/course'
                                                ),
                                                'objectType' => "Activity"
                                            )
                                        )
                                    )
                                ),
                                'result' => array(
                                    'completion' => $tax_args['completion'],
                                    'duration' => $tax_args['duration'],
                                ),
                    'timestamp' => date('Y-m-d\TH:i:s'.substr((string)microtime(), 1, 4).'\Z')
                );
                break;
            case "completed":
                $this->statment_args = array(
                    'actor' => array(
                                'name' => $tax_args['name'],
                                'mbox'  => 'mailto:'.$tax_args['email'],
                                'objectType' => 'Agent',
                            ),
                    'verb' => array(
                                'id' => 'http://adlnet.gov/expapi/verbs/completed',
                                'display' => array("en-US" => "completed") 
                            ),
                    'object' => array(
                                    'id'=> $tax_args['lessonUrl'],
                                    'definition' => array(
                                        'name' => array(strval($plt_lang) => $tax_args['lessonName']),
                                        'description' => array(strval($plt_lang) => $tax_args['lessonDesc']),
                                        'type' => 'http://adlnet.gov/expapi/activities/lesson'
                                    ),
                                    'objectType' => 'Activity',
                    ),
                    'context' => array(
                                    'instructor' => array(
                                        'name' => $tax_args['instructor'],
                                        'mbox' => 'mailto:'.$tax_args['inst_email'],
                                    ),
                                    'platform' => strval($platform),
                                    'language' => strval($plt_lang),
                                    'extensions' => array (
                                        "http://id.tincanapi.com/extension/browser-info" => array(
                                            "code_name" => $br_os,
                                            "name" => $br_name,  
                                            "version" => $br_ver
                                        ),
                                        "https://nelc.gov.sa/extensions/platform" => array(
                                            "name" => array(
                                                "ar-SA" => strval($platformAr),
                                                "en-US" => strval($platformEn)                                            )
                                        )
                                    ),
                                    'contextActivities' => array(
                                        'parent' => array(
                                            array (
                                                'id' => $tax_args['courseId'],
                                                'definition' => array(  
                                                    'name' => array(strval($plt_lang) => $tax_args['courseName']),
                                                    'description' => array( strval($plt_lang) => $tax_args['courseDesc'] ),
                                                    'type' => 'https://w3id.org/xapi/cmi5/activitytype/course'
                                                ),
                                                'objectType' => "Activity"
                                            )
                                        )
                                    )
                                ),
            
                    'timestamp' => date('Y-m-d\TH:i:s'.substr((string)microtime(), 1, 4).'\Z')
                );
                break;
            case "completedUnit":
                $this->statment_args = array(
                    'actor' => array(
                                'name' => $tax_args['name'],
                                'mbox'  => 'mailto:'.$tax_args['email'],
                                'objectType' => 'Agent',
                            ),
                    'verb' => array(
                                'id' => 'http://adlnet.gov/expapi/verbs/completed',
                                'display' => array("en-US" => "completed") 
                            ),
                    'object' => array(
                        'id'=> $tax_args['unitUrl'],
                        'definition' => array(
                            'name' => array(strval($plt_lang) => $tax_args['unitName']),
                            'description' => array(strval($plt_lang) => $tax_args['unitDesc']),
                                    'type' => 'http://adlnet.gov/expapi/activities/module'
                                ),
                                'objectType' => 'Activity',
                            ),
                    'context' => array(
                                    'instructor' => array(
                                        'name' => $tax_args['instructor'],
                                        'mbox' => 'mailto:'.$tax_args['inst_email'],
                                    ),
                                    'platform' => strval($platform),
                                    'language' => strval($plt_lang),
                                    'extensions' => array (
                                        "http://id.tincanapi.com/extension/browser-info" => array(
                                            "code_name" => $br_os,
                                            "name" => $br_name,  
                                            "version" => $br_ver
                                        ),
                                        "https://nelc.gov.sa/extensions/platform" => array(
                                            "name" => array(
                                                "ar-SA" => strval($platformAr),
                                                "en-US" => strval($platformEn)   
                                            )
                                        )
                                    ),
                                    'contextActivities' => array(
                                        'parent' => array(
                                            array (
                                                'id' => $tax_args['courseId'],
                                                'definition' => array(  
                                                    'name' => array(strval($plt_lang) => $tax_args['courseName']),
                                                    'description' => array( strval($plt_lang) => $tax_args['courseDesc'] ),
                                                    'type' => 'https://w3id.org/xapi/cmi5/activitytype/course'
                                                ),
                                                'objectType' => "Activity"
                                            )
                                        )
                                    )
                                ),
            
                    'timestamp' => date('Y-m-d\TH:i:s'.substr((string)microtime(), 1, 4).'\Z')
                );
                break;
            case "progressed":
                $this->statment_args = array(
                    'actor' => array(
                        'name' => $tax_args['name'],
                        'mbox'  => 'mailto:'.$tax_args['email'],
                        'objectType' => 'Agent',
                    ),
                    'verb' => array(
                                'id' => 'http://adlnet.gov/expapi/verbs/progressed',
                                'display' => array("en-US" => "progressed") 
                            ),
                    'object' => array(
                                    'id'=> $tax_args['courseId'],
                                    'definition' => array(
                                        'name' => array(strval($plt_lang) => $tax_args['courseName']),
                                        'description' => array( strval($plt_lang) => $tax_args['courseDesc'] ),
                                        'type' => 'https://w3id.org/xapi/cmi5/activitytype/course'
                                    ),
                                    'objectType' => 'Activity',
                                ),
                    'context' => array(
                                    'instructor' => array(
                                        'name' => $tax_args['instructor'],
                                        'mbox' => 'mailto:'.$tax_args['inst_email'],
                                    ),
                                    'platform' => strval($platform),
                                    'language' => strval($plt_lang),
                                    "extensions" => array(
                                        "https://nelc.gov.sa/extensions/platform" => array(
                                            "name" => array(
                                                "ar-SA" => strval($platformAr),
                                                "en-US" => strval($platformEn)   
                                            )
                                        )
                                    )
                                ),
                    'result' => array(
                                    "score" => array(
                                        "scaled" =>  $tax_args['scaled']
                                        ),
                                    "completion" => $tax_args['completion'],
                        ),                
                    'timestamp' => date('Y-m-d\TH:i:s'.substr((string)microtime(), 1, 4).'\Z')
                );
                break;
            case "attempted":
                $this->statment_args = array(
                    'actor' => array(
                        'name' => $tax_args['name'],
                        'mbox'  => 'mailto:'.$tax_args['email'],
                        'objectType' => 'Agent',
                    ),
                    'verb' => array(
                                'id' => 'http://adlnet.gov/expapi/verbs/attempted',
                                'display' => array("en-US" => "attempted") 
                            ),
                    'object' => array(
                                    'id'=> $tax_args['quizUrl'],
                                    'definition' => array(
                                        'name' => array( strval($plt_lang) => $tax_args['quizName'] ),
                                        'description' => array( strval($plt_lang) => $tax_args['quizDesc'] ),
                                        'type' => 'http://id.tincanapi.com/activitytype/unit-test'
                                    ),
                                    'objectType' => 'Activity',
                                ),
                    'context' => array(
                                    'instructor' => array(
                                        'name' => $tax_args['instructor'],
                                        'mbox' => 'mailto:'.$tax_args['inst_email'],
                                    ),
                                    'platform' => strval($platform),
                                    'language' => strval($plt_lang),
                                    'extensions' => array (
                                        "http://id.tincanapi.com/extension/attempt-id" => $tax_args['attempNumber'],
                                        "http://id.tincanapi.com/extension/browser-info" => array(
                                            "code_name" => $br_os,
                                            "name" => $br_name,  
                                            "version" => $br_ver
                                        ),
                                        "https://nelc.gov.sa/extensions/platform" => array(
                                            "name" => array(
                                                "ar-SA" => strval($platformAr),
                                                "en-US" => strval($platformEn)   
                                            )
                                        )
                                    ),
                                    'contextActivities' => array(
                                        'parent' => array(
                                            array (
                                                'id' => $tax_args['courseId'],
                                                'definition' => array(  
                                                    'name' => array(strval($plt_lang) => $tax_args['courseName']),
                                                    'description' => array( strval($plt_lang) => $tax_args['courseDesc'] ),
                                                    'type' => 'https://w3id.org/xapi/cmi5/activitytype/course'
                                                ),
                                                'objectType' => "Activity"
                                            )
                                        )
                                    )
                                ),
                                'result' => array(
                                    "score" => array(
                                        "scaled" => $tax_args['scaled'],
                                        "raw" => $tax_args['raw'],
                                        "min" => $tax_args['min'],
                                        "max" => $tax_args['max']
                                    ),
                                    'completion' => $tax_args['completion'],
                                    "success" => $tax_args['success'],
                                ),
                    'timestamp' => date('Y-m-d\TH:i:s'.substr((string)microtime(), 1, 4).'\Z')
                );
                break;
            case "completedCourse":
                $this->statment_args = array(
                    'actor' => array(
                                'name' => $tax_args['name'],
                                'mbox'  => 'mailto:'.$tax_args['email'],
                                'objectType' => 'Agent',
                            ),
                    'verb' => array(
                                'id' => 'http://adlnet.gov/expapi/verbs/completed',
                                'display' => array("en-US" => "completed") 
                            ),
                    'object' => array(
                                'id'=> $tax_args['courseId'],
                                'definition' => array(
                                    'name' => array(strval($plt_lang) => $tax_args['courseName']),
                                    'description' => array( strval($plt_lang) => $tax_args['courseDesc'] ),
                                    'type' => 'https://w3id.org/xapi/cmi5/activitytype/course'
                                ),
                                'objectType' => 'Activity',
                            ),
                    'context' => array(
                                    'instructor' => array(
                                        'name' => $tax_args['instructor'],
                                        'mbox' => 'mailto:'.$tax_args['inst_email'],
                                    ),
                                    'platform' => strval($platform),
                                    'language' => strval($plt_lang),
                                    'extensions' => array (
                                        "https://nelc.gov.sa/extensions/platform" => array(
                                            "name" => array(
                                                "ar-SA" => strval($platformAr),
                                                "en-US" => strval($platformEn)   
                                            )
                                        )
                                    )
                                ),
            
                    'timestamp' => date('Y-m-d\TH:i:s'.substr((string)microtime(), 1, 4).'\Z')
                );
                break;
            case "earned":
                $this->statment_args = array(
                    'actor' => array(
                                'name' => $tax_args['name'],
                                'mbox'  => 'mailto:'.$tax_args['email'],
                                'objectType' => 'Agent',
                            ),
                    'verb' => array(
                                'id' => 'http://id.tincanapi.com/verb/earned',
                                'display' => array("en-US" => "earned") 
                            ),
                    'object' => array(
                                    'id'=> $tax_args['certUrl'],
                                    'definition' => array(
                                        'name' => array(strval($plt_lang) => $tax_args['certName']),
                                        'type' => 'https://www.opigno.org/en/tincan_registry/activity_type/certificate'
                                    ),
                                    'objectType' => 'Activity',
                                ),
                    'context' => array(
                                    'extensions' => array (
                                        "http://id.tincanapi.com/extension/jws-certificate-location" => $tax_args['certUrl'],
                                        "https://nelc.gov.sa/extensions/platform" => array(
                                            "name" => array(
                                                "ar-SA" => strval($platformAr),
                                                "en-US" => strval($platformEn)
                                            )
                                        )
                                    ),
                                    'platform' => strval($platform),
                                    'language' => strval($plt_lang),
                                    'contextActivities' => array(
                                        'parent' => array(
                                            array (
                                                'id'=> $tax_args['courseId'],
                                                'definition' => array(
                                                    'name' => array(strval($plt_lang) => $tax_args['courseName']),
                                                    'description' => array( strval($plt_lang) => $tax_args['courseDesc'] ),
                                                ),
                                                'objectType' => "Activity"
                                            )
                                        )
                                    )
                                ),
                    'timestamp' => date('Y-m-d\TH:i:s'.substr((string)microtime(), 1, 4).'\Z')
                );
                break;
            case "rated":
                $this->statment_args = array(
                    'actor' => array(
                        'name' => $tax_args['name'],
                        'mbox'  => 'mailto:'.$tax_args['email'],
                                'objectType' => 'Agent',
                            ),
                    'verb' => array(
                                'id' => 'http://id.tincanapi.com/verb/rated',
                                'display' => array('en-US' => 'rated') 
                            ),
                    'object' => array(
                        'id'=> $tax_args['courseId'],
                        'definition' => array(
                                        'name' => array(strval($plt_lang) => $tax_args['courseName']),
                                        'description' => array( strval($plt_lang) => $tax_args['courseDesc'] ),
                                        'type' => 'https://w3id.org/xapi/cmi5/activitytype/course'
                                    ),
                                    'objectType' => 'Activity',
                                ),
                    'context' => array(
                                    'instructor' => array(
                                        'name' => $tax_args['instructor'],
                                        'mbox' => 'mailto:'.$tax_args['inst_email'],
                                    ),
                                    'platform' => strval($platform),
                                    'language' => strval($plt_lang),
                                    "extensions" => array(
                                        "https://nelc.gov.sa/extensions/platform" => array(
                                            "name" => array(
                                                "ar-SA" => strval($platformAr),
                                                "en-US" => strval($platformEn)
                                            )
                                        )
                                    )
                                ),
                    "result" => array(
                                "score" => array(
                                    "scaled" => $tax_args['scaled'],
                                    "raw" => $tax_args['raw'],
                                    "min" => $tax_args['min'],
                                    "max" => $tax_args['max']
                                ),
                                "response" => $tax_args['comment']
                            ),
                    'timestamp' => date('Y-m-d\TH:i:s'.substr((string)microtime(), 1, 4).'\Z')
                );
                break;
            default:
                echo "Register statment";
            }
		// Register statment.
		//add_action( 'init', array( $this, 'register_statment' ) );
	}


}
