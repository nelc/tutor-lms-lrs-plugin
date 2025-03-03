<?php
namespace tutorLmsLrsPlugin\includes\Interactions;

class Registered
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
    }

    public function send($data = [])
    {
        global $CFG;
        $actor = $data['name'];
        $actorEmail = $data['email'];
        $instructor = $data['instructor'];
        $instructorEmail = $data['inst_email'];
        $courseId = get_site_url() . '/course/view.php?id=' . esc_attr($data['courseId']);
        $courseTitle = $data['courseName'];
        $courseDesc = $data['courseDesc'];
        $this->lang = $data['courseLang'] ?? 'en-US';
        $duration = $data['duration'];
        $learneMobileNo = $data['learneMobileNo'];
        $learnerFullName = $data['learnerFullName'];
        $learnerNationality = $data['learnerNationality'];
        $dateOfBirth = $data['dateOfBirth'];

        $vars = array(
            'actor' => array(
                        'name' => strval($actor),
                        'mbox'  => 'mailto:'.strval($actorEmail),
                        'objectType' => 'Agent',
                    ),
            'verb' => array(
                        'id' => 'http://adlnet.gov/expapi/verbs/registered',
                        'display' => array('en-US' => 'registered') 
                    ),
            'object' => array(
                            'id'=> strval($courseId),
                            'definition' => array(
                                'name' => array(strval($this->lang) => strval($courseTitle)),
                                'description' => array(strval($this->lang) => strval($courseDesc)),
                                'type' => 'https://w3id.org/xapi/cmi5/activitytype/course'
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
                            "extensions" => array(
                                'https://nelc.gov.sa/extensions/duration'=> $duration,
                                'https://nelc.gov.sa/extensions/learner_mobile_no'=> $learneMobileNo,
                                'https://nelc.gov.sa/extensions/learner_full_name'=> $learnerFullName,
                                'https://nelc.gov.sa/extensions/learner_nationality'=> $learnerNationality,
                                'https://nelc.gov.sa/extensions/date_of_birth'=> $dateOfBirth,
                                'https://nelc.gov.sa/extensions/platform' => array(
                                    "name" => array(
                                        "ar-SA" => strval($this->platformname_ar),
                                        "en-US" => strval($this->platformname_en)
                                    )
                                )
                            )
                        ),
            'timestamp' => date('Y-m-d\TH:i:s'.substr((string)microtime(), 1, 4).'\Z')
        );

        return $vars;
    }
}
