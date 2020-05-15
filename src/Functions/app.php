<?php

use Limkie\App;
use Limkie\DB;
use Limkie\Config;
use Limkie\Http\Request;
use Limkie\Http\Response;
use Limkie\Route;
use Limkie\Translations;
use Nahid\JsonQ\Jsonq;

/**
 * Return app instance
 *
 * @return App
 */
function app(){
    return App::getInstance();
}

/**
 * parse a resource
 *
 * @param string $resource
 * @return void
 */
function parseResourceName(string $resource = ''){
    preg_match('#^((?P<source_type>(module|package))\:\/\/(?P<source>[a-zA-Z0-9]+)\-\>)?(?P<target>.+)$#',$resource,$result);

    return [
        'source_type'   => $result['source_type'],
        'source'        => $result['source'],
        'target'        => $result['target']
    ];
}


/**
 * Returns db instance based on connecion name.
 * if null the nam, will be returned connection based on .env DATABASE_CONNECTION_NAME
 *
 * @param string $name
 * @return DB
 */
function db(string $name=null){
    return DB::get($name);
}

/**
 * Resolve path starting from application path
 *
 * @param string $subPath
 * @return string
 */
function path(string $subPath=null){
    if($subPath){
        return __APP_PATH__."/{$subPath}";
    }

    return __APP_PATH__;
}

/**
 * Return an url based on current application url
 *
 * @param string $subPath
 * @param array $queryData
 * @return string
 */
function get_url(string $subPath='',array $queryData=[]){
    $url = Request::baseUrl();

    if($subPath){
        $subPath = preg_replace('#^\/*#','',trim((string)$subPath));
        $url .= $subPath;
    }

    if($queryData){
        $url .= '?'.http_build_query($queryData);
    }

    return $url;
}

/**
 * retrieve full url from named route
 *
 * @param string $name
 * @param array $vars The variables to replace in the route 
 * @param array $queryData Optional query string data
 * @return bool|string The full quailfied url or false on failure
 */
function route(string $name,array $vars=[],array $queryData = []){
    if(Route::hasRoute($name)){
        $subPath = Route::route($name,$vars);
        return get_url($subPath,$queryData);
    }

    return false;
}

/**
 * call a view for rendering
 *
 * @param string $file
 * @param array $params
 * @return string
 */
function view(string $file,array $params= []){
    $resource       = parseResourceName($file);
    $pathRewriteTo  = false;


    if($resource['source_type'] == 'module'){
        $pathTo         = path(getEnv('MODULES_DIR')."/{$resource['source']}/View/{$resource['target']}");
        $pathRewriteTo  = path("app/View/modules/{$resource['source']}/{$resource['target']}");
    }
    elseif($resource['source_type'] == 'Package'){
        $pathTo = path("vendor/{$resource['source']}/View/{$resource['target']}");
        $pathRewriteTo  = path("app/View/vendor/{$resource['source']}/{$resource['target']}");
    }
    else{
        $pathTo = path("app/View/{$resource['target']}");
    }

    // dumpe($pathTo,$pathRewriteTo);

    if($pathRewriteTo && is_file($pathRewriteTo)){
        $file = $pathRewriteTo;
    }
    else{
        $file = $pathTo;
    }
    
    return call_user_func_array("App\\View::render",[$file,$params]);
}



/**
 * instantiate a model with optional arguments
 *
 * @param string $name
 * @param array $params
 * @return object
 */
function model(string $name,array $params= []){
    $cls = str_replace('.','\\',$name);
    $namespacedCls = "App\\Model\\".$cls;
    
    if(version_compare(PHP_VERSION, '5.6.0', '>=')){
        $instance = new $namespacedCls(...$params);
    } else {
        $reflect  = new ReflectionClass($namespacedCls);
        $instance = $reflect->newInstanceArgs($params);
    }

    return $instance;
}



/**
 * instantiate a controller with optional arguments
 *
 * @param string $name
 * @param array $params
 * @return object
 */
function controller(string $name,array $params= []){
    $resource = parseResourceName($name);

    if($resource['source_type'] == 'module'){
        $namespace = "Modules\\{$resource['source']}\\Http\\Controller\\";
    }
    elseif($resource['source_type'] == 'Package'){
        $namespace = "{$resource['source']}\\Http\\Controller\\";
    }
    else{
        $namespace = "App\\Http\\Controller\\";
    }
    
    $cls = str_replace('.','\\',$resource['target']);
    $namespacedCls = $namespace.$cls;
    
    if(version_compare(PHP_VERSION, '5.6.0', '>=')){
        $instance = new $namespacedCls(...$params);
    } else {
        $reflect  = new ReflectionClass($namespacedCls);
        $instance = $reflect->newInstanceArgs($params);
    }

    return $instance;
}


/**
 * Return a request object
 *
 * @return Request
 */
function request(){
    static $request;

    if(is_null($request)){
        $reques = new Request;
    }

    return $request;
}

/**
 * Make response with passed contents
 *
 * @param string $contents
 * @return void
 */
function response(string $contents = null){
    $response = new Response($contents);

    return $response;
}


/**
 * Make speficif response type with passed init arguments and contents
 *
 * @param string $name
 * @param array $params
 * @return object
 */
function responseOf(string $name,array $params = []){
    $resource = parseResourceName($name);

    if($resource['source_type'] == 'module'){
        $namespace = "Modules\\{$resource['source']}\\Http\\Response\\";
    }
    elseif($resource['source_type'] == 'Package'){
        $namespace = "{$resource['source']}\\Http\\Response\\";
    }
    else{
        $namespace = "App\\Http\\Response\\";
    }

    $cls = str_replace('.','\\',$resource['target']);
    $namespacedCls = $namespace.$cls;
    
    if(version_compare(PHP_VERSION, '5.6.0', '>=')){
        $instance = new $namespacedCls(...$params);
    } else {
        $reflect  = new ReflectionClass($namespacedCls);
        $instance = $reflect->newInstanceArgs($params);
    }

    return $instance;
}


/**
 * retrieve a value from configuraitons, with dot notation
 *
 * @param string $name
 * @param string $default
 * @return null|mixed
 */
function config(string $name=null,$default=null){
    return Config::getInstance()->get($name,$default);
}

/**
 * set or overwrite a configuration parameter, with dot notation
 *
 * @param string $name
 * @param mixed $vaue
 * @return bool
 */
function setConfig(string $name,$value){
    return Config::getInstance()->set($name,$value);
}

/**
 * set or overwrite a configuration parameter, with dot notation
 *
 * @param string $name
 * @param mixed $vaue
 * @return bool
 */
function setConfigAll(array $data = []){
    foreach($data as $name=>$value){
        Config::getInstance()->set($name,$value);
    }
}



/**
 * Returns a storage instance based on its identifier
 *
 * @param string $name
 * @param string $path
 * @return Storage
 */
function storage(string $name,string $path=''){
    if(!config("storage.{$name}")){
        return false;
    }

    $clsName = "App\\Storage";
    $path = config("storage.{$name}");
    return (new $clsName($path));
}

/**
 * Return a collection array querable
 *
 * @param array $data
 * @return Jsonq
 */
function collect(array $data = []){
    $queryBuilder = new Jsonq();
    $queryBuilder->collect($data);

    return $queryBuilder;
}

/**
 * Encrypt a mixed element based an an arbitrary key, otherwishe the env SECRET_KEY
 *
 * @param mixed $var
 * @param string $key
 * @return string
 */
function encrypt($var,string $key = null,string $cipher = null){
    $key            = (is_null($key)) ? getEnv('SECRET_KEY') : (string) $key;
    $cipher         = ($cipher)?$cipher:getenv('ENCRYPTION_CIPHER');
    $var            = json_encode($var);
    $ivlen          = openssl_cipher_iv_length($cipher);
    $iv             = openssl_random_pseudo_bytes($ivlen);
    $cipherTextRaw = openssl_encrypt($var, $cipher, $key, $options=OPENSSL_RAW_DATA, $iv);
    $hmac           = hash_hmac('sha256', $cipherTextRaw, $key, $as_binary=true);
    $result         = base64_encode( $iv.$hmac.$cipherTextRaw );    
    $result         = str_replace(['+', '/', '='],['|', '-', '_'],$result);

    return $result;
}


/**
 * Decrypt a string based an an arbitrary key, otherwishe the env SECRET_KEY
 *
 * @param mixed $var
 * @param string $key
 * @return mixed
 */
function decrypt($var,string $key = null,string $cipher = null){
    $key                = (is_null($key)) ? getEnv('SECRET_KEY') : (string) $key;
    $cipher             = ($cipher)?$cipher:getenv('ENCRYPTION_CIPHER');
    $var                = str_replace(['|', '-', '_'],['+', '/', '='],$var);
    $var                = base64_decode($var);
    $ivlen              = openssl_cipher_iv_length($cipher);
    $iv                 = substr($var, 0, $ivlen);
    $hmac               = substr($var, $ivlen, $sha2len=32);
    $cipherTextRaw      = substr($var, $ivlen+$sha2len);
    $result             = openssl_decrypt($cipherTextRaw, $cipher, $key, OPENSSL_RAW_DATA, $iv);
    $calcmac = hash_hmac('sha256', $cipherTextRaw, $key, true);
    if (hash_equals($hmac, $calcmac)){//PHP 5.6+ timing attack safe comparison
        return json_decode($result,true);
    }

    return false;
}

/**
 * Deeply trim an array of data
 *
 * @param string|array $data
 * @return string|array
 */
function deeptrim(array $data){
    if (is_string($data)){
        return trim($data);
    }

    foreach ($data as $k => $v) {
        if (is_string($v)) {
            $data[$k] = trim($v);
        } elseif (is_array($v)) {
            $data[$k] = deeptrim($v);
        }
    }
    return $data;
}

/**
 * Get traanslation based on custom locale
 *
 * @param string $locale
 * @param string $key
 * @param string $default
 * @return null|string
 */
function locale_trans(string $locale,string $key,string $default = null){
    return Translations::getLocale($locale,$key,$default);
}


/**
 * Get translation based on custom locale, formatted with spritf format
 *
 * @param string $locale
 * @param string $key
 * @param array $params
 * @return void
 */
function locale_transf(string $locale,string $key,array $params = []){
    
    $string = Translations::getLocale($locale,$key);

    $string = ($string)
        ?preg_replace('#(%[0-9]+)#','$1\$s',$string)
        :"{$locale}.{$key}";
    
    array_unshift($params,$string);

    return ($string)
        ?call_user_func_array('sprintf',$params)
        :$string;
}


/**
 * Get translation based on current locale
 *
 * @param string $key
 * @param string $default
 * @return null|string
 */
function trans(string $key,string $default = null){
    return locale_trans(config('app.locale'),$key,$default);
}


/**
 * Get translation based on current locale, formatted with spritf format
 *
 * @param string $key
 * @param array $params
 * @return null|string
 */
function transf(string $key,array $params = []){
    return locale_transf(config('app.locale'), $key,$params);
}

