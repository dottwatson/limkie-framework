<?php

namespace Limkie;

abstract class Module{


    public function __construct(){

    }


    public function install(){

    }

    public function uninstall(){

    }


    public function publishView(){
        //publish views
        $path = static::path();

        if(is_dir("{$path}/view")){

        }

    }

    public function publishConfig(){
        //publish configurations
        $path = static::path();

        if(is_dir("{$path}/config")){
            
        }

    }


    public function publishAssets(){
        //publish public resources
        $path = static::path();

        if(is_dir("{$path}/assets")){
                        
        }

    }


    /**
     * Return thee path of module
     *
     * @return string
     */
    public static function path(){
        $fileName = (new \ReflectionClass(static::class))->getFileName();

        return dirname($fileName);
    }
}