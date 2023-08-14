<?php
namespace VIKSHRO\VIKCRYPT\Component;
use Exception;

class VIKCRYPT{
    protected Crypto $crypto;
    private string $license;

    private string $secret;

    private string $key='';

    /**
     * @throws Exception
     */
    function __construct($key=''){
        $this->key = $key;
        if(file_exists(__DIR__.'/License/License')){
            $this->license = json_decode(file_get_contents(__DIR__.'/License/License'))->License;
        }else{
            $this->license = License::getNewLicense();
            $lData = '{"License":"'.$this->license.'","Created_at":"'.date("Y-m-d h:i:sa").'"}';
            if(!is_dir(__DIR__.'/License')){
                mkdir(__DIR__.'/License');
            }
            $file = fopen(__DIR__.'/License/License','w');
            fwrite($file,$lData);
            fclose($file);
            //file_put_contents('License/vik.license',$lData);
        }

        if(file_exists(__DIR__.'/License/Secret.db')){
            $this->secret = json_decode(file_get_contents(__DIR__.'/License/Secret.db'))->Secret;
        }else{
            $this->secret = License::secrete();
            $lData = '{"Secret":"'.$this->secret.'","Created_at":"'.date("Y-m-d h:i:sa").'"}';
            if(!is_dir(__DIR__.'/License')){
                mkdir(__DIR__.'/License');
            }
            $file = fopen(__DIR__.'/License/Secret.db','w');
            fwrite($file,$lData);
            fclose($file);
        }

        if($this->key){
            $this->crypto = new Crypto($this->license,key:$this->key,secret:$this->secret);
        }else{
            $this->crypto = new Crypto($this->license,secret:$this->secret);
        }
        
    }

    public function getLicenseData(): mixed
    {
        return License::LicenseData($this->license);
    }

    public function getSecretData(): mixed
    {
        return License::secretData($this->secret);
    }

    /**
     * @throws Exception
     */
    public function encrypt($data, $key=""): string
    {
        if(!$this->key && !$key){
            throw new Exception('Key is not Defined!',500);
        }
        return $this->crypto->encrypt($data,$key);
    }

    /**
     * @throws Exception
     */
    public function decrypt($data, $key=""): ?string
    {
        if(!$this->key && !$key){
            throw new Exception('Key is not Defined!',500);
        }
        return $this->crypto->decrypt($data,$key);
    }

    /**
     * @throws Exception
     */
    public function dbEncrypter($db="mysqli", $host="localhost", $port=3306, $name="mysql", $user="app", $password="App@123"): string
    {
        $data = json_encode(array(
            "DB_TYPE"=>$db,
            "DB_HOST"=>$host,
            "DB_PORT"=>$port,
            "DB_NAME"=>$name,
            "DB_USER"=>$user,
            "DB_PASS"=>$password,
            
        ));
        return $this->encrypt($data,"OluO_DB_ENCRYPTION");
    }

    /**
     * @throws Exception
     */
    public function dbDecrypt($db): mixed
    {
        return json_decode($this->decrypt($db,"OluO_DB_ENCRYPTION"));
    }

    /**
     * @throws Exception
     */
    public function passwordHasher(string $password, string $userName): string
    {
        return md5(sha1(md5(sha1($this->crypto->multiBaseEncode($this->encrypt($password,"VIKCRYPT_".$userName))))));
    }
    

}