<?php

namespace Limkie;

class Modules{

    protected static $modules = [];

    /**
     * Initialize modules loading based on environment settings
     *
     * @return void
     */
    public static function init(){
        $envModules = config('env.'.getEnv('ENVIRONMENT').'.modules',[]);

        foreach($envModules as $envModule){
            $requiredFile   = path(getEnv('MODULES_DIR')."/{$envModule}/Module.php");
            $moduleCls      = "Modules\\{$envModule}\\Module";

            if(is_file($requiredFile)){
                require_once $requiredFile;

                if(class_exists($moduleCls)){
                    self::$modules[$envModule] = new $moduleCls();
                }
            }
        }
    }


    /**
     * Check if module is loaded
     * 
     * @return bool
     */
    public static function isModule(string $moduleName){
        return array_key_exists($moduleName,self::$modules);
    }


    /**
     * get Module instance
     *
     * @param string $moduleName
     * @return object|null
     */
    public static function getModule(string $moduleName){
        return (self::isModule($moduleName))
            ?self::$modules[$moduleName]
            :null;
    }


}




