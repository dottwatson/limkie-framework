<?php

namespace Limkie;

use Limkie\DataContainer;


class Config extends DataContainer{

    /**
     * SIngleton Instance
     *
     * @var object
     */
    protected static $instance = null;

    public function __construct(){
    }
    
    /**
     * Return the current instance if already created,
     * otherwise create it and returns the instance just created.
     *
     * @return object
     */
    public static function getInstance(){
        if(self::$instance == null){
            self::$instance = new static;
            self::$instance->init();
        }

        return self::$instance;
    }
    
    /**
     * Load all configurations, with priority to the default configuration
     *
     * @return void
     */
    public function init(){
        $reserved = ['adapter','alias','storage','database','console','view','app'];

        foreach($reserved as $reservedConfigName){
            $this->load($reservedConfigName);
        }
        
        $files = glob(__APP_CONFIG_PATH__."/*.php");
        foreach($files as $name){
            $configName = basename($name,'.php');

            if(!in_array($configName,$reserved)){
                $this->load($configName);
            }
            
        }

        //load modules configurations
        $dirs = glob(__APP_CONFIG_PATH__."/modules/*",GLOB_ONLYDIR);
        foreach($dirs as $dir){
            $moduleName = basename($dir);
            $this->loadModule($moduleName);
        }
    }

    /**
     * Load a configuration. if previously loaded, will be reloaded
     * Act as a reset on startup values
     *
     * @param string $name
     * @return bool
     */
    protected function load(string $name){
        if(is_file(__APP_CONFIG_PATH__."/{$name}.php")){
            $configData = include __APP_CONFIG_PATH__."/{$name}.php";
            $this->set($name,$configData);
            return true;
        }
        
        return false;
    }

    /**
     * Load configuration of specific module name (declared as folder in config/modules/[modulename])
     * if previously loaded, will be reloaded. Act as a reset on startup values
     *
     * @param string $moduleName
     * @return void
     */
    protected function loadModule(string $moduleName){
        if(is_dir(__APP_CONFIG_PATH__."/{$moduleName}")){
            $moduleConfigFiles = glob(__APP_CONFIG_PATH__."/{$moduleName}/*.php");
            foreach($moduleConfigFiles as $configFile){
                $configData = include $configFile;
                $configIndex = basename($configFile,'.php');
                $this->set("module.{$moduleName}.{$configIndex}",$configData);
            }
        }
    }


}


