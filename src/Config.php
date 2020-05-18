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
     * Load configuration of specific module name (declared modules/[modulename]/config)
     * if previously loaded, will be reloaded. Act as a reset on startup values
     *
     * @param string $moduleName
     * @return void
     */
    protected function loadModule(string $moduleFolder,string $configName){
        $filePath = path( getENv('MODULES_DIR')."/{$moduleFolder}/{$configName}.php");
        
        $configData = include $filePath;
        if(!is_array($configData)){
            throw new \Exception("{$configName} for module {$moduleFolder} is not a valid array");
        }

        $this->set("modules.{$moduleFolder}.{$configName}",$configData);
    }


}


