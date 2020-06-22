<?php 
namespace Limkie\Traits;

use Limkie\Events as EventsManager;

trait Events{

    /**
     * attach event on Class. This event is triggerable only from class instance 
     *
     * @param string $eventName
     * @param callable $callBack
     * @return void
     */
    public function on($eventName,$callBack){
        return EventsManager::onCls(static::class,$eventName,$callBack);
    }

    /**
     * Trigger the event attached on class instance
     *
     * @param string $eventName
     * @return void
     */
    public function trigger($eventName){
        $args       = func_get_args();
        array_shift($args);
        return call_user_func_array('Limkie\\Events::triggerCls',[static::class,$eventName,$this]+$args);
    }

    /**
     * Remove all clalbacks attached to eventname in the cuirrent class 
     *
     * @param string $eventName
     * @return void
     */
    public function off($eventName){
        return EventsManager::offCls(static::class,$eventName);
    }
}


?>