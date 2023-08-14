<?php

namespace VIKSHRO\VIKCRYPT\Component;

use Exception;

class VAToken extends VIKCRYPT
{
    public function __construct($key = '')
    {
        parent::__construct($key);
    }

    /**
     * @throws Exception
     */

    public function generateToken(mixed $user,mixed $cookie,mixed $header,int $AV=1): mixed
    {
        $session = $this->generateSessionID();
        $min = rand(15,59);
        $now=time();
        $exp = strtotime("+".$min." minutes");
        $tokenHead=json_encode(array(
            "SESSION"=> $session,
            "VERSION"=> $AV,
            "METHOD"=>"VCRYPT-1",
            "BASE"=>"MODIFIED FERNET",
            "PLATFORM"=> isset($header["SEC_CH_UA_PLATFORM"]) ?$header["SEC_CH_UA_PLATFORM"]:"None",
            "HOST"=> isset($header["HOST"])?$header["HOST"] : APP::url(),
            "MOBILE"=> ((int) str_replace("?","",isset($header["SEC_CH_UA_MOBILE"])?$header["SEC_CH_UA_MOBILE"]:"0"))??0,
            "UA"=> isset($header["USER_AGENT"]) ?$header["USER_AGENT"] : "None",
            "SECURE"=> (strtolower(isset($header["X_FORWARDED_PROTO"])?$header["X_FORWARDED_PROTO"]:"") == "https") ? 1 : 0,
        ));
        $user["username"] = isset($user["email"])?$user["email"]:(isset($user["user_id"])?$user["user_id"]:"Unknown");
        unset($user["email"]);
        $user["id"] = isset($user["id"])?$user["id"]:0;
        //unset($user["id"]);
        $user["host"] = isset($header["HOST"])?$header["HOST"]:APP::url();
        $user["aud"] = isset($header["HOST"])?$header["HOST"]:APP::url();
        $user["auth"] = isset($user["auth"])?$user["auth"]:1;
        //unset($user["auth"]);
        $user["role"] = isset($user["role"])?$user["role"]:"None";
        $user["permissions"] = isset($user["permissions"])?$user["permissions"]:["None"];
        $user["exp"] = $exp;
        $user["gen"] = $now;
        $user["interval"] = $min;

        /*$tokenData = json_encode(array(
            "host" => isset($header["HOST"])?$header["HOST"]:APP::url(),
            "aud" => isset($header["HOST"])?$header["HOST"]:APP::url(),
            "username"=>isset($user["email"])?$user["email"]:(isset($user["user_id"])?$user["user_id"]:"Unknown"),
            "id" => isset($user["id"])?$user["id"]:0,
            "auth" => isset($user["auth"])?$user["auth"]:1,
            "firstname" => isset($user['first_name'])?$user['first_name']:"Unknown",
            "role" => isset($user["role"])?$user["role"]:"None",
            "permissions" => isset($user["permissions"])?$user["permissions"]:["None"],
            "exp" => $exp,
            "gen" => $now,
            "interval" => $min
        ));*/
        $tokenData = json_encode($user);
        $tokenfoot=json_encode(array(
            "Signature"=>"VCRTPT-256",
            "ITERATION"=>($min % 5),
            "Time"=>date("Y-m-d h:i:s a T")
        ));
        return json_decode("{
            \"access_token\":\"".$this->crypto->multiBaseEncode($tokenHead,($min % 5)).".".$this->encrypt($tokenData,$session).".".$this->encrypt($tokenfoot,"VATOKEN_FOOT")."\",
            \"created_at\":\"".date("Y-m-d h:i:s a T")."\",
            \"expires_in\":".($min * 60).",
            \"token_type\":\"bearer\",
            \"refresh_token\":\"".($this->encrypt($session,"VATOKEN_REFRESH"))."\"
        }",true);
    }


    /**
     * @throws Exception
     */
    public function tokenValidation(mixed $cookie, mixed $header, int $AV=1){
        if(!isset($header["AUTHORIZATION"]) || !isset($header["authorization"])){
            //print_r($header);
            return  array(
                "Status"=>"Failed",
                "StatusCode"=>401,
                "Error Message"=>"No Authorization Header!"
            );
        }
        $token = explode(" ",explode(",",$header["authorization"])[0]);
        //print_r($token);
        if(strtolower($token[0]) != "bearer"){
            return  array(
                "Status"=>"Failed",
                "StatusCode"=>401,
                "Error Message"=>"Unrecognised Authentication Method"
            );
        }
        $token = explode(".",$token[1]);

        if(count($token) != 3){
            return  array(
                "Status"=>"Failed",
                "StatusCode"=>401,
                "Error Message"=>"Invalid Token"
            );
        }
        $tokenFoot = json_decode($this->decrypt($token[2],"VATOKEN_FOOT"),true);

        if(!$tokenFoot){
            return  array(
                "Status"=>"Failed",
                "StatusCode"=>401,
                "Error Message"=>"Unidentified Token!"
            );
        }
        if($tokenFoot["Signature"] !="VCRTPT-256"){
            return  array(
                "Status"=>"Failed",
                "StatusCode"=>401,
                "Error Message"=>"Token is not VAToken!"
            );
        }
        $tokenHead = json_decode($this->crypto->multiBaseDecode($token[0],((int) $tokenFoot["ITERATION"])),true);

        if(!$tokenHead){
            return  array(
                "Status"=>"Failed",
                "StatusCode"=>401,
                "Error Message"=>"Incorrect Algorithm Details in Token!"
            );
        }

        if($tokenHead["SECURE"] != ((strtolower(isset($header["X_FORWARDED_PROTO"])?$header["X_FORWARDED_PROTO"]:"") == "https") ? 1 : 0)){
            return  array(
                "Status"=>"Failed",
                "StatusCode"=>401,
                "Error Message"=>"Invalid Requested Host!"
            );
        }

        if($tokenHead["HOST"] != (isset($header["HOST"])?$header["HOST"] : APP::url())){
            return array(
                "Status"=>"Failed",
                "StatusCode"=>401,
                "Error Message"=>"Invalid Requested Host!"
            );
        }

        if(($tokenHead["PLATFORM"] != (isset($header["SEC_CH_UA_PLATFORM"]) ?$header["SEC_CH_UA_PLATFORM"]:"None")) || ($tokenHead["MOBILE"] != (((int) str_replace("?","",isset($header["SEC_CH_UA_MOBILE"])?$header["SEC_CH_UA_MOBILE"]:"0"))??0)) || ($tokenHead["UA"] != (isset($header["USER_AGENT"]) ?$header["USER_AGENT"] : "None"))){
            return array(
                "Status"=>"Failed",
                "StatusCode"=>401,
                "Error Message"=>"Invalid Platform!"
            );
        }

        $tokenData = json_decode($this->decrypt($token[1],$tokenHead["SESSION"]),true);
        if(!$tokenData){
            return array(
                "Status"=>"Failed",
                "StatusCode"=>401,
                "Error Message"=>"Invalid Token Session!"
            );
        }

        if(((int) $tokenData["exp"]) < time() ){
            return array(
                "Status"=>"Failed",
                "StatusCode"=>401,
                "Error Message"=>"Expired Token!"
            );
        }
        unset($tokenData["exp"]);
        unset($tokenData["host"]);
        unset($tokenData["aud"]);
        unset($tokenData["gen"]);
        unset($tokenData["interval"]);
        unset($tokenData["auth"]);
        $tokenData["Status"] = "Success";
        $tokenData["StatusCode"] = 200;
        $tokenData["isValid"] = true;
        return $tokenData;
    }
    /**
     * @throws Exception
     */
    private function generateSessionID(): string
    {
        return preg_replace('/[^a-zA-Z0-9_ -]/s','',base64_encode(random_bytes(32)));
    }
}