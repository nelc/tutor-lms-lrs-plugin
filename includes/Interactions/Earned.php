<?php

namespace tutorLmsLrsPlugin\includes\Interactions;

class Earned
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
    }

    public function send($data = [])
    {
        global $CFG;

        $actor = $data['name'];
        $actorEmail = $data['email'];
        $courseId = get_site_url() . '/course/view.php?id=' . esc_attr($data['courseId']);
        $courseTitle = $data['courseName'];
        $courseDesc = $data['courseDesc'];
        $this->lang = $data['courseLang'] ?? 'en-US';
        $certUrl = $data['certUrl'];
        $certName = $data['certName'];

        // استخراج cert_hash من الرابط
        $parsedUrl = parse_url($certUrl);
        parse_str($parsedUrl['query'] ?? '', $queryParams);

        $certHash = $queryParams['cert_hash'] ?? null;

        // تحديد الـ ID النهائي
        $certificateId = $certHash
            ? get_site_url() . '/certificate/' . $certHash
            : get_site_url() . '/certificate/' . md5($certUrl);

        $vars = array(
            'actor' => array(
                'name' => strval($actor),
                'mbox'  => 'mailto:'.strval($actorEmail),
                        'objectType' => 'Agent',
                    ),
            'verb' => array(
                        'id' => 'http://id.tincanapi.com/verb/earned',
                        'display' => array("en-US" => "earned") 
                    ),
            'object' => array(
                            'id'=> strval($certificateId),
                            'definition' => array(
                                'name' => array($this->lang => strval($certName)),
                                'type' => 'https://www.opigno.org/en/tincan_registry/activity_type/certificate'
                            ),
                            'objectType' => 'Activity',
                        ),
            'context' => array(
                            'extensions' => array (
                                "http://id.tincanapi.com/extension/jws-certificate-location" => strval($certUrl),
                                "https://nelc.gov.sa/extensions/platform" => array(
                                    "name" => array(
                                        "ar-SA" => strval($this->platformname_ar),
                                        "en-US" => strval($this->platformname_en)
                                    )
                                )
                            ),
                            'platform' => strval($this->platform),
                            'language' => strval($this->lang),
                            'contextActivities' => array(
                                'parent' => array(
                                    array (
                                        'id' => strval($courseId),
                                        'definition' => array(  
                                            'name' => array(strval($this->lang) => strval($courseTitle)),
                                            'description' => array( strval($this->lang) => strval($courseDesc) ),                                            'type' => 'https://w3id.org/xapi/cmi5/activitytype/course'
                                        ),
                                        'objectType' => "Activity"
                                    )
                                )
                            )
                        ),
            'timestamp' => date('Y-m-d\TH:i:s'.substr((string)microtime(), 1, 4).'\Z')
        );

        return $vars;
    }
}
