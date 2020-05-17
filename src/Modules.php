<?php

namespace Limkie;

class Modules{

    protected $modules = [];


    public static function init(){
        $envModules = config('env.'.getEnv('ENVIRONMENT').'.modules',[]);

        foreach($envModules as $envModule){
            $requiredFile   = getEnv('MODULES_DIR')."/{$envModule}/Module.php";
            $moduleCls      = "Modules\\{$envModule}\\Module";
            if(is_file($requiredFile)){
                require_once $requiredFile;

                if(class_exists("Modules\\{$envModule}\\Module")){
                    self::$modules[$envModule] = new $moduleCls();
                }
            }
        }
    }

    public static function isModule(string $moduleName){
        return array_key_exists($moduleName,self::$modules);
    }


    public static function getModule(string $moduleName){
        return (self::isModule($moduleName))
            ?self::$modules[$moduleName]
            :null;
    }


}




