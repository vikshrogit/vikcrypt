<?php
namespace VIKSHRO\VIKCRYPT\Component;

class License{
    public static function getNewLicense($version=1,$host="https://vikshro.in"): string
    {
        $license="{\"OS\":\"".PHP_OS."\",\"PLATFORM\":\"".PHP_OS_FAMILY."\",\"HOST\":\"".$host."\",\"Server\":\"".php_uname()."\"}";
        for($i=0;$i < (10 + ($version - 1));$i++){
            $license = base64_encode($license);
        }
        return $license;
    }

    public static function secrete($version=1,$host="https://vikshro.in"): string
    {
        $secrete = "{\"PLATFORM\":\"".PHP_OS_FAMILY."\",\"VERSION\":\"V".$version."\",\"HOST\":\"".$host."\"}";
        return base64_encode($secrete);
    }

    public static function validateLicense($license,$version=1,$host="https://vikshro.in"): bool
    {
        $clicense="{\"OS\":\"".PHP_OS."\",\"PLATFORM\":\"".PHP_OS_FAMILY."\",\"HOST\":\"".$host."\",\"Server\":\"".php_uname()."\"}";
        for($i=0;$i < (10 + ($version - 1));$i++){
            $license = base64_decode($license);
        }
        if($license == $clicense){
            return true;
        }else{
            return false;
        }
    }

    public static function LicenseData($license,$version=1,$host="https://vikshro.in"): mixed
    {
        for($i=0;$i < (10 + ($version - 1));$i++){
            $license = base64_decode($license);
        }
        return json_decode($license);
    }

    public static function secretData($secret,$version=1,$host="https://vikshro.in"): mixed
    {
        return json_decode(base64_decode($secret));
    }
}