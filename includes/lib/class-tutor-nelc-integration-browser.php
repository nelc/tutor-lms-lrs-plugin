<?php
// Browser.php - اسم الملف الذي ستضع فيه هذا الكود
class Browser {
    public static function getBrowser() {
        $u_agent = $_SERVER['HTTP_USER_AGENT'];
        $bname = 'Unknown';
        $platform = 'Unknown';
        $version= "";

        // الحصول على النظام الأساسي
        if (preg_match('/linux/i', $u_agent)) {
            $platform = 'Linux';
        } elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
            $platform = 'Mac';
        } elseif (preg_match('/windows|win32/i', $u_agent)) {
            $platform = 'Windows';
        }

        // الحصول على اسم المتصفح والإصدار
        if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent)) {
            $bname = 'Internet Explorer';
            $ub = "MSIE";
        } elseif(preg_match('/Firefox/i',$u_agent)) {
            $bname = 'Mozilla Firefox';
            $ub = "Firefox";
        } elseif(preg_match('/OPR/i',$u_agent)) {
            $bname = 'Opera';
            $ub = "Opera";
        } elseif(preg_match('/Chrome/i',$u_agent)) {
            $bname = 'Google Chrome';
            $ub = "Chrome";
        } elseif(preg_match('/Safari/i',$u_agent)) {
            $bname = 'Apple Safari';
            $ub = "Safari";
        } elseif(preg_match('/Netscape/i',$u_agent)) {
            $bname = 'Netscape';
            $ub = "Netscape";
        }

        // الحصول على رقم الإصدار
        $known = array('Version', $ub, 'other');
        $pattern = '#(?<browser>' . join('|', $known) .
        ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
        if (!preg_match_all($pattern, $u_agent, $matches)) {
            // لا توجد معلومات حول الإصدار
        }

        // اصطياد الإصدار
        $i = count($matches['browser']);
        if ($i != 1) {
            if (strripos($u_agent, "Version") < strripos($u_agent, $ub)){
                $version = $matches['version'][0];
            } else {
                $version = $matches['version'][1];
            }
        } else {
            $version = $matches['version'][0];
        }

        // مزيد من المعالجة
        if ($version == null || $version == "") {$version = "?";}

        return array(
            'userAgent' => $u_agent,
            'name'      => $bname,
            'version'   => $version,
            'platform'  => $platform,
            'pattern'    => $pattern
        );
    }
}
