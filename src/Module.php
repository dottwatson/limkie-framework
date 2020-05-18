<?php

namespace Limkie;

use Limkie\Config;

abstract class Module{

    public $path;
    public $app;

    public static $version;
    public static $name;
    public static $descroption;

    protected $resourcePrefix = '';
    
    public function __construct(){
        $reflection = new \ReflectionClass(static::class);

        $this->path = static::path();
        $this->app = app();

        $this->resourcePrefix = 'module://'.basename($this->path).'->';
    }


 
    /**
     * Returns a model instance declared in current module
     *
     * @param string $path
     * @param array $data
     * @return void
     */
    public function model(string $path = '',array $data=[]){
        return model("{$this->resourcePrefix}{$path}",$data);
    }


    /**
     * Returns a rendered view declared in current module
     * if nom overriden in App/View/modules/[module-name]/[view-path]
     *
     * @param string $path
     * @param array $data
     * @return void
     */
    public function view(string $path = '',array $data=[]){
        return view("{$this->resourcePrefix}{$path}",$data);
    }

    /**
     * Returns a controller instance declared in current module
     *
     * @param string $path
     * @return object
     */
    public function controller(string $path = ''){
        return controller("{$this->resourcePrefix}{$path}");
    }


    /**
     * Returns a response instance based on current module
     *
     * @param string $path
     * @return void
     */
    public function response(string $path = ''){
        return response("{$this->resourcePrefix}{$path}");
    }

    /**
     * Get the resource prefix of current module
     * e.g. modules://moduleName->
     *
     * @return string
     */
    public function resourcePrefix(){
        return $this->resourcePrefix;
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


    /**
     * Returns the module name based on its folder name
     *
     * @return string
     */
    public static function name(){
        return basename(static::path());
    }


    /**
     * Load configuration of current module
     *
     * @param array $requiredConfig
     * @return void
     */
    public function loadConfig(array $requiredConfig=null){
        $files = glob(static::path().'/config/*.php');

        foreach($files as $file){
            $configName = basename($file);
            if($requiredConfig === null || in_array($configName,$requiredConfig)){
                $this->app->config->loadModule(static::name(),$configName);
            }
        }
    }


    /**
     * Load routes of current module abased on current environment
     *
     * @param array $requiredRoute
     * @return void
     */
    public function loadRoute(array $requiredRoute=null){
        $files = glob(static::path().'/Http/Route/*.php');

        foreach($files as $file){
            $routeName = basename($file);
            if($requiredRoute === null || in_array($routeName,$requiredRoute)){
                include_once $file;
            }
        }
    }


}