<?php
namespace Limkie\Http;


/**
 * Cretae a response whit status and headers
 */
class Response{
    protected $status   = 200;
    protected $headers  = [];
    protected $body = '';


    public function __construct($contents = null){
        if($contents !== null){
            $this->setBody($contents);
        }
    }


    /**
     * Set status header
     *
     * @param integer $status
     * @return self
     */
    public function setStatus($status = 200){
        $status = (int)$status;
        $this->status = $status;

        return $this;
    }

    /**
     * set an header
     *
     * @param string $name
     * @param string $value
     * @return self
     */
    public function setHeader($name='',$value=''){
        $name = (string)$name;
        $value = (string)$value;

        $this->headers[$name] = $value;
    
        return $this;
    }

    /**
     * Set headers with an array pair hederName=>headerValue
     *
     * @param array $headers
     * @return self
     */
    public function setHeaders($headers = []){
        foreach($headers as $name=>$value){
            $this->setHeader($name,$value);
        }

        return $this;
    }

    /**
     * Fill response with return data
     *
     * @param mixed $contents
     * @return self
     */
    public function setBody($contents){
        $this->body = (string)$contents;
        return $this;
    }

    /**
     * The main method to send data 
     *
     * @param mixed $content if set, eoverwrite contents to send
     * @return 
     */
    public function send($contents=null){
        if(!is_null($contents)){
            $this->body = (string)$contents;
        }
        
        foreach($this->headers as $name=>$value){
            header("{$name}: {$value}");
        }

        http_response_code($this->status);

        echo $this->body;
        exit;
    }

    /**
     * Force a file to be downloaded
     *
     * @param string $source
     * @param string $filename
     * @return void
     */
    public function download($source,$filename=''){
        try{
            $filename = ($filename !== '')
                ?(string)$filename
                :date('Ymd_His');

            $this->setHeaders([
                'Content-Disposition'=>'attachment; filename="'.$filename.'"',
                'Content-Transfer-Encoding'=>'Binary'
            ]);
        
            $this->setHeader('Content-Type','application/octet-stream');
            
            if(is_file($source) && is_readable($source)){
                $mimeType = mime_content_type($source);
                $mimeType = ($mimeType === false)?'application/octet-stream':$mimeType;

                $this->setHeader('Content-Type',$mimeType);

                $content = file_get_contents($source);
                $this->send($content);
            }
        }catch(\Exception $e){
            die($e->getMessage());
        }

    }

    /**
     * Set a cookie parameter
     *
     * @param string $name
     * @param string $value
     * @param integer $expires
     * @param string $path
     * @param string $domain
     * @param boolean $secure
     * @param boolean $httponly
     * @return self
     */
    public function setCookie(string $name , $value = "" , int $expires = 0 , string $path = "" , string $domain = "" , bool $secure = FALSE , bool $httponly = false ){
        
        setCookie($name , $value  , $expires , $path , $domain , $secure , $httponly);
        return $this;
    }

    /**
     * Unset a cookie parameter
     *
     * @param string $name
     * @return self
     */
    public function unsetCookie(string $name){
        if(isset($_COOKIE[$name])){
            unset($_COOKIE[$name]); 
            $this->setCookie($name, null, -1, '/'); 
        }

        return $this;
    }


    /**
     * Set a location header parameter
     *
     * @return self
     */
    public function back(){
        $this->setHeader('Location: ',Request::from());

        return $this;
    }


    /**
     * output contents as json
     *
     * @param mixed $data
     * @return void
     */
    public function json($data=null){
        $data = json_encode($data);
        $this->setHeader('Content-Type','application/json')->send($data);
    }


    /**
     * convert object to string
     * 
     *
     * @return string
     */
    public function __toString(){
        return $this->send();
    }

    function image($data = null){
        return image($data);
    }

}