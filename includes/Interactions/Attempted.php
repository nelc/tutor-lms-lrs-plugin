<?php

namespace tutorLmsLrsPlugin\includes\Interactions;

class Attempted
{
    protected $platformname_ar;
    protected $platformname_en;
    protected $platform;
    protected $lang;
    protected $browserName;
    protected $browserVersion;
    protected $browserCode;

    public function __construct()
    {

        $this->platformname_ar = get_option('lmtni_xapi_platform_ar_name');
        $this->platformname_en = get_option('lmtni_xapi_platform_en_name');
        $this->platform = get_option('lmtni_xapi_platform');
        //$this->lang = current_language() === 'ar' ? 'ar-SA' : 'en-US';

        $browser = Browser::getBrowser();

        $this->browserName = $browser["name"] ?? $_SERVER['HTTP_USER_AGENT'];
        $this->browserVersion = $browser['version'] ?? '';
        $this->browserCode =  'Mozilla';
    }

    public function send($data = [])
    {
        $actor = $data['name'];
        $actorEmail = $data['email'];
        $quizUrl = $data['quizUrl'];
        $quizName = $data['quizName'];
        $quizDesc = $data['quizDesc'];
        $instructor = $data['instructor'];
        $inst_email = $data['inst_email'];
        $attempNumber = $data['attempNumber'];
        $courseId = get_site_url() . '/course/view.php?id=' . esc_attr($data['courseId']);
        $courseName = $data['courseName'];
        $courseDesc = $data['courseDesc'];
        $this->lang = $data['courseLang'] ?? 'en-US';
        $scaled = $data['scaled'];
        $raw = $data['raw'];
        $min = $data['min'];
        $max = $data['max'];
        $completion = $data['completion'];
        $success = $data['success'];

        $vars = array(
            'actor' => array(
                'name' => strval($actor),
                'mbox'  => 'mailto:'.strval($actorEmail),
                'objectType' => 'Agent',
            ),
            'verb' => array(
                'id' => 'http://adlnet.gov/expapi/verbs/attempted',
                'display' => array("en-US" => "attempted")
            ),
            'object' => array(
                'id' => strval($quizUrl),
                'definition' => array(
                    'name' => array(strval($this->lang) => strval($quizName)),
                    'description' => array(strval($this->lang) => strval($quizDesc)),
                    'type' => 'http://id.tincanapi.com/activitytype/unit-test'
                ),
                'objectType' => 'Activity',
            ),
            'context' => array(
                'instructor' => array(
                    'name' => strval($instructor),
                    'mbox' => 'mailto:'.strval($inst_email),
                ),
                'platform' => strval($this->platform),
                'language' => strval($this->lang),
                'extensions' => array(
                    "http://id.tincanapi.com/extension/attempt-id" => strval($attempNumber),
                    "http://id.tincanapi.com/extension/browser-info" => array(
                        "code_name" => strval($this->browserCode),
                        "name" => strval($this->browserName),
                        "version" => strval($this->browserVersion)
                    ),
                    "https://nelc.gov.sa/extensions/platform" => array(
                        "name" => array(
                            "ar-SA" => strval($this->platformname_ar),
                            "en-US" => strval($this->platformname_en)
                        )
                    )
                ),
                'contextActivities' => array(
                    'parent' => array(
                        array(
                            'id' => strval($courseId),
                            'definition' => array(
                                'name' => array(strval($this->lang) => strval($courseName)),
                                'description' => array(strval($this->lang) => strval($courseDesc)),
                                'type' => 'https://w3id.org/xapi/cmi5/activitytype/course'
                            ),
                            'objectType' => "Activity"
                        )
                    )
                )
            ),
            'result' => array(
                "score" => array(
                    "scaled" => $scaled,
                    "raw" => $raw,
                    "min" => $min,
                    "max" => $max
                ),
                'completion' => $completion,
                "success" => $success,
            ),
            'timestamp' => date('Y-m-d\TH:i:s'.substr((string)microtime(), 1, 4).'\Z')
        );

        return $vars;
    }
}
