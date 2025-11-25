<?php

namespace App\Lib;

class ClientInfo
{

    /**
     * Get requestor IP information
     *
     * @return array
     */
    public static function ipInfo()
    {
        $ip     = getRealIP();
        $apiKey = '9183m0-96792g-1639d4-in8718';

        $url      = "http://proxycheck.io/v2/?key=$apiKey&days=7&vpn=1&inf=1&asn=1&risk=1&node=1&port=1&seen=1";
        $arr      = ['ips' => $ip, 'tag' => 'timodesk.com'];
        $response = json_decode(CurlRequest::curlPostContent($url, $arr));

        if ($response && $response->status == 'ok') {
            $data['country']  = [$response->$ip->country ?? ''];
            $data['city']     = [$response->$ip->city ?? ''];
            $data['area']     = [$response->$ip->region ?? ''];
            $data['code']     = [$response->$ip->isocode ?? ''];
            $data['long']     = [$response->$ip->longitude ?? ''];
            $data['lat']      = [$response->$ip->latitude ?? ''];
            $data['timezone'] = [$response->$ip->timezone ?? ''];
            $data['ip']       = $ip;
            $data['time']     = date('Y-m-d h:i:s A');
        } else {
            $data = [];
        }

        return $data;
    }

    /**
     * Get requestor operating system information
     *
     * @return array
     */
    public static function osBrowser()
    {
        $userAgent  = $_SERVER['HTTP_USER_AGENT'];
        $osPlatform = "Unknown OS Platform";
        $osArray    = array(
            '/windows nt 10/i'      => 'Windows 10',
            '/windows nt 6.3/i'     => 'Windows 8.1',
            '/windows nt 6.2/i'     => 'Windows 8',
            '/windows nt 6.1/i'     => 'Windows 7',
            '/windows nt 6.0/i'     => 'Windows Vista',
            '/windows nt 5.2/i'     => 'Windows Server 2003/XP x64',
            '/windows nt 5.1/i'     => 'Windows XP',
            '/windows xp/i'         => 'Windows XP',
            '/windows nt 5.0/i'     => 'Windows 2000',
            '/windows me/i'         => 'Windows ME',
            '/win98/i'              => 'Windows 98',
            '/win95/i'              => 'Windows 95',
            '/win16/i'              => 'Windows 3.11',
            '/macintosh|mac os x/i' => 'Mac OS X',
            '/mac_powerpc/i'        => 'Mac OS 9',
            '/linux/i'              => 'Linux',
            '/ubuntu/i'             => 'Ubuntu',
            '/iphone/i'             => 'iPhone',
            '/ipod/i'               => 'iPod',
            '/ipad/i'               => 'iPad',
            '/android/i'            => 'Android',
            '/blackberry/i'         => 'BlackBerry',
            '/webos/i'              => 'Mobile',
        );
        foreach ($osArray as $regex => $value) {
            if (preg_match($regex, $userAgent)) {
                $osPlatform = $value;
            }
        }
        $browser      = "Unknown Browser";
        $browserArray = array(
            '/msie/i'      => 'Internet Explorer',
            '/firefox/i'   => 'Firefox',
            '/safari/i'    => 'Safari',
            '/chrome/i'    => 'Chrome',
            '/edge/i'      => 'Edge',
            '/opera/i'     => 'Opera',
            '/netscape/i'  => 'Netscape',
            '/maxthon/i'   => 'Maxthon',
            '/konqueror/i' => 'Konqueror',
            '/mobile/i'    => 'Handheld Browser',
        );
        foreach ($browserArray as $regex => $value) {
            if (preg_match($regex, $userAgent)) {
                $browser = $value;
            }
        }

        $data['os_platform'] = $osPlatform;
        $data['browser']     = $browser;

        return $data;
    }

}
