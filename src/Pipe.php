<?php

namespace Limkie;

class Pipe{



    protected $value;

    public function __construct($value){
        $this->value = $value;

        if(!defined('PIPE_VALUE')){
            define('PIPE_VALUE','PIPE_VALUE__'.md5(uniqid()));
        }
    }


    /**
     * Enable chianer on undefined object functions to global functions
     *
     * @param string|array|Closure $name
     * @param  $args
     * @return void
     */
    public function __call($name,$args){
        if(is_callable($name)){
            if(empty($args)){
                $args[] = PIPE_VALUE;
            }
            
            foreach($args as &$arg){
                if($arg === PIPE_VALUE){
                    $arg = $this->value;
                }
            }
        
            $this->value = call_user_func_array($name,$args);
            return $this;
        }

        return false;
    }


    /**
     * Chainer in object
     *
     * @param callable $pipedAction
     * @return self
     */
    public function pipe($pipedAction){
        return $this->__call($pipedAction,[PIPE_VALUE]);
    }

    /**
     * Get current value
     *
     * @return mixed
     */
    public function get(){
        return $this->value;
    }

}