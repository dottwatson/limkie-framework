<?php


namespace Limkie;

use Limkie\DataContainer;

class DebugWatcher extends DataContainer{

    protected static $instance = null;

    /**
     * Singleton
     *
     * @return self
     */
    public static function getInstance(){
        if(self::$instance == null){
            self::$instance = new static;
        }

        return self::$instance;
    }

    /**
     * Disable the setter of DataContainer
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set($key,$value = null){
        return null;
    }

    
    /**
     * Register the element to be watch
     * The element is registereb by reference
     * 
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function register($key,$value){
        if(is_scalar($value) || is_array($value)){
            $value = &$value;
        }
        
        if(!$this->get($key)){
            parent::set($key,$value);
        }
    }

}