<?php

namespace Limkie;

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


    public function model(string $path = '',array $data=[]){
        return model("{$this->resourcePrefix}{$path}",$data);
    }


    public function view(string $path = ''){
        return view("{$this->resourcePrefix}{$path}");
    }

    public function controller(string $path = ''){
        return controller("{$this->resourcePrefix}{$path}");
    }

    public function response(string $path = ''){
        return response("{$this->resourcePrefix}{$path}");
    }

    /**
     * Get the resource pprefix of current module
     * e.g. modules://moduleName->
     *
     * @return void
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
}