<?php
namespace VIKSHRO\VIKCRYPT\Component;

class License{
    public static function getNewLicense($version=1,$host=""): string
    {
        if(is_null($host)){
            $host=App::url();
        }
        $license="{\"OS\":\"".PHP_OS."\",\"PLATFORM\":\"".PHP_OS_FAMILY."\",\"HOST\":\"".($host??App::url())."\",\"Server\":\"".php_uname()."\"}";
        for($i=0;$i < (10 + ($version - 1));$i++){
            $license = base64_encode($license);
        }
        return $license;
    }

    public static function secrete($version=1,$host=""): string
    {
        if(is_null($host)){
            $host=App::url();
        }
        $secrete = "{\"PLATFORM\":\"".PHP_OS_FAMILY."\",\"VERSION\":\"V".$version."\",\"HOST\":\"".(($host=="")?APP::url():$host)."\"}";
        return base64_encode($secrete);
    }

    public static function validateLicense($license,$version=1,$host=""): bool
    {
        $clicense="{\"OS\":\"".PHP_OS."\",\"PLATFORM\":\"".PHP_OS_FAMILY."\",\"HOST\":\"".(($host=="")?APP::url():$host)."\",\"Server\":\"".php_uname()."\"}";
        for($i=0;$i < (10 + ($version - 1));$i++){
            $license = base64_decode($license);
        }
        if($license == $clicense){
            return true;
        }else{
            return false;
        }
    }

    public static function LicenseData($license,$version=1,$host=""): mixed
    {
        for($i=0;$i < (10 + ($version - 1));$i++){
            $license = base64_decode($license);
        }
        return json_decode($license);
    }

    public static function secretData($secret,$version=1,$host=""): mixed
    {
        return json_decode(base64_decode($secret));
    }
}