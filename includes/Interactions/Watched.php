<?php
namespace tutorLmsLrsPlugin\includes\Interactions;

class Watched
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
        global $CFG;

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
        global $CFG;
        
        $actor = $data['name'];
        $actorEmail = $data['email'];
        $lessonUrl = $data['lessonUrl'];
        $lessonTitle = $data['lessonName'];
        $lessonDesc = $data['lessonDesc'];
        $instructor = $data['instructor'];
        $instructorEmail = $data['inst_email'];
        $courseId = get_site_url() . '/course/view.php?id=' . esc_attr($data['courseId']);
        $courseTitle = $data['courseName'];
        $courseDesc = $data['courseDesc'];
        $this->lang = $data['courseLang'] ?? 'en-US';
        $completion = $data['completion'];
        $duration = $data['duration'];

        $vars = array(
            'actor' => array(
                        'name' => strval($actor),
                        'mbox'  => 'mailto:'.strval($actorEmail),
                        'objectType' => 'Agent',
                    ),
            'verb' => array(
                        'id' => 'https://w3id.org/xapi/acrossx/verbs/watched',
                        'display' => array("en-US" => "watched") 
                    ),
            'object' => array(
                            'id'=> strval($lessonUrl),
                            'definition' => array(
                                'name' => array(strval($this->lang) => strval($lessonTitle)),
                                'description' => array(strval($this->lang) => strval($lessonDesc)),
                                'type' => 'https://w3id.org/xapi/video/activity-type/video'
                            ),
                            'objectType' => 'Activity',
                        ),
            'context' => array(
                            'instructor' => array(
                                'name' => strval($instructor),
                                'mbox' => 'mailto:'.strval($instructorEmail),
                            ),
                            'platform' => strval($this->platform),
                            'language' => strval($this->lang),
                            'extensions' => array (
                                "http://id.tincanapi.com/extension/browser-info" => array(
                                    "code_name" => strval($this->browserCode),
                                    "name" => strval($this->browserName),  
                                    "version" => strval($this->browserVersion)
                                ),
                                'https://nelc.gov.sa/extensions/platform'=> array(
                                    'name'=> array(
                                        'ar-SA'=> strval($this->platformname_ar),
                                        'en-US'=> strval($this->platformname_en)
                                    )
                                )

                            ),
                            'contextActivities' => array(
                                'parent' => array(
                                    array (
                                        'id' => strval($courseId),
                                        'definition' => array(  
                                            'name' => array(strval($this->lang) => strval($courseTitle)),
                                            'description' => array( strval($this->lang) => strval($courseDesc) ),
                                            'type' => 'https://w3id.org/xapi/cmi5/activitytype/course'
                                        ),
                                        'objectType' => "Activity"
                                    )
                                )
                            )
                        ),
                        'result' => array(
                            'completion' => $completion,
                            'duration' => $duration,
                        ),
            'timestamp' => date('Y-m-d\TH:i:s'.substr((string)microtime(), 1, 4).'\Z')
        );

        return $vars;
    }
}
