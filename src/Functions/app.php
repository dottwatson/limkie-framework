<?php

use Limkie\App;
use Limkie\DB;
use Limkie\Pipe;
use Nahid\JsonQ\Jsonq;

/**
 * Return app instance
 *
 * @return App
 */
function app(){
    return  App::getInstance();
}


function pipe($value){
    $instance = new Pipe($value);
    return $instance;
}


/**
 * Execute a console command
 *
 * @param string $command
 * @return void
 */
function console(string $command){
    return app()->execCommand($command);
}

/**
 * Return a module instance if exists or false
 *
 * @return object|false
 */
function module(string $name){
    return app()->module($name);
}

/**
 * Watch item in current context, exposed in error page
 *
 * @param string $key
 * @param mixed $item
 * @return void
 */
function watch(string $key,$item){
    app()->watch($key,$item);
}


/**
 * Track item in current session
 *
 * @param mixed $item
 * @return void
 */
function track($item){
    app()->track($item);
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
