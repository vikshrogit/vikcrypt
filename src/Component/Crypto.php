<?php
namespace VIKSHRO\VIKCRYPT\Component;

use Exception;
use Fernet\Fernet;
class Crypto{

    private Fernet $fernet;
    private string $key='';

    private string $license;
    private string $secret;

    private int $version;

    private string $fernetKey;

    /**
     * @throws Exception
     */
    function __construct($license, $key="", $secret="", $version=1){
        $this->license = $license;
        $this->version = $version;
        if ($key){
            $this->key = $key;
        }
        if($secret){
            $this->secret=$secret;
        }

        if($this->license && $this->key){
            $this->keyBuilder();
        }
    }

    /**
     * @throws Exception
     */
    private function keyBuilder(): void
    {
        if ($this->license && $this->key){
            $decoded_license = $this->multiBaseDecode($this->license,(10 + ($this->version - 1)));
            $keySecret = $this->key."_VIK_[".base64_decode($this->secret)."]SALT_[".$decoded_license."]_V".$this->version;
            $fernetKey = base64_encode($keySecret);
            if (strlen($fernetKey) > 32){
                $fernetKey = substr($fernetKey,0,32);
            }else if(strlen($fernetKey) < 32){
                for($i=0;$i<(32 - strlen($fernetKey));$i++){
                    $fernetKey = $fernetKey."X";
                }
            }

            if($fernetKey){
                $this->fernetKey = base64_encode($fernetKey);
                //print("Key:: ".$this->$fernetKey);
            }

        }

        if($this->fernetKey){
            $this->fernet = new Fernet($this->fernetKey);
        }

    }

    /**
     * @throws Exception
     */
    public function encrypt(string $message, string $key=""): string
    {
        if(!$message){
            throw new Exception('No Message Data Found for Encryption!',500);
        }

        if($key){
            $this->key = $key;
            $this->keyBuilder();
        }

        if($this->fernetKey){
            return $this->fernet->encode($message);
        }else{
            throw new Exception('No key Data Found for Encryption!',500);
        }
    }

    /**
     * @throws Exception
     */
    public function decrypt(string $message, string $key=""): ?string
    {
        if(!$message){
            throw new Exception('No Message Data Found for Encryption!',500);
        }
        if($key){
            $this->key = $key;
            $this->keyBuilder();
        }
        if($this->fernetKey){
            return $this->fernet->decode($message);
        }else{
            throw new Exception('No key Data Found for Encryption!',500);
        }
    }

    /**
     * @param string $data
     * @param int $n
     * @return string
     * @throws Exception
     */
    public function multiBaseEncode(string $data, int $n=10): string
    {
        $out=$data;
        if (!$out){
            throw new Exception('Data Cannot be Empty! Please send the Data',500);
            //exit(0);
        }
        for($i=0;$i < $n;$i++){
            $out = base64_encode($out);
        }
        return $out;
    }

    /**
     * @throws Exception
     */
    public function multiBaseDecode($data, $n=10): mixed
    {
        $out=$data;
        if (!$out){
            throw new Exception('Data Cannot be Empty! Please send the Data',500);
            //exit(0);
        }
        for($i=0;$i < $n;$i++){
            $out = base64_decode($out);
        }
        return $out;
    }



}