<?php 
namespace VIKSHRO\VIKCRYPT\Component;


use Vikshro\Oluo\Controllers\App_config;
use Vikshro\Oluo\Controllers\Logs;

class App
{
    


    public static function url(): string
    {
        if(isset($_SERVER['HTTPS'])){
            $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
        }
        else{
            $protocol = 'http';
        }
        return $protocol . "://" . $_SERVER['HTTP_HOST'];
    }

    
}