<?php 

use Limkie\Translations;

use function Symfony\Component\String\b;
use function Symfony\Component\String\u;
use Symfony\Component\String\Slugger\AsciiSlugger;

function string(string $string){
    return u($string);
}

function bstring(string $string){
    return b($string);
}

function camelcase(string $string = ''){
    return pipe($string)
        ->preg_split('#[^a-z0-9]#',PIPE_VALUE,null,PREG_SPLIT_NO_EMPTY)
        ->array_map('trim',PIPE_VALUE)
        ->array_map('ucfirst',PIPE_VALUE)
        ->implode('',PIPE_VALUE)
        ->get();
}

function slug(string $string='',string $separator = '-'){
    $slugger = new AsciiSlugger();
    return $slugger->slug($string,$separator);
}


/**
 * Deeply trim an array of data
 *
 * @param array $data
 * @param string $charlist
 * @return array
 */
function array_trim(array $data,string $charlist = " \t\n\r\0\x0B"){
    foreach ($data as $k => $v) {
        if (is_string($v)) {
            $data[$k] = trim($v,$charlist);
        } elseif (is_array($v)) {
            $data[$k] = array_trim($v,$charlist);
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


?>