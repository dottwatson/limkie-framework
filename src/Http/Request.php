<?php
namespace Limkie\Http;

use Limkie\DataContainer;

/**
 * Retrieve informations on current request
 */
class Request{
    
    protected static $get;
    protected static $post;
    protected static $request;
    protected static $server;
    protected static $files;
    protected static $cookie;
    protected static $session;
    protected static $headers;

    protected static $initialized = false;


    protected static function init(){
        if(self::$initialized === false){
            $headers = apache_request_headers();

            self::$get      = new DataContainer($_GET);
            self::$post     = new DataContainer($_POST);
            self::$request  = new DataContainer($_REQUEST);
            self::$files    = new DataContainer($_FILES);
            self::$server   = new DataContainer($_SERVER);
            self::$cookie   = new DataContainer($_COOKIE);
            self::$headers  = new DataContainer($headers);

            self::$session  = &app()->session;



            self::$initialized = true;
        }
    }


    public function __call($name,$args){
        if(method_exists($this,$name)){
            $reflectionMethod = new \ReflectionMethod($this,$name);
            if($reflectionMethod->isPublic() && $reflectionMethod->isStatic()){
                return call_user_func_array(static::class."..{$name}",$args);
            }
        }

        throw new \Exception("{$name} is not callable in ".static::class);
    }

    /**
     * Return current url, or a fragment based on PHP parse_url constants
     *
     * @param integer $component
     * @return string
     */
    public static function url($component = 0){
        if(app()->isCommandLine()){
            return '';
        }

        $protocol = self::protocol();
        $url  = "{$protocol}{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";

        if($component){
            $url = parse_url($url,$component);
        }
    
        return $url;
    }


    public static function protocol(){
        $sslPort=443; /* for it might be different, as also Gabriel Sosa stated */
        
        /* X appended after correction by Michael Kopinsky */
        if(!isset($_SERVER["SERVER_NAME"]) || !$_SERVER["SERVER_NAME"]) {
            if(!isset($_ENV["SERVER_NAME"])) {
                getenv("SERVER_NAME");
                // Set to env server_name
                $_SERVER["SERVER_NAME"]=$_ENV["SERVER_NAME"];
            }
        }

        
        if(!isset($_SERVER["SERVER_PORT"]) || !$_SERVER["SERVER_PORT"]) {
            if(!isset($_ENV["SERVER_PORT"])) {
                getenv("SERVER_PORT");
                $_SERVER["SERVER_PORT"]=$_ENV["SERVER_PORT"];
            }
        }
        
        return 
            isset($_SERVER["HTTPS"]) ? (($_SERVER["HTTPS"]==="on" || $_SERVER["HTTPS"]===1 || $_SERVER["SERVER_PORT"]===$sslPort) 
                ? "https://" : "http://") :  (($_SERVER["SERVER_PORT"]===$sslPort) 
                ? "https://" : "http://");
    }

    /**
     * return application base url, or a fragment based on PHP parse_url constants
     *
     * @param integer $component
     * @return string
     */
    public static function baseUrl($component = 0){
        $isCommandLine  = app()->isCommandLine();
        $currentPath    = $_SERVER['PHP_SELF'];
        $pathInfo       = pathinfo($currentPath);
        $hostName       = ($isCommandLine)?'':$_SERVER['HTTP_HOST']; 
        $protocol       = ($isCommandLine)?'':self::protocol();
        $fullResult     = $protocol.$hostName.(($pathInfo['dirname'] == '/')?'':$pathInfo['dirname'])."/";

        if($component){
            return parse_url($fullResult,$component);
        }
        return $fullResult;
    }

    /**
     * Get current referer full or the component according to 
     * https://www.php.net/manual/en/function.parse-url.php constants
     *
     * @param integer $component The referer component if needed
     * @return string
     */
    public static function from($component = 0){
        self::init();

        $from = self::server('HTTP_REFERER');

        if($component){
            $from = parse_url($from,$component);
        }
    
        return $from;
    }

    /**
     * Returns the GET variable retrieved by a dot notation syntax
     * If Found, will be returne d the found variable, or the default value if specified
     * If not found, returns false
     * 
     * 
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public static function get($key='',$default=null){
        self::init();

        if($key == ''){
            return self::$get;
        }

        return self::$get->get($key,$default);
    }


    /**
     * Returns the POST variable retrieved by a dot notation syntax
     * If Found, will be returne d the found variable, or the default value if specified
     * If not found, returns false
     * 
     * 
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public static function post($key='',$default=null){
        self::init();

        if($key == ''){
            return self::$post;
        }

        return self::$post->get($key,$default);
    }

    /**
     * Returns the REQUEST variable retrieved by a dot notation syntax
     * If Found, will be returne d the found variable, or the default value if specified
     * If not found, returns false
     * 
     * 
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public static function input($key='',$default=null){
        self::init();

        if($key == ''){
            return self::$request;
        }

        return self::$request->get($key,$default);
    }

    /**
     * Returns the FILES variable retrieved by a dot notation syntax
     * If Found, will be returne d the found variable, or the default value if specified
     * If not found, returns false
     * 
     * 
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public static function files($key='',$default=null){
        self::init();

        if($key == ''){
            return self::$files;
        }

        return self::$files->get($key,$default);
    }

    /**
     * Returns the SERVER variable retrieved by a dot notation syntax
     * If Found, will be returne d the found variable, or the default value if specified
     * If not found, returns false
     * 
     * 
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public static function server($key='',$default=null){
        self::init();

        if($key == ''){
            return self::$server;
        }

        return self::$server->get($key,$default);
    }

    /**
     * Returns the COOKIE variable retrieved by a dot notation syntax
     * If Found, will be returne d the found variable, or the default value if specified
     * If not found, returns false
     * 
     * 
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public static function cookie($key='',$default=null){
        self::init();

        if($key == ''){
            return self::$cookie;
        }

        return self::$cookie->get($key,$default);
    }


    /**
     * Returns the SESSION data retrieved by a dot notation syntax
     * If Found, will be returne d the found variable, or the default value if specified
     * If not found, returns false
     * 
     * 
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public static function session($key='',$default=null){
        self::init();

        if($key == ''){
            return self::$session;
        }

        return self::$session->get($key,$default);
    }


    /**
     * returns teh request body.
     * php://input is not available for requests specifying a Content-Type: multipart/form-data header (enctype="multipart/form-data" in HTML forms). 
     * This results from PHP already having parsed the form data into the $_POST superglobal.
     *
     * @return string
     */
    public static function body(){
        return stream_get_contents(STDIN);
    }

    /**
     * Returns the headers data retrieved by a dot notation syntax
     * If Found, will be returne d the found variable, or the default value if specified
     * If not found, returns false
     * 
     * 
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public static function header($key='',$default=null){
        self::init();

        if($key == ''){
            return self::$headers;
        }

        return self::$headers->get($key,$default);
    }

    public static function isJson(){
        return self::header('Content-type') == 'application/json';
    }

    public static function isXML(){
        return in_array(
            self::header('Content-type'),
            ['application/xml','text/xml']
        );
    }


}