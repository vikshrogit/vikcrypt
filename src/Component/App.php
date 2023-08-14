<?php 
namespace VIKSHRO\VIKCRYPT\Component;



class App
{
    


    public static function url(): string
    {
        if(isset($_SERVER)){
            if(isset($_SERVER['HTTPS'])){
                $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
            }
            else{
                $protocol = 'http';
            }
            if(isset($_SERVER['HTTP_HOST'])){
                $host = $_SERVER['HTTP_HOST'];
            }else{
                $host = "vikshro.in";
            }
            return $protocol . "://" . $host;
        }else{
            return "https://vikshro.in";
        }

    }

    
}