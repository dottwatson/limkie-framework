<?php

namespace Limkie;

class Pipe{



    protected $value;

    /**
     * The flow followed by the variable
     *
     * @var array
     */
    protected $pipeFlow = [];

    /**
     * Initialize object with variable to pipe
     *
     * @param mixed $value
     */
    public function __construct($value){
        $this->value = $value;

        if(!defined('PIPE_VALUE')){
            define('PIPE_VALUE','PIPE_VALUE__'.md5(uniqid()));
        }
    }


    /**
     * Enable chianer on undefined object functions to global functions.
     * This is invoked by pipe method too
     *
     * @param string|array|Closure $name
     * @param  $args
     * @return void
     */
    public function __call($name,$args){
        $this->pipeFlow[] = [
            'pipe'          => $name,
            'args'          => $args,
            'prev_value'    => (!is_object($this->value)) ? $this->value: clone $this->value,
            'current_value' => null,
            'executed'      => false
        ];

        $pipeFlowKey = count($this->pipeFlow) -1; 

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

            $this->pipeFlow[$pipeFlowKey]['executed']       = true;
            $this->pipeFlow[$pipeFlowKey]['current_value']  = (!is_object($this->value)) ? $this->value : clone $this->value;

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

    /**
     * Returns an array of detailed pipes , 
     * where each pipe haves the pipe action, pipe args, executed status and value (before and after pipe)
     *
     * @return array
     */
    public function getFlow(){
        return $this->pipeFlow;
    }

}