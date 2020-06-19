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

        $this->path = static::path();
        $this->app  = app();


        $this->resourcePrefix = 'module://'.basename($this->path).'->';

        $this->importGates();
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
    public function importConfig(array $requiredConfig=null){
        $files = glob(static::path().'/config/*.php');

        foreach($files as $file){
            $configName = basename($file,'.php');
            if($requiredConfig === null || in_array($configName,$requiredConfig)){
                $this->app->config->loadModule(static::name(),$configName);
            }
        }
    }


    /**
     * Load routes of current module based on current environment
     *
     * @param array $requiredRoute
     * @return void
     */
    public function importRoutes(array $requiredRoute=null){
        $files = glob(static::path().'/Http/Route/*.php');

        foreach($files as $file){
            $routeName = basename($file);
            if($requiredRoute === null || in_array($routeName,$requiredRoute)){
                include_once $file;
            }
        }
    }

        /**
     * Load routes of current module abased on current environment
     *
     * @param array $requiredRoute
     * @return void
     */
    public function importGates(){
        $files      = glob(static::path().'/Http/Gate/*.php');
        $namespace  = 'Modules\\'.static::name().'\\Http\\Gate';


        //list all classes under current Module Gate namespace
        $classes = get_declared_classes();
        foreach($classes as $className){
            $lowerClsName   = strtolower($className);
            $lowerNamespace = strtolower($namespace);
            if(strpos($lowerClsName,$lowerNamespace) === 0){
                $reflectionCLs  = new \ReflectionClass($className);
                $properties     = $reflectionCLs->getDefaultProperties();
                $alias = (isset($properties['alias']))
                    ?trim((string)$properties['alias'])
                    :null;

                if($alias){
                    Route::filter($alias,function() use($className){
                        $instance = new $className;
                        $nextStep = $instance->handle();

                        if($nextStep instanceOf \Limkie\Http\Response){
                            echo (string)$nextStep;
                            exit;
                        }

                        $continue = ($nextStep === true || $nextStep === null)?null:false;

                        return $continue;
                    });
                }
            }            
        }
    }


}