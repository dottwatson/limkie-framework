<?php

namespace Limkie;

/**
 * Event manager
 * 
 * This tool, in combination with \App\Traits\Events used on object
 * gives you the ability to attach automatically events on objects
 * 
 * The obly work for dev is to properly trigger events on appropriate object instance
 * 
 * In an object can be ONLY 1 callback per event
 * 
 */
class Events{

    protected static $events    = [];
    protected static $clsEvents = [];


    /**
     * Attach a callback to a specific event
     *
     * @param string $eventName
     * @param Closure $callBack
     * @return void
     */
    public static function on($eventName,$callBack){
        if(!array_key_exists($eventName,self::$events)){
            self::$events[$eventName] = [];
        }

        self::$events[$eventName][] = $callBack;
    }

    /**
     * Trigger an event with custom args
     *
     * @param string $eventName
     * @return bool|mixed
     */
    public static function trigger($eventName){
        $args       = func_get_args();
        $eventName  = array_shift($args);
        
        if(!array_key_exists($eventName,self::$events)){
            return false;
        }

        foreach(self::$events[$eventName] as $eventCallBack){
            if(is_callable($eventCallBack)){
                $result = call_user_func_array($eventCallBack,$args);
                if($result === false){
                    return;
                }
            }
        }
    }

    /**
     * Remove all closures on specific event
     *
     * @param string $eventName
     * @return void
     */
    public static function off($eventName){
        unset(self::$events[$eventName]);
    }

    /**
     * Attach events on Object instance
     *
     * @param string $clsName
     * @param string $eventName
     * @param Closure $callBack
     * @return void
     */
    public static function onCls($clsName,$eventName,$callBack){
        if(!array_key_exists($clsName,self::$clsEvents)){
            self::$clsEvents[$clsName] = [];
        }

        if(!array_key_exists($eventName,self::$clsEvents[$clsName])){
            self::$clsEvents[$clsName][$eventName] = [];
        }

        self::$clsEvents[$clsName][$eventName][] = $callBack;
    }


    /**
     * Trigger events on Object instance
     *
     * @param string $clsName
     * @param string $eventName
     * @return bool|mixed
     */
    public static function triggerCls($clsName,$eventName){
        $args       = func_get_args();
        $clsName    = array_shift($args);
        $eventName  = array_shift($args);
        
        if(!array_key_exists($clsName,self::$clsEvents) || !array_key_exists($eventName,self::$clsEvents[$clsName])){
            return false;
        }

        foreach(self::$clsEvents[$clsName][$eventName] as $eventCallBack){
            if(is_callable($eventCallBack)){
                $result = call_user_func_array($eventCallBack,$args);
                if($result === false){
                    return;
                }
            }
        }
    }

    /**
     * remove events on Object instance
     *
     * @param string $clsName
     * @param string $eventName
     * @param Closure $callBack
     * @return void
     */
    public static function offCls($clsName,$eventName){
        if(!array_key_exists($clsName,self::$clsEvents) || !array_key_exists($eventName,self::$clsEvents[$clsName])){
            return false;
        }

        unset(self::$clsEvents[$clsName][$eventName]);
    }



}


