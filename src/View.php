<?php

namespace Limkie;

use Limkie\Storage;
use Limkie\DataContainer;
use Smarty;

class View{
    protected static $storage;
    protected static $engine;
    protected static $basePath;
    protected static $vars;


    /**
     * Checks if view is initialized and initialize it
     *
     * @return void
     */
    private static function  checkInit(){
        self::$basePath = config('view.path');
        
        if(!self::$storage){
            self::$storage = new Storage(self::$basePath);
        }

        if(!self::$engine){
            self::$vars     = new DataContainer([]);
            self::$engine   = new Smarty;

            self::$engine->setTemplateDir(self::$basePath);
            self::$engine->setCompileDir(config('view.cache').'/smarty/compile');
            self::$engine->setCacheDir(config('view.cache').'/smarty/cache');

            if(app()->env('view.cache') !== null){
                self::$engine->setCaching(app()->env('view.cache'));
            }

        }
    }

    /**
     * return internal engine used for view
     *
     * @return object
     */
    public static function engine(){
        return self::$engine;
    }

    /**
     * Set variable in the engine
     *
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public static function setVar($name,$value){
        self::$vars->set($name,$value);
        
        return self::$engine->assign($name,$value);
    }

    /**
     * Render the view
     *
     * @param string $name
     * @param array $params optiona variables to pass
     * @return string
     */
    public static function render($name,array $params = []){
        self::checkInit();
        
        $name = trim($name);

        if(!is_file($name)){
            throw new \Exception('View '.$name.' not found');
        }

        $vars = self::$vars->all();
        $viewParams = array_merge($vars,$params);

        return self::$engine->fetch($name,$viewParams);
    }

    /**
     * Register a plugin for engine
     *
     * @return mixed
     */
    public static function registerPlugin(){
        self::checkInit();

        $args = func_get_args();

        return call_user_func_array([self::$engine,'registerPlugin'],$args);
    }
}