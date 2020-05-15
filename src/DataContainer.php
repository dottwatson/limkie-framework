<?php


namespace Limkie;

use \Adbar\Dot;
use Nahid\JsonQ\Jsonq;


/**
 * Handle data with a dot notatio traverser
 * 
 * Also implements a tiny mutator whre you can set the accessor Syntax:
 * e.g:
 * 
 * protected $mutatorSyntax = "getAttribute%s";
 * 
 * 
 * call $object->getAttributeBar();
 * or simply $object->bar;
 * 
 * 
 */


class DataContainer extends Dot{

    /**
     * Object mutators
     *
     * @var array
     */
    protected $mutators = [];

    /**
     * Getter
     *
     * @param string $key with dot notation
     * @param mixed $default
     * @return mixed
     */
    public function get($key=null,$default=null){
        $value = parent::get($key,$default);

        if ($this->mutators && array_key_exists($key,$this->mutators)){
            $requestedMutator = $this->mutators[$key];
            if(is_string($requestedMutator) &&  method_exists($this,$requestedMutator)){
                $value = call_user_func_array($requestedMutator,[$this]);
            }
            elseif(is_callable($requestedMutator)){
                $value = call_user_func_array($requestedMutator,[$this]);
            }
            else{
                $mutatorInfo = var_export($requestedMutator);
                throw new \Exception("Mutator {$mutatorInfo} does not exists");
                return false;
            }
        }
    
        return $value;
    }

    /**
     * Add a mutator for current model.
     * The mutator must be a callable reference
     * if string it will be searched in the model methods.
     * 
     *
     * @param string $name
     * @param Method|Callable $callBack
     * @return bool|self
     */
    public function addMutator($name,$callBack){
        if(!array_key_exists($name,$this->mutators)){
            $this->mutators[$name]= $callBack;
            return $this;
        }
        
    }


    /**
     * Access to array with a querybuilder
     *
     * @param string $name
     * @param array $args
     * @return mixed
     */
    public function __call($name,$args){
        if(method_exists($name,Jsonq::class)){
            $collection =  collect($this->get());
            return call_user_func_array([$collection,$name],$args);
        }
    
        throw new \Exception("Undefined method {$name} in DataContainer");
    }


    /**
     * Convert array data to object
     *
     * @return stdClass
     */
    public function toObject(){
        return json_decode($this->toJson());
    }

}