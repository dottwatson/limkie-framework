<?php
use Limkie\Config;

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
 * return configuration for given module
 *
 * @param string $moduleName
 * @param string $target
 * @param mixed $default
 * @return mixed
 */
function configModule(string $moduleName,string $target = null,$default = null){
    $baseName = "modules.{$moduleName}";
    if($target === null){
        return config($baseName,$default);       
    }

    return config("{$baseName}.{$target}",$default);
}
